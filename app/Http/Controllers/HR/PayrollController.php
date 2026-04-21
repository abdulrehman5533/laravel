<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\HR\EmployeeExpense;
use App\Models\HR\EmployeeLoan;
use App\Models\Payroll;
use App\Services\Accounting\AccountingService;
use App\Services\HR\PayrollService;
use App\Services\MultiCountryComplianceService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    protected $complianceService;

    protected $accountingService;

    protected $notificationService;

    protected $payrollService;

    public function __construct(
        MultiCountryComplianceService $complianceService,
        AccountingService $accountingService,
        NotificationService $notificationService,
        PayrollService $payrollService
    ) {
        $this->complianceService = $complianceService;
        $this->accountingService = $accountingService;
        $this->notificationService = $notificationService;
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->filter_month) {
            [$yr, $mo] = explode('-', $request->filter_month);
            $query->where('month_year', sprintf('%02d-%d', $mo, $yr));
        }
        if ($request->filter_status) {
            $query->where('payment_status', $request->filter_status);
        }
        if ($request->search) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%");
            });
        }

        $payrolls = $query->orderBy('month_year', 'desc')->get();

        return view('hr.payroll.index', compact('payrolls'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric',
        ]);

        $monthYear = sprintf('%02d-%d', $request->month, $request->year);
        $employees = Employee::where('status', 'active')->get();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $calc = $this->payrollService->calculateMonthlyPayroll($employee, $request->month, $request->year);

                // Use employee's saved tax/SS if set, otherwise calculate via compliance service
                $tax = (float)($employee->income_tax ?? 0) > 0
                    ? (float)$employee->income_tax
                    : $this->complianceService->calculateTax($calc['net_salary'], $employee->country_code ?? 'PK');

                $ss = ((float)($employee->eobi_deduction ?? 0) + (float)($employee->pessi_deduction ?? 0)) > 0
                    ? (float)$employee->eobi_deduction + (float)$employee->pessi_deduction
                    : $this->complianceService->calculateSocialSecurity($calc['basic_salary'], $employee->country_code ?? 'PK');

                $perfScore = (float) ($employee->performance_score ?? 0);
                $bonus     = ($perfScore / 100) * $calc['basic_salary'];
                $finalNet  = max(0, $calc['net_salary']);

                Payroll::updateOrCreate(
                    ['employee_id' => $employee->id, 'month_year' => $monthYear],
                    [
                        'basic_salary'                => $calc['basic_salary'],
                        'allowances'                  => $calc['allowances'],
                        'deductions'                  => $calc['deductions'],
                        'overtime_pay'                => $calc['overtime_pay'],
                        'late_deduction'              => $calc['late_deduction'] ?? 0,
                        'sales_commission'            => $calc['sales_commission'],
                        'karigar_making_charges'      => $calc['karigar_making_charges'],
                        'bonus_performance'           => $bonus,
                        'loan_deduction'              => $calc['loan_deduction'],
                        'expense_reimbursement'       => EmployeeExpense::where('employee_id', $employee->id)
                                                            ->where('status', 'approved')->sum('amount'),
                        'tax_amount'                  => $tax,
                        'social_security_contribution'=> $ss,
                        'net_salary'                  => $finalNet,
                        'salary_breakdown'            => $calc['salary_breakdown'],
                        'compliance_details'          => $this->complianceService->getComplianceLog($employee, $finalNet),
                        'payment_status'              => 'pending',
                    ]
                );
            }
            DB::commit();

            return redirect()->back()->with('success', "Payroll for {$monthYear} generated for {$employees->count()} employees.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error generating payroll: ' . $e->getMessage());
        }
    }

    public function downloadPayslip($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $pdf = Pdf::loadView('hr.payroll.payslip', compact('payroll'));

        return $pdf->download("Payslip-{$payroll->employee->employee_code}-{$payroll->month_year}.pdf");
    }

    public function approve($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->update(['payment_status' => 'approved']);

        return redirect()->back()->with('success', 'Payroll approved. Ready for payment.');
    }

    public function pay($id)
    {
        $payroll = Payroll::findOrFail($id);

        if ($payroll->payment_status !== 'approved') {
            return redirect()->back()->with('error', 'Payroll must be approved before payment.');
        }

        DB::beginTransaction();
        try {
            $payroll->update([
                'payment_status' => 'paid',
                'paid_on' => Carbon::now(),
            ]);

            $this->postToAccounting($payroll);

            // Update Loan balance
            if ($payroll->loan_deduction > 0) {
                $loan = EmployeeLoan::where('employee_id', $payroll->employee_id)
                    ->where('status', 'active')
                    ->first();
                if ($loan) {
                    $newBalance = $loan->remaining_balance - $payroll->loan_deduction;
                    $loan->update([
                        'remaining_balance' => max(0, $newBalance),
                        'status' => $newBalance <= 0 ? 'paid_off' : 'active',
                    ]);
                }
            }

            // Mark expenses as reimbursed
            if ($payroll->expense_reimbursement > 0) {
                EmployeeExpense::where('employee_id', $payroll->employee_id)
                    ->where('status', 'approved')
                    ->update(['status' => 'reimbursed']);
            }

            if ($payroll->employee->user) {
                $this->notificationService->sendOmnichannel(
                    $payroll->employee->user,
                    "Your salary for {$payroll->month_year} has been disbursed."
                );
            }

            DB::commit();

            return redirect()->back()->with('success', 'Salary marked as paid and posted to accounting.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error posting to accounting: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        if (request('download')) {
            $pdf = Pdf::loadView('hr.payroll.payslip', compact('payroll'));
            return $pdf->download("Payslip-{$payroll->employee->employee_code}-{$payroll->month_year}.pdf");
        }

        return view('hr.payroll.show', compact('payroll'));
    }

    public function bulkApprove(Request $request)
    {
        $ids = $request->validate(['payroll_ids' => 'required|array', 'payroll_ids.*' => 'exists:hr_payroll,id'])['payroll_ids'];
        $count = Payroll::whereIn('id', $ids)->where('payment_status', 'pending')->update(['payment_status' => 'approved']);
        return back()->with('success', "{$count} payroll records approved.");
    }

    public function bulkPay(Request $request)
    {
        $ids = $request->validate(['payroll_ids' => 'required|array', 'payroll_ids.*' => 'exists:hr_payroll,id'])['payroll_ids'];
        $count = 0;
        foreach (Payroll::whereIn('id', $ids)->where('payment_status', 'approved')->get() as $payroll) {
            DB::beginTransaction();
            try {
                $payroll->update(['payment_status' => 'paid', 'paid_on' => Carbon::now()]);
                $this->postToAccounting($payroll);
                DB::commit();
                $count++;
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
        return back()->with('success', "{$count} salaries marked as paid.");
    }

    protected function postToAccounting(Payroll $payroll)
    {
        // Identify accounts - in production, these should be from settings or mapping keys
        $salaryExpenseAccount = ChartOfAccount::where('mapping_key', 'expense_salary')->first()
            ?? ChartOfAccount::where('account_code', '5610')->first();

        $taxLiabilityAccount = ChartOfAccount::where('mapping_key', 'liabilities_tax_payroll')->first()
            ?? ChartOfAccount::where('account_code', '2120')->first();

        $cashAccount = ChartOfAccount::where('mapping_key', 'cash_in_hand')->first()
            ?? ChartOfAccount::where('account_code', '1010')->first();

        if (! $salaryExpenseAccount || ! $cashAccount) {
            // Create default accounts if missing for demonstration/completeness
            if (! $salaryExpenseAccount) {
                $salaryExpenseAccount = ChartOfAccount::create([
                    'account_code' => '5610',
                    'account_name' => 'Salaries & Wages',
                    'account_type' => 'Expense',
                    'account_category' => 'Expense',
                    'mapping_key' => 'expense_salary',
                    'is_active' => true,
                ]);
            }
            if (! $cashAccount) {
                $cashAccount = ChartOfAccount::where('account_code', '1010')->first(); // Fallback
            }
        }

        $items = [
            [
                'account_id'  => $salaryExpenseAccount->id,
                'debit'       => $payroll->net_salary,
                'credit'      => 0,
                'description' => "Salary Expense for {$payroll->employee->first_name} - {$payroll->month_year}",
            ],
        ];

        if ($payroll->tax_amount > 0 && $taxLiabilityAccount) {
            $items[] = [
                'account_id'  => $taxLiabilityAccount->id,
                'debit'       => $payroll->tax_amount,
                'credit'      => 0,
                'description' => "Tax Withholding for {$payroll->employee->first_name}",
            ];
        }

        $items[] = [
            'account_id'  => $cashAccount->id,
            'debit'       => 0,
            'credit'      => $payroll->net_salary,
            'description' => "Salary Payment for {$payroll->employee->first_name}",
        ];

        if ($payroll->tax_amount > 0 && $taxLiabilityAccount) {
            $items[] = [
                'account_id'  => $taxLiabilityAccount->id,
                'debit'       => 0,
                'credit'      => $payroll->tax_amount,
                'description' => "Tax Liability for {$payroll->employee->first_name}",
            ];
        }

        if ($payroll->social_security_contribution > 0 && $taxLiabilityAccount) {
            $items[] = [
                'account_id'  => $taxLiabilityAccount->id,
                'debit'       => 0,
                'credit'      => $payroll->social_security_contribution,
                'description' => "Social Security for {$payroll->employee->first_name}",
            ];
        }

        $this->accountingService->createJournalEntry([
            'reference_number' => "PAYROLL-{$payroll->id}",
            'entry_date' => now()->toDateString(),
            'narration' => "Monthly Payroll for {$payroll->employee->first_name} ({$payroll->month_year})",
            'items' => $items,
            'reference_type' => 'payroll',
        ]);
    }
}
