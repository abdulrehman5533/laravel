<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Installment;
use App\Models\InstallmentPlan;
use App\Models\InstallmentSchedule;
use App\Services\Accounting\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InstallmentController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index(Request $request): View
    {
        $query = Installment::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $installments = $query->with(['customer', 'plan', 'schedules'])
            ->paginate(20);

        return view('accounts.installment.index', compact('installments'));
    }

    public function create(): View
    {
        $plans = InstallmentPlan::where('is_active', true)->get();
        $customers = Customer::all();

        return view('accounts.installment.create', compact('plans', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plan_id' => 'required|exists:installment_plans,id',
            'total_amount' => 'required|numeric|min:0.01',
            'down_payment_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'internal_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $plan = InstallmentPlan::findOrFail($validated['plan_id']);
            $downPayment = $validated['down_payment_amount'] ?? 0;
            $amountToInstall = $validated['total_amount'] - $downPayment;
            $installmentAmount = $amountToInstall / $plan->number_of_installments;

            $installment = Installment::create([
                'customer_id' => $validated['customer_id'],
                'plan_id' => $validated['plan_id'],
                'total_amount' => $validated['total_amount'],
                'down_payment_amount' => $downPayment,
                'installment_amount' => $installmentAmount,
                'total_installments' => $plan->number_of_installments,
                'outstanding_amount' => $amountToInstall,
                'start_date' => $validated['start_date'],
                'status' => 'active',
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);

            // Create schedules
            $currentDate = Carbon::parse($validated['start_date']);
            for ($i = 1; $i <= $plan->number_of_installments; $i++) {
                InstallmentSchedule::create([
                    'installment_id' => $installment->id,
                    'sequence_number' => $i,
                    'due_date' => $currentDate->copy(),
                    'amount' => $installmentAmount,
                    'status' => 'pending',
                ]);

                // Move to next date based on frequency
                match ($plan->frequency) {
                    'daily' => $currentDate->addDay(),
                    'weekly' => $currentDate->addWeek(),
                    'monthly' => $currentDate->addMonth(),
                    'yearly' => $currentDate->addYear(),
                };
            }

            // If down payment > 0, we should record it as a payment
            if ($downPayment > 0) {
                // You might want to link this to a special schedule or just handle it as a separate record
                // For now, let's just make sure it's accounted for in the accounting system
                try {
                    $cashAccount = $this->accountingService->getAccountByCode('1001'); // Assuming 1001 is Cash
                    $receivableAccount = $this->accountingService->getAccountByCode('1201'); // Assuming 1201 is Accounts Receivable

                    $this->accountingService->createJournalEntry([
                        'entry_date' => now()->toDateString(),
                        'reference_number' => 'DP-'.$installment->id.'-'.time(),
                        'narration' => 'Down payment for Installment #'.$installment->id.' - Customer: '.$installment->customer->name,
                        'items' => [
                            ['account_id' => $cashAccount->id, 'debit' => $downPayment, 'credit' => 0],
                            ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => $downPayment],
                        ],
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail transaction if accounting fails (optional choice)
                    \Log::error('Failed to post down payment to accounting: '.$e->getMessage());
                }
            }

            return redirect()->route('accounts.installment.index')
                ->with('success', 'Installment plan created successfully'.($downPayment > 0 ? ' with Rs. '.number_format($downPayment, 2).' down payment.' : ''));
        });
    }

    public function show(Installment $installment): View
    {
        $installment->load(['customer', 'plan', 'schedules']);
        $overdueSchedules = $installment->getOverdueSchedules();

        return view('accounts.installment.show', compact('installment', 'overdueSchedules'));
    }

    public function edit(Installment $installment): View
    {
        $installment->load(['customer', 'plan']);
        $plans = InstallmentPlan::where('is_active', true)->get();

        return view('accounts.installment.edit', compact('installment', 'plans'));
    }

    public function update(Request $request, Installment $installment): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:installment_plans,id',
            'total_amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:active,completed,defaulted,suspended',
        ]);

        $installment->update($validated);

        return redirect()->route('accounts.installment.show', $installment)
            ->with('success', 'Installment updated successfully');
    }

    public function destroy(Installment $installment): RedirectResponse
    {
        $installment->delete();

        return redirect()->route('accounts.installment.index')
            ->with('success', 'Installment deleted successfully');
    }

    public function recordPayment(Request $request, InstallmentSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $schedule) {
            $amountPaidThisTime = $validated['amount_paid'];
            $newCumulativePaid = ($schedule->amount_paid ?? 0) + $amountPaidThisTime;
            $totalDueForSchedule = $schedule->amount + $schedule->late_fee;

            $status = 'partial';
            if ($newCumulativePaid >= $totalDueForSchedule) {
                $status = 'paid';
            }

            $schedule->update([
                'status' => $status,
                'paid_date' => now()->toDateString(),
                'amount_paid' => $newCumulativePaid,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? $schedule->notes,
            ]);

            // Update installment record
            $installment = $schedule->installment;

            // Calculate total paid across all schedules + down payment
            $totalPaidOnSchedules = $installment->schedules()->sum('amount_paid');
            $totalLateFees = $installment->schedules()->sum('late_fee');

            // We consider an installment "paid" if it's fully paid
            $fullyPaidCount = $installment->schedules()->where('status', 'paid')->count();

            $totalToPay = $installment->total_amount - $installment->down_payment_amount + $totalLateFees;
            $newOutstanding = max(0, $totalToPay - $totalPaidOnSchedules);

            $installment->update([
                'paid_installments' => $fullyPaidCount,
                'outstanding_amount' => $newOutstanding,
                'total_late_fees' => $totalLateFees,
                'status' => ($fullyPaidCount === $installment->total_installments && $newOutstanding <= 0) ? 'completed' : 'active',
            ]);

            // Post to Accounting
            try {
                $cashAccount = $this->accountingService->getAccountByCode('1001'); // Cash
                $receivableAccount = $this->accountingService->getAccountByCode('1201'); // AR
                $revenueAccount = $this->accountingService->getAccountByCode('4001'); // Revenue (for late fees)

                $items = [
                    ['account_id' => $cashAccount->id, 'debit' => $amountPaidThisTime, 'credit' => 0],
                ];

                // If there's a late fee being paid, part of it goes to revenue
                // Simplified: assuming late fee is covered first
                // In a real system you'd track how much of this specific payment is fee vs principal

                $items[] = ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => $amountPaidThisTime];

                $this->accountingService->createJournalEntry([
                    'entry_date' => now()->toDateString(),
                    'reference_number' => 'INST-'.$schedule->id.'-'.time(),
                    'narration' => "Installment Payment - Schedule #{$schedule->sequence_number} for Installment #{$installment->id}",
                    'items' => $items,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to post installment payment to accounting: '.$e->getMessage());
            }

            return back()->with('success', 'Payment of Rs. '.number_format($amountPaidThisTime, 2).' recorded successfully');
        });
    }

    public function skipInstallment(InstallmentSchedule $schedule): RedirectResponse
    {
        if ($schedule->installment->plan->skip_allowed === 0) {
            return back()->with('error', 'Skipping is not allowed for this plan');
        }

        $schedule->update(['status' => 'skipped']);
        $schedule->installment->increment('skipped_installments');

        return back()->with('success', 'Installment skipped');
    }

    public function applyLateFees(): RedirectResponse
    {
        $today = now()->toDateString();
        $overdueSchedules = InstallmentSchedule::where('status', 'pending')
            ->where('due_date', '<', $today)
            ->with(['installment.plan'])
            ->get();

        $appliedCount = 0;
        foreach ($overdueSchedules as $schedule) {
            $plan = $schedule->installment->plan;
            $graceDate = Carbon::parse($schedule->due_date)->addDays($plan->grace_period_days)->toDateString();

            if ($today > $graceDate && ! $schedule->late_fee_applied) {
                $lateFee = 0;
                if ($plan->late_fee_type === 'percentage') {
                    $lateFee = $schedule->amount * ($plan->late_fee_percentage / 100);
                } else {
                    $lateFee = $plan->fixed_late_fee_amount;
                }

                if ($lateFee > 0) {
                    $schedule->update([
                        'late_fee' => $lateFee,
                        'late_fee_applied' => true,
                        'status' => 'overdue',
                    ]);

                    $schedule->installment->increment('total_late_fees', $lateFee);
                    $schedule->installment->increment('outstanding_amount', $lateFee);
                    $appliedCount++;
                }
            }
        }

        return back()->with('success', "Applied late fees to {$appliedCount} overdue installments.");
    }

    public function sendReminder(InstallmentSchedule $schedule): RedirectResponse
    {
        // In a real system, you would send SMS/Email here
        $schedule->update(['reminder_sent_at' => now()]);

        return back()->with('success', 'Reminder marked as sent for Schedule #'.$schedule->sequence_number);
    }

    public function dashboard(): View
    {
        $activeInstallments = Installment::where('status', 'active')->count();
        $completedInstallments = Installment::where('status', 'completed')->count();
        $defaultedInstallments = Installment::where('status', 'defaulted')->count();

        $overdueSchedules = InstallmentSchedule::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        $totalOutstanding = Installment::where('status', 'active')
            ->sum('outstanding_amount');

        $todayDue = InstallmentSchedule::where('status', 'pending')
            ->whereDate('due_date', now()->toDateString())
            ->with(['installment.customer'])
            ->get();

        $recentPayments = InstallmentSchedule::where('status', 'paid')
            ->orderBy('paid_date', 'desc')
            ->limit(10)
            ->with(['installment.customer'])
            ->get();

        // Recovery Rate (Simplified)
        $totalBilled = InstallmentSchedule::whereIn('status', ['paid', 'partial', 'overdue', 'pending'])
            ->where('due_date', '<=', now()->toDateString())
            ->sum('amount');
        $totalCollected = InstallmentSchedule::whereIn('status', ['paid', 'partial'])
            ->sum('amount_paid');

        $recoveryRate = $totalBilled > 0 ? ($totalCollected / $totalBilled) * 100 : 0;

        return view('accounts.installment.dashboard', compact(
            'activeInstallments',
            'completedInstallments',
            'defaultedInstallments',
            'overdueSchedules',
            'totalOutstanding',
            'todayDue',
            'recentPayments',
            'recoveryRate'
        ));
    }
}
