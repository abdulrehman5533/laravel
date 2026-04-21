<?php

namespace App\Services\POS;

use App\Models\PosPayment;
use App\Models\PosSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordSinglePayment(PosSale $sale, array $paymentData): PosPayment
    {
        return DB::transaction(function () use ($sale, $paymentData) {
            $payment = PosPayment::create([
                'pos_sale_id' => $sale->id,
                'pos_customer_id' => $sale->pos_customer_id,
                'payment_method' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? $sale->currency,
                'bank_name' => $paymentData['bank_name'] ?? null,
                'cheque_number' => $paymentData['cheque_number'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by' => auth()->id(),
                'status' => 'completed',
            ]);

            $sale->recalculateTotals();

            $sale->addAuditLog('payment_recorded', "Payment of {$payment->amount} {$payment->currency} via {$payment->payment_method}", auth()->id());

            return $payment;
        });
    }

    public function recordPartialPayment(PosSale $sale, array $paymentData): PosPayment
    {
        return DB::transaction(function () use ($sale, $paymentData) {
            $amount = $paymentData['amount'];

            if ($amount > $sale->outstanding_balance) {
                throw new \Exception("Payment amount cannot exceed outstanding balance of {$sale->outstanding_balance}");
            }

            $payment = $this->recordSinglePayment($sale, $paymentData);

            if ($paymentData['due_date'] ?? null) {
                $sale->update(['due_date' => $paymentData['due_date']]);
            }

            $sale->addAuditLog('partial_payment', "Partial payment recorded. Outstanding: {$sale->outstanding_balance}", auth()->id());

            return $payment;
        });
    }

    public function recordSplitPayment(PosSale $sale, array $splitPayments): array
    {
        return DB::transaction(function () use ($sale, $splitPayments) {
            $totalAmount = 0;
            $payments = [];

            foreach ($splitPayments as $paymentData) {
                $totalAmount += $paymentData['amount'];

                if ($totalAmount > $sale->outstanding_balance) {
                    throw new \Exception('Total split payment amount exceeds outstanding balance');
                }

                $payment = $this->recordSinglePayment($sale, $paymentData);
                $payments[] = $payment;
            }

            $sale->addAuditLog('split_payment', "Split payment recorded ({count($payments)} payments, total: {$totalAmount})", auth()->id());

            return $payments;
        });
    }

    public function createInstallmentPlan(PosSale $sale, array $planData): void
    {
        DB::transaction(function () use ($sale, $planData) {
            $amount = $sale->outstanding_balance;
            $numberOfInstallments = $planData['number_of_installments'];
            $firstPaymentDate = Carbon::parse($planData['first_payment_date']);
            $installmentAmount = $amount / $numberOfInstallments;
            $frequency = $planData['frequency'] ?? 'monthly';

            $installments = [];

            for ($i = 1; $i <= $numberOfInstallments; $i++) {
                $dueDate = match ($frequency) {
                    'weekly' => $firstPaymentDate->clone()->addWeeks($i - 1),
                    'fortnightly' => $firstPaymentDate->clone()->addWeeks(($i - 1) * 2),
                    'monthly' => $firstPaymentDate->clone()->addMonths($i - 1),
                    'quarterly' => $firstPaymentDate->clone()->addMonths(($i - 1) * 3),
                    default => $firstPaymentDate->clone()->addMonths($i - 1),
                };

                $finalInstallment = ($i === $numberOfInstallments);
                $finalAmount = $finalInstallment ? ($amount - (($numberOfInstallments - 1) * $installmentAmount)) : $installmentAmount;

                $installments[] = [
                    'installment_number' => $i,
                    'amount' => $finalAmount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'payment_date' => null,
                ];
            }

            $sale->update([
                'installment_plan' => [
                    'status' => 'active',
                    'frequency' => $frequency,
                    'total_amount' => $amount,
                    'number_of_installments' => $numberOfInstallments,
                    'installments' => $installments,
                    'created_at' => now(),
                ],
            ]);

            $sale->addAuditLog('installment_plan_created', "Installment plan created: {$numberOfInstallments} installments of {$installmentAmount}", auth()->id());
        });
    }

    public function recordInstallmentPayment(PosSale $sale, int $installmentNumber, array $paymentData): PosPayment
    {
        return DB::transaction(function () use ($sale, $installmentNumber, $paymentData) {
            $plan = $sale->installment_plan;

            if (! $plan || $plan['status'] !== 'active') {
                throw new \Exception('No active installment plan for this sale');
            }

            if (! isset($plan['installments'][$installmentNumber - 1])) {
                throw new \Exception('Invalid installment number');
            }

            $installment = $plan['installments'][$installmentNumber - 1];

            if ($installment['status'] === 'paid') {
                throw new \Exception('This installment has already been paid');
            }

            if ($paymentData['amount'] < $installment['amount']) {
                throw new \Exception("Installment amount must be at least {$installment['amount']}");
            }

            $paymentData['reference'] = "INSTALLMENT-{$installmentNumber}";

            $payment = $this->recordSinglePayment($sale, $paymentData);

            $plan['installments'][$installmentNumber - 1]['status'] = 'paid';
            $plan['installments'][$installmentNumber - 1]['payment_date'] = now();

            $allPaid = collect($plan['installments'])->every(fn ($i) => $i['status'] === 'paid');
            if ($allPaid) {
                $plan['status'] = 'completed';
            }

            $sale->update(['installment_plan' => $plan]);

            $sale->addAuditLog('installment_paid', "Installment {$installmentNumber} paid. Amount: {$payment->amount}", auth()->id());

            return $payment;
        });
    }

    public function cancelInstallmentPlan(PosSale $sale, ?string $reason = null): void
    {
        DB::transaction(function () use ($sale, $reason) {
            if (! $sale->installment_plan) {
                throw new \Exception('No installment plan exists for this sale');
            }

            $sale->update([
                'installment_plan' => array_merge(
                    $sale->installment_plan,
                    ['status' => 'cancelled', 'cancelled_reason' => $reason]
                ),
            ]);

            $sale->addAuditLog('installment_plan_cancelled', "Installment plan cancelled. Reason: {$reason}", auth()->id());
        });
    }

    public function getInstallmentSchedule(PosSale $sale): ?array
    {
        return $sale->installment_plan ? $sale->installment_plan['installments'] ?? [] : null;
    }

    public function getNextInstallmentDue(PosSale $sale): ?array
    {
        $plan = $sale->installment_plan;

        if (! $plan || $plan['status'] !== 'active') {
            return null;
        }

        foreach ($plan['installments'] as $installment) {
            if ($installment['status'] === 'pending') {
                return $installment;
            }
        }

        return null;
    }

    public function getPaymentHistory(PosSale $sale)
    {
        return $sale->payments()
            ->with('recordedByUser')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPaymentSummary(PosSale $sale): array
    {
        $payments = $sale->payments()->get();

        return [
            'total_amount' => $sale->total,
            'total_paid' => $payments->sum('amount'),
            'outstanding_balance' => $sale->outstanding_balance,
            'payment_count' => $payments->count(),
            'payment_status' => $sale->payment_status,
            'payment_methods' => $payments->groupBy('payment_method')->map->count(),
            'last_payment_date' => $payments->max('created_at'),
            'first_payment_date' => $payments->min('created_at'),
        ];
    }

    public function getPendingInstallments(PosSale $sale)
    {
        $plan = $sale->installment_plan;

        if (! $plan) {
            return [];
        }

        return array_filter(
            $plan['installments'] ?? [],
            fn ($inst) => $inst['status'] === 'pending' && Carbon::parse($inst['due_date'])->isPast()
        );
    }

    public function getUpcomingInstallments(PosSale $sale)
    {
        $plan = $sale->installment_plan;

        if (! $plan) {
            return [];
        }

        return array_filter(
            $plan['installments'] ?? [],
            fn ($inst) => $inst['status'] === 'pending' && Carbon::parse($inst['due_date'])->isFuture()
        );
    }
}
