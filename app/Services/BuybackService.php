<?php

namespace App\Services;

use App\Models\Buyback;
use App\Models\Customer;
use App\Models\CustomerMetalLedger;
use App\Services\Accounting\AccountingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuybackService
{
    protected $accountingService;

    protected $marketRateService;

    public function __construct(
        AccountingService $accountingService,
        MarketRateService $marketRateService
    ) {
        $this->accountingService = $accountingService;
        $this->marketRateService = $marketRateService;
    }

    /**
     * Create a new Buyback record and post related ledger entries
     */
    public function processBuyback(array $data)
    {
        return DB::transaction(function () use ($data) {
            $buyback = Buyback::create([
                'buyback_number' => $this->generateBuybackNumber(),
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'item_description' => $data['item_description'],
                'metal_type' => $data['metal_type'],
                'gross_weight' => $data['gross_weight'],
                'stone_weight' => $data['stone_weight'] ?? 0,
                'net_weight' => $data['gross_weight'] - ($data['stone_weight'] ?? 0),
                'purity_reported' => $data['purity_reported'],
                'purity_tested' => $data['purity_tested'],
                'melting_loss_expected' => $data['melting_loss_expected'] ?? 0,
                'net_fine_weight' => $this->calculateFineWeight($data),
                'rate_applied' => $data['rate_applied'],
                'total_value' => $data['total_value'],
                'exchange_type' => $data['exchange_type'],
                'status' => 'completed',
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // 1. Update Customer Metal Ledger
            $this->postToMetalLedger($buyback);

            // 2. Accounting Integration
            $this->postToAccounting($buyback);

            return $buyback;
        });
    }

    protected function calculateFineWeight(array $data)
    {
        $netWeight = $data['gross_weight'] - ($data['stone_weight'] ?? 0);
        $effectivePurity = $data['purity_tested'] - ($data['melting_loss_expected'] ?? 0);

        return ($netWeight * $effectivePurity) / 100;
    }

    protected function generateBuybackNumber()
    {
        $prefix = 'BB-';
        $lastBuyback = Buyback::orderBy('id', 'desc')->first();
        $nextId = $lastBuyback ? $lastBuyback->id + 1 : 1;

        return $prefix.str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    protected function postToMetalLedger(Buyback $buyback)
    {
        $customer = $buyback->customer;
        $metalType = strtolower($buyback->metal_type);

        $currentGrossBalance = ($metalType === 'gold') ? $customer->current_gold_balance : $customer->current_silver_balance;
        $currentFineBalance = ($metalType === 'gold') ? $customer->current_fine_gold_balance : $customer->current_fine_silver_balance;

        $newGrossBalance = $currentGrossBalance + $buyback->net_weight;
        $newFineBalance = $currentFineBalance + $buyback->net_fine_weight;

        CustomerMetalLedger::create([
            'customer_id' => $buyback->customer_id,
            'branch_id' => $buyback->branch_id,
            'transaction_date' => now()->toDateString(),
            'transaction_type' => 'buyback',
            'metal_type' => $metalType,
            'purity_percentage' => $buyback->purity_tested,
            'weight_in' => $buyback->net_weight,
            'fine_weight_in' => $buyback->net_fine_weight,
            'weight_out' => 0,
            'fine_weight_out' => 0,
            'running_weight_balance' => $newGrossBalance,
            'running_fine_balance' => $newFineBalance,
            'reference_type' => 'buyback',
            'reference_id' => $buyback->id,
            'description' => "Old Gold Buyback - #{$buyback->buyback_number}",
            'created_by' => Auth::id(),
        ]);

        // Update Customer Running Balances
        if ($metalType === 'gold') {
            $customer->update([
                'current_gold_balance' => $newGrossBalance,
                'current_fine_gold_balance' => $newFineBalance,
            ]);
        } else {
            $customer->update([
                'current_silver_balance' => $newGrossBalance,
                'current_fine_silver_balance' => $newFineBalance,
            ]);
        }
    }

    protected function postToAccounting(Buyback $buyback)
    {
        try {
            $inventoryAccountCode = match (strtolower($buyback->metal_type)) {
                'gold' => '1310',
                'silver' => '1330',
                default => '1310',
            };

            $inventoryAccount = $this->accountingService->getAccountByCode($inventoryAccountCode);

            $offsetAccount = null;
            if ($buyback->exchange_type === 'cash') {
                $offsetAccount = $this->accountingService->getAccountByCode('1100'); // Cash
            } elseif ($buyback->exchange_type === 'account_credit') {
                $offsetAccount = $this->accountingService->getAccountByCode('2100'); // Accounts Payable (or Customer Advance)
            } else {
                // 'exchange' is usually handled within a sale, but if it's a standalone buyback for exchange,
                // we might credit a "Buyback Holding" or "Customer Advance" account.
                $offsetAccount = $this->accountingService->getAccountByCode('2100');
            }

            $this->accountingService->createJournalEntry([
                'entry_date' => now()->toDateString(),
                'reference_number' => $buyback->buyback_number,
                'narration' => "Old Gold Buyback - #{$buyback->buyback_number}",
                'reference_type' => 'buyback',
                'branch_id' => $buyback->branch_id,
                'items' => [
                    [
                        'account_id' => $inventoryAccount->id,
                        'debit' => $buyback->total_value,
                        'credit' => 0,
                        'description' => "Inventory In (Old Gold) - #{$buyback->buyback_number}",
                    ],
                    [
                        'account_id' => $offsetAccount->id,
                        'debit' => 0,
                        'credit' => $buyback->total_value,
                        'description' => "Payment/Credit for Buyback - #{$buyback->buyback_number}",
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Buyback Accounting Error: '.$e->getMessage());
        }
    }
}
