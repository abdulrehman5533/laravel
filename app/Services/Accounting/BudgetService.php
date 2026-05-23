<?php

namespace App\Services\Accounting;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\BudgetApproval;

class BudgetService
{
    public function createBudget(array $data): Budget
    {
        $data['created_by'] = auth()->id();
        $budget = Budget::create($data);
        
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'chart_of_account_id' => $item['chart_of_account_id'] ?? null,
                    'category' => $item['category'],
                    'description' => $item['description'] ?? null,
                    'budgeted_amount' => $item['budgeted_amount'],
                ]);
            }
        }
        
        return $budget->load('items');
    }

    public function updateBudget(Budget $budget, array $data): Budget
    {
        $budget->update($data);
        
        if (isset($data['items'])) {
            $budget->items()->delete();
            foreach ($data['items'] as $item) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'chart_of_account_id' => $item['chart_of_account_id'] ?? null,
                    'category' => $item['category'],
                    'description' => $item['description'] ?? null,
                    'budgeted_amount' => $item['budgeted_amount'],
                ]);
            }
        }
        
        return $budget->load('items');
    }

    public function calculateVariance(Budget $budget): array
    {
        $items = $budget->items;
        $totalBudgeted = $items->sum('budgeted_amount');
        $totalActual = $items->sum('actual_amount');
        $variance = $totalBudgeted - $totalActual;
        $variancePercentage = $totalBudgeted > 0 ? ($variance / $totalBudgeted) * 100 : 0;

        return [
            'budgeted' => $totalBudgeted,
            'actual' => $totalActual,
            'variance' => $variance,
            'variance_percentage' => round($variancePercentage, 2),
            'status' => $variancePercentage < -10 ? 'exceeded' : ($variancePercentage < 0 ? 'warning' : 'on_track'),
        ];
    }

    public function approveBudget(Budget $budget, $userId, $comments = null): bool
    {
        $budget->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        BudgetApproval::create([
            'budget_id' => $budget->id,
            'approved_by' => $userId,
            'approval_level' => 1,
            'status' => 'approved',
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        return true;
    }

    public function rejectBudget(Budget $budget, $userId, $comments = null): bool
    {
        $budget->update(['status' => 'draft']);

        BudgetApproval::create([
            'budget_id' => $budget->id,
            'approved_by' => $userId,
            'approval_level' => 1,
            'status' => 'rejected',
            'comments' => $comments,
        ]);

        return true;
    }

    public function activateBudget(Budget $budget): bool
    {
        if ($budget->status !== 'approved') {
            return false;
        }

        $budget->update(['status' => 'active']);
        return true;
    }

    public function closeBudget(Budget $budget): bool
    {
        $budget->update(['status' => 'closed']);
        return true;
    }

    public function getBudgetsByBranch($branchId)
    {
        return Budget::where('branch_id', $branchId)->get();
    }

    public function getBudgetsByStatus($status)
    {
        return Budget::where('status', $status)->get();
    }
}
