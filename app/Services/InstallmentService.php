<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Installment;
use App\Models\InstallmentPlan;
use App\Models\InstallmentSchedule;
use Carbon\Carbon;

class InstallmentService
{
    public function createInstallmentPlan(Customer $customer, float $totalAmount, InstallmentPlan $plan, string $startDate): Installment
    {
        $installmentAmount = $totalAmount / $plan->number_of_installments;
        $start = Carbon::parse($startDate);
        $end = $start->copy();

        // Calculate end date
        for ($i = 1; $i < $plan->number_of_installments; $i++) {
            match ($plan->frequency) {
                'daily' => $end->addDay(),
                'weekly' => $end->addWeek(),
                'monthly' => $end->addMonth(),
                'yearly' => $end->addYear(),
            };
        }

        $installment = Installment::create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'total_amount' => $totalAmount,
            'installment_amount' => $installmentAmount,
            'total_installments' => $plan->number_of_installments,
            'outstanding_amount' => $totalAmount,
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'active',
        ]);

        $this->generateSchedule($installment, $plan, $start);

        return $installment;
    }

    protected function generateSchedule(Installment $installment, InstallmentPlan $plan, Carbon $startDate): void
    {
        $currentDate = $startDate->copy();

        for ($i = 1; $i <= $plan->number_of_installments; $i++) {
            InstallmentSchedule::create([
                'installment_id' => $installment->id,
                'sequence_number' => $i,
                'due_date' => $currentDate->copy(),
                'amount' => $installment->installment_amount,
                'status' => 'pending',
            ]);

            match ($plan->frequency) {
                'daily' => $currentDate->addDay(),
                'weekly' => $currentDate->addWeek(),
                'monthly' => $currentDate->addMonth(),
                'yearly' => $currentDate->addYear(),
            };
        }
    }

    public function recordPayment(InstallmentSchedule $schedule, float $amountPaid, string $paymentMethod): void
    {
        $schedule->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'amount_paid' => $amountPaid,
            'payment_method' => $paymentMethod,
        ]);

        $installment = $schedule->installment;
        $paidSchedules = $installment->schedules()->where('status', 'paid')->count();
        $newOutstanding = max(0, $installment->total_amount - ($paidSchedules * $installment->installment_amount));

        $installment->update([
            'paid_installments' => $paidSchedules,
            'outstanding_amount' => $newOutstanding,
            'status' => $paidSchedules === $installment->total_installments ? 'completed' : 'active',
        ]);
    }

    public function calculateLateFee(InstallmentSchedule $schedule): float
    {
        if (! $schedule->isOverdue()) {
            return 0;
        }

        $daysOverdue = now()->diffInDays($schedule->due_date);
        $plan = $schedule->installment->plan;

        return ($schedule->amount * $plan->late_fee_percentage / 100) * ($daysOverdue / 30);
    }

    public function getOverdueSchedules(Installment $installment): array
    {
        return $installment->schedules()
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get()
            ->toArray();
    }

    public function markAsDefaulted(Installment $installment): void
    {
        $installment->update(['status' => 'defaulted']);
    }

    public function generateReminders(): array
    {
        $reminders = [];
        $schedules = InstallmentSchedule::where('status', 'pending')
            ->where('due_date', '<=', now()->addDays(3)->toDateString())
            ->get();

        foreach ($schedules as $schedule) {
            $customer = $schedule->installment->customer;
            $reminders[] = [
                'customer_id' => $customer->id,
                'customer_phone' => $customer->phone,
                'amount' => $schedule->amount,
                'due_date' => $schedule->due_date,
                'message_type' => $schedule->due_date->isToday() ? 'due_today' : 'upcoming_due',
            ];
        }

        return $reminders;
    }
}
