<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Cashbook;
use App\Models\CashierShift;

class CashbookService
{
    public function recordCashIn(Branch $branch, float $amount, string $category, ?string $subcategory = null, ?string $description = null, string $paymentMethod = 'cash'): Cashbook
    {
        return Cashbook::create([
            'branch_id' => $branch->id,
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'entry_type' => 'cash_in',
            'category' => $category,
            'subcategory' => $subcategory,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'description' => $description,
            'status' => 'pending',
        ]);
    }

    public function recordCashOut(Branch $branch, float $amount, string $category, ?string $subcategory = null, ?string $description = null, string $paymentMethod = 'cash'): Cashbook
    {
        return Cashbook::create([
            'branch_id' => $branch->id,
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'entry_type' => 'cash_out',
            'category' => $category,
            'subcategory' => $subcategory,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'description' => $description,
            'status' => 'pending',
        ]);
    }

    public function getDailyBalance(Branch $branch, string $date): array
    {
        $cashIn = Cashbook::where('branch_id', $branch->id)
            ->whereDate('date', $date)
            ->where('entry_type', 'cash_in')
            ->sum('amount');

        $cashOut = Cashbook::where('branch_id', $branch->id)
            ->whereDate('date', $date)
            ->where('entry_type', 'cash_out')
            ->sum('amount');

        return [
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'balance' => $cashIn - $cashOut,
        ];
    }

    public function getDailyBreakdown(Branch $branch, string $date): array
    {
        $entries = Cashbook::where('branch_id', $branch->id)
            ->whereDate('date', $date)
            ->get()
            ->groupBy('category');

        $breakdown = [];
        foreach ($entries as $category => $items) {
            $breakdown[$category] = [
                'cash_in' => $items->where('entry_type', 'cash_in')->sum('amount'),
                'cash_out' => $items->where('entry_type', 'cash_out')->sum('amount'),
            ];
        }

        return $breakdown;
    }

    public function getCurrentShift(Branch $branch): ?CashierShift
    {
        return CashierShift::where('branch_id', $branch->id)
            ->where('shift_date', now()->toDateString())
            ->where('status', 'open')
            ->first();
    }

    public function closeShiftAndReconcile(CashierShift $shift, float $physicalCount, ?string $notes = null): array
    {
        $cashIn = Cashbook::where('branch_id', $shift->branch_id)
            ->whereDate('date', $shift->shift_date)
            ->where('entry_type', 'cash_in')
            ->sum('amount');

        $cashOut = Cashbook::where('branch_id', $shift->branch_id)
            ->whereDate('date', $shift->shift_date)
            ->where('entry_type', 'cash_out')
            ->sum('amount');

        $closingBalance = $shift->opening_balance + $cashIn - $cashOut;
        $variance = $physicalCount - $closingBalance;

        $shift->update([
            'total_cash_in' => $cashIn,
            'total_cash_out' => $cashOut,
            'closing_balance' => $closingBalance,
            'physical_count' => $physicalCount,
            'variance' => $variance,
            'notes' => $notes,
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        return [
            'opening_balance' => $shift->opening_balance,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'closing_balance' => $closingBalance,
            'physical_count' => $physicalCount,
            'variance' => $variance,
        ];
    }

    public function verifyCashbook(Cashbook $cashbook): void
    {
        $cashbook->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
    }
}
