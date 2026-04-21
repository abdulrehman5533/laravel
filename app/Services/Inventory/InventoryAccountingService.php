<?php

namespace App\Services\Inventory;

use App\Models\InventoryProduct;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Support\Facades\DB;

class InventoryAccountingService
{
    public function postInventoryValuation($branchId = null)
    {
        return DB::transaction(function () use ($branchId) {
            $query = InventoryProduct::active();

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $products = $query->get();

            $totalInventoryValue = $products->sum(fn ($p) => $p->current_stock * $p->cost_price);

            $journalEntry = JournalEntry::create([
                'entry_date' => now()->toDateString(),
                'description' => 'Inventory Valuation - '.now()->format('Y-m-d H:i:s'),
                'type' => 'inventory_valuation',
                'branch_id' => $branchId ?? auth()->user()->branch_id ?? 1,
                'created_by' => auth()->id(),
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getInventoryAssetAccount(),
                'debit' => $totalInventoryValue,
                'credit' => 0,
                'description' => 'Inventory Assets',
            ]);

            return $journalEntry;
        });
    }

    public function recordStockMovementAccounting($productId, $quantity, $type, $reference = null)
    {
        $product = InventoryProduct::findOrFail($productId);

        if ($type === 'sale') {
            return $this->recordSaleAccounting($product, $quantity, $reference);
        } elseif ($type === 'purchase') {
            return $this->recordPurchaseAccounting($product, $quantity, $reference);
        } elseif ($type === 'wastage') {
            return $this->recordWastageAccounting($product, $quantity);
        } elseif ($type === 'damage') {
            return $this->recordDamageAccounting($product, $quantity);
        }
    }

    private function recordSaleAccounting($product, $quantity, $reference)
    {
        return DB::transaction(function () use ($product, $quantity, $reference) {
            $costOfGoodsSold = $quantity * $product->cost_price;

            $journalEntry = JournalEntry::create([
                'entry_date' => now()->toDateString(),
                'description' => "COGS - {$product->name} (Sale: {$reference})",
                'type' => 'cogs',
                'branch_id' => $product->branch_id,
                'created_by' => auth()->id(),
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getCOGSAccount(),
                'debit' => $costOfGoodsSold,
                'credit' => 0,
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getInventoryAssetAccount(),
                'debit' => 0,
                'credit' => $costOfGoodsSold,
            ]);

            return $journalEntry;
        });
    }

    private function recordPurchaseAccounting($product, $quantity, $reference)
    {
        return DB::transaction(function () use ($product, $quantity) {
            $purchaseCost = $quantity * $product->cost_price;

            $journalEntry = JournalEntry::create([
                'entry_date' => now()->toDateString(),
                'description' => "Inventory Purchase - {$product->name}",
                'type' => 'purchase',
                'branch_id' => $product->branch_id,
                'created_by' => auth()->id(),
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getInventoryAssetAccount(),
                'debit' => $purchaseCost,
                'credit' => 0,
            ]);

            return $journalEntry;
        });
    }

    private function recordWastageAccounting($product, $quantity)
    {
        return DB::transaction(function () use ($product, $quantity) {
            $wastageValue = $quantity * $product->cost_price;

            $journalEntry = JournalEntry::create([
                'entry_date' => now()->toDateString(),
                'description' => "Wastage Loss - {$product->name}",
                'type' => 'wastage',
                'branch_id' => $product->branch_id,
                'created_by' => auth()->id(),
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getWastageExpenseAccount(),
                'debit' => $wastageValue,
                'credit' => 0,
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getInventoryAssetAccount(),
                'debit' => 0,
                'credit' => $wastageValue,
            ]);

            return $journalEntry;
        });
    }

    private function recordDamageAccounting($product, $quantity)
    {
        return DB::transaction(function () use ($product, $quantity) {
            $damageValue = $quantity * $product->cost_price;

            $journalEntry = JournalEntry::create([
                'entry_date' => now()->toDateString(),
                'description' => "Damage Loss - {$product->name}",
                'type' => 'damage',
                'branch_id' => $product->branch_id,
                'created_by' => auth()->id(),
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getDamageExpenseAccount(),
                'debit' => $damageValue,
                'credit' => 0,
            ]);

            JournalEntryItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $this->getInventoryAssetAccount(),
                'debit' => 0,
                'credit' => $damageValue,
            ]);

            return $journalEntry;
        });
    }

    public function getInventoryValuationReport($fromDate, $toDate, $branchId = null)
    {
        $query = InventoryProduct::whereHas('stockMovements', function ($q) use ($fromDate, $toDate) {
            $q->whereBetween('created_at', [$fromDate, $toDate]);
        });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $products = $query->get();

        return [
            'total_inventory_value' => $products->sum(fn ($p) => $p->current_stock * $p->cost_price),
            'total_retail_value' => $products->sum(fn ($p) => $p->current_stock * $p->selling_price),
            'products' => $products,
        ];
    }

    private function getInventoryAssetAccount()
    {
        return \App\Models\ChartOfAccount::where('account_code', 'like', '%1200%')
            ->orWhere('name', 'like', '%inventory%')
            ->first()?->id ?? 1;
    }

    private function getCOGSAccount()
    {
        return \App\Models\ChartOfAccount::where('account_code', 'like', '%5000%')
            ->orWhere('name', 'like', '%COGS%')
            ->first()?->id ?? 2;
    }

    private function getWastageExpenseAccount()
    {
        return \App\Models\ChartOfAccount::where('name', 'like', '%wastage%')
            ->orWhere('account_code', 'like', '%6100%')
            ->first()?->id ?? 3;
    }

    private function getDamageExpenseAccount()
    {
        return \App\Models\ChartOfAccount::where('name', 'like', '%damage%')
            ->orWhere('account_code', 'like', '%6110%')
            ->first()?->id ?? 4;
    }
}
