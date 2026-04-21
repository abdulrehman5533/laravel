<?php

namespace App\Services\HR;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HR\AttendanceRule;
use App\Models\HR\EmployeeLoan;
use App\Models\HR\EmployeeSalaryStructure;
use App\Models\HR\KarigarRate;
use App\Models\HR\SalesCommissionRate;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Calculate comprehensive payroll for an employee
     */
    public function calculateMonthlyPayroll(Employee $employee, $month, $year)
    {
        $basic = $employee->base_salary;
        $monthYear = sprintf('%02d-%d', $month, $year);
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;

        // Attendance stats for display only — salary not affected by attendance
        $attendanceStats = $this->calculateAttendanceStats($employee, $month, $year);
        $overtimeHours = $attendanceStats['overtime_hours'];
        $lateDeduction = 0; // Not deducting based on attendance

        $grossSalary = $basic; // Always full basic salary
        $overtimePay = $this->calculateOvertimePay($employee, $overtimeHours);

        // 2. Jewellery Specific: Sales Commission
        $salesCommission = $this->calculateSalesCommission($employee, $month, $year);

        // 3. Jewellery Specific: Karigar Making Charges
        $karigarMaking = $this->calculateKarigarMakingCharges($employee, $month, $year);

        // 4. Salary Components (HRA, Allowances, PF)
        $components = $this->calculateSalaryComponents($employee, $basic);
        $totalEarnings = $components['earnings']->sum('amount');
        $totalDeductions = $components['deductions']->sum('amount');

        // Add employee's own allowances/deductions from profile
        $hra               = (float) ($employee->hra ?? 0);
        $medicalAllowance  = (float) ($employee->medical_allowance ?? 0);
        $transportAllowance= (float) ($employee->transport_allowance ?? 0);
        $eobiDeduction     = (float) ($employee->eobi_deduction ?? 0);
        $pessiDeduction    = (float) ($employee->pessi_deduction ?? 0);
        $incomeTax         = (float) ($employee->income_tax ?? 0);

        $profileAllowances  = $hra + $medicalAllowance + $transportAllowance;
        $profileDeductions  = $eobiDeduction + $pessiDeduction + $incomeTax;

        // 5. Loans & Advances
        $loanDeduction = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->value('monthly_installment') ?? 0;

        // Final Calculation
        $netSalary = ($grossSalary + $overtimePay + $salesCommission + $karigarMaking + $totalEarnings + $profileAllowances)
                     - ($totalDeductions + $profileDeductions + $loanDeduction + $lateDeduction);

        return [
            'employee_id'                 => $employee->id,
            'month_year'                  => $monthYear,
            'basic_salary'                => $basic,
            'allowances'                  => $profileAllowances + $totalEarnings,
            'deductions'                  => $profileDeductions + $totalDeductions,
            'overtime_pay'                => $overtimePay,
            'late_deduction'              => $lateDeduction,
            'sales_commission'            => $salesCommission,
            'karigar_making_charges'      => $karigarMaking,
            'loan_deduction'              => $loanDeduction,
            'expense_reimbursement'       => 0,
            'tax_amount'                  => $incomeTax,
            'social_security_contribution'=> $eobiDeduction + $pessiDeduction,
            'bonus_performance'           => 0,
            'net_salary'                  => max(0, $netSalary),
            'salary_breakdown'       => [
                'attendance'         => $attendanceStats,
                'allowances'         => [
                    'hra'               => $hra,
                    'medical'           => $medicalAllowance,
                    'transport'         => $transportAllowance,
                ],
                'profile_deductions' => [
                    'eobi'              => $eobiDeduction,
                    'pessi'             => $pessiDeduction,
                    'income_tax'        => $incomeTax,
                ],
                'components'         => [
                    'earnings'    => $components['earnings']->map(fn($e) => ['name' => $e->name, 'amount' => $e->amount])->values()->toArray(),
                    'deductions'  => $components['deductions']->map(fn($d) => ['name' => $d->name, 'amount' => $d->amount])->values()->toArray(),
                ],
                'jewellery_metrics'  => [
                    'sales_commission' => $salesCommission,
                    'karigar_making'   => $karigarMaking,
                ],
            ],
        ];
    }

    protected function calculateAttendanceStats(Employee $employee, $month, $year)
    {
        // attendances table uses user_id (not employee_id), date stored in check_in_time
        $query = Attendance::whereMonth('check_in_time', $month)
            ->whereYear('check_in_time', $year);

        // Link via user_id if employee has a user account, else return defaults
        if ($employee->user_id) {
            $query->where('user_id', $employee->user_id);
        } else {
            return [
                'present_days'   => 0,
                'absent_days'    => Carbon::createFromDate($year, $month)->daysInMonth,
                'late_count'     => 0,
                'late_deduction' => 0,
                'overtime_hours' => 0,
            ];
        }

        $attendances = $query->get();

        $rule = AttendanceRule::where('is_default', true)->first();
        $lateDeductionAmount = $rule ? ($rule->late_deduction_per_instance ?? 0) : 0;
        $weeklyOffDays = $rule ? ($rule->weekly_off_days ?? ['Sunday']) : ['Sunday'];

        // Try to get holidays (table may not exist yet)
        $holidays = collect();
        try {
            $holidays = \App\Models\HR\Holiday::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where(function ($q) use ($employee) {
                    $q->where('branch_id', $employee->branch_id)->orWhereNull('branch_id');
                })
                ->get()
                ->keyBy(fn ($d) => $d->date->toDateString());
        } catch (\Exception $e) {
            // holidays table may not exist
        }

        $daysInMonth   = Carbon::createFromDate($year, $month)->daysInMonth;
        $presentDays   = 0;
        $lateCount     = 0;
        $overtimeSecs  = 0;

        // Group by date (use check_in_time date)
        $byDate = $attendances->groupBy(fn ($a) => \Carbon\Carbon::parse($a->check_in_time)->toDateString());

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date    = Carbon::createFromDate($year, $month, $i)->toDateString();
            $dayName = Carbon::parse($date)->format('l');
            $recs    = $byDate->get($date);

            if ($recs && $recs->isNotEmpty()) {
                $att = $recs->first();
                $status = $att->status ?? 'present';

                if (in_array($status, ['present', 'verified', 'checked_in', 'checked_out'])) {
                    $presentDays += 1;
                } elseif ($status === 'half_day') {
                    $presentDays += 0.5;
                } elseif (in_array($status, ['holiday', 'on_leave', 'leave'])) {
                    $presentDays += 1;
                }

                if ($att->late_flag) $lateCount++;

                // Calculate overtime from check_in/check_out seconds
                if ($att->check_in_time && $att->check_out_time && $att->total_working_seconds) {
                    $standardSecs = 8 * 3600; // 8 hours standard
                    $extra = max(0, $att->total_working_seconds - $standardSecs);
                    $overtimeSecs += $extra;
                }
            } else {
                if ($holidays->has($date)) {
                    $presentDays += 1;
                } elseif (in_array($dayName, (array) $weeklyOffDays)) {
                    $presentDays += 1;
                }
            }
        }

        return [
            'present_days'   => $presentDays,
            'absent_days'    => max(0, $daysInMonth - $presentDays),
            'late_count'     => $lateCount,
            'late_deduction' => $lateCount * $lateDeductionAmount,
            'overtime_hours' => round($overtimeSecs / 3600, 2),
        ];
    }

    protected function calculateOvertimePay(Employee $employee, $hours)
    {
        $rule = AttendanceRule::where('is_default', true)->first();
        $multiplier = $rule ? $rule->ot_rate_multiplier : 1.5;

        // Enterprise rule: (Basic / 240 hours) * Hours * OT Multiplier
        $hourlyRate = $employee->base_salary / 240;

        return $hours * $hourlyRate * $multiplier;
    }

    protected function calculateSalesCommission(Employee $employee, $month, $year)
    {
        $commissionRates = SalesCommissionRate::where('employee_id', $employee->id)->get();
        if ($commissionRates->isEmpty()) {
            return 0;
        }

        $totalCommission = 0;
        foreach ($commissionRates as $rate) {
            $salesTotal = DB::table('pos_sale_items')
                ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
                ->join('inventory_products', 'pos_sale_items.product_id', '=', 'inventory_products.id')
                ->join('product_categories', 'inventory_products.category_id', '=', 'product_categories.id')
                ->where('pos_sales.created_by', $employee->user_id) // Assuming employee is linked to user via user_id
                ->where('product_categories.name', $rate->product_category)
                ->whereMonth('pos_sales.created_at', $month)
                ->whereYear('pos_sales.created_at', $year)
                ->sum('pos_sale_items.line_total');

            $totalCommission += ($salesTotal * ($rate->commission_percent / 100));
        }

        return $totalCommission;
    }

    protected function calculateKarigarMakingCharges(Employee $employee, $month, $year)
    {
        if (! $employee->is_karigar) {
            return 0;
        }

        $rates = KarigarRate::where('employee_id', $employee->id)->get();
        if ($rates->isEmpty()) {
            return 0;
        }

        $totalMakingCharges = 0;
        foreach ($rates as $rate) {
            // Calculate by weight (grams)
            $weightProduced = DB::table('production_jobs')
                ->join('product_boms', 'production_jobs.product_bom_id', '=', 'product_boms.id')
                ->join('product_categories', 'product_boms.category_id', '=', 'product_categories.id')
                ->where('production_jobs.karigar_id', $employee->id) // Assuming we added employee_id or use mapping
                ->where('product_categories.name', $rate->item_category)
                ->where('production_jobs.status', 'completed')
                ->whereMonth('production_jobs.actual_delivery_date', $month)
                ->whereYear('production_jobs.actual_delivery_date', $year)
                ->sum('production_jobs.metal_weight_received');

            $totalMakingCharges += ($weightProduced * $rate->rate_per_gram);

            // Calculate by pieces
            $piecesProduced = DB::table('production_jobs')
                ->join('product_boms', 'production_jobs.product_bom_id', '=', 'product_boms.id')
                ->join('product_categories', 'product_boms.category_id', '=', 'product_categories.id')
                ->where('production_jobs.karigar_id', $employee->id)
                ->where('product_categories.name', $rate->item_category)
                ->where('production_jobs.status', 'completed')
                ->whereMonth('production_jobs.actual_delivery_date', $month)
                ->whereYear('production_jobs.actual_delivery_date', $year)
                ->count(); // Or sum pieces if tracked

            $totalMakingCharges += ($piecesProduced * $rate->rate_per_piece);
        }

        return $totalMakingCharges;
    }

    protected function calculateSalaryComponents(Employee $employee, $basic)
    {
        $components = EmployeeSalaryStructure::with('component')
            ->where('employee_id', $employee->id)
            ->get();

        $earnings = collect();
        $deductions = collect();

        foreach ($components as $struct) {
            $amount = $struct->amount;
            if ($struct->component->calculation_type === 'percentage_of_basic') {
                $amount = ($basic * ($struct->amount / 100));
            }

            $item = (object) [
                'name' => $struct->component->name,
                'amount' => $amount,
            ];

            if ($struct->component->type === 'earning') {
                $earnings->push($item);
            } else {
                $deductions->push($item);
            }
        }

        return [
            'earnings' => $earnings,
            'deductions' => $deductions,
        ];
    }
}
