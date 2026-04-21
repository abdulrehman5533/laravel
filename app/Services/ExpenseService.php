<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;

class ExpenseService
{
    public function createExpense(Branch $branch, array $data): Expense
    {
        $data['branch_id'] = $branch->id;
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';

        return Expense::create($data);
    }

    public function approveExpense(Expense $expense): void
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // 1. Post to General Ledger
        $glService = app(GeneralLedgerService::class);
        $glService->postExpense($expense);

        // 2. Auto-create cashbook entry
        $cashbookService = app(CashbookService::class);
        $cashbookService->recordCashOut(
            $expense->branch,
            $expense->amount,
            $expense->category->name,
            $expense->subcategory?->name,
            "Approved: {$expense->description}"
        );
    }

    public function getMonthlyExpenses(Branch $branch, ?string $month = null): array
    {
        $month = $month ?? now()->format('Y-m');

        return Expense::where('branch_id', $branch->id)
            ->where('status', 'approved')
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->get()
            ->groupBy('category_id')
            ->map(fn ($expenses) => [
                'category' => $expenses->first()->category->name,
                'total' => $expenses->sum('amount'),
                'count' => $expenses->count(),
            ])
            ->toArray();
    }

    public function checkMonthlyLimit(ExpenseCategory $category, float $amount): bool
    {
        if (! $category->monthly_limit) {
            return true;
        }

        $monthlyUsed = Expense::where('category_id', $category->id)
            ->where('status', 'approved')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        return ($monthlyUsed + $amount) <= $category->monthly_limit;
    }

    public function getExpenseTrends(Branch $branch, string $period = 'monthly'): array
    {
        $expenses = Expense::where('branch_id', $branch->id)
            ->where('status', 'approved')
            ->get();

        if ($period === 'daily') {
            return $expenses->groupBy(fn ($e) => $e->date)->map(fn ($g) => $g->sum('amount'))->toArray();
        }

        if ($period === 'monthly') {
            return $expenses->groupBy(fn ($e) => $e->date->format('Y-m'))->map(fn ($g) => $g->sum('amount'))->toArray();
        }

        if ($period === 'yearly') {
            return $expenses->groupBy(fn ($e) => $e->date->format('Y'))->map(fn ($g) => $g->sum('amount'))->toArray();
        }

        return [];
    }
}
