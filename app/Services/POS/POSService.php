<?php

namespace App\Services\POS;

use App\Models\Customer;
use App\Models\InventoryProduct;
use App\Models\PosPricingTier;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Services\Accounting\SalesAccountingService;
use App\Services\PricingEngineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSService
{
    public function __construct(
        private SalesAccountingService $accountingService,
        private PricingEngineService $pricingEngine
    ) {}

    /**
     * Scan barcode and fetch product data
     */
    public function scanBarcode(string $sku): ?array
    {
        // Search across inventory products
        $product = InventoryProduct::where('sku', $sku)
            ->orWhere('barcode', $sku)
            ->first();

        if (! $product) {
            return null;
        }

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'description' => $product->name,
            'unit_price' => $product->selling_price ?? 0,
            'unit' => $product->unit ?? 'pcs',
            'stock' => $product->current_stock,
            'weight' => $product->weight,
            'purity' => $product->purity?->name,
            'making_charge' => (float) $product->making_charge_value,
            'wastage' => (float) $product->wastage_percentage,
        ];
    }

    /**
     * Create a new POS sale (draft)
     */
    public function createSale(array $data): PosSale
    {
        return DB::transaction(function () use ($data) {
            $invoiceNo = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
            $user = Auth::user();

            $sale = PosSale::create([
                'invoice_no' => $invoiceNo,
                'branch_id' => $data['branch_id'] ?? ($user?->branch_id ?? 1),
                'pos_customer_id' => $data['customer_id'] ?? null,
                'created_by' => Auth::id(),
                'sale_time' => now(),
                'currency' => $data['currency'] ?? 'PKR',
                'status' => 'open',
                'payment_status' => 'unpaid',
                'is_wholesale' => $data['is_wholesale'] ?? false,
            ]);

            $sale->logAudit('created', 'New POS Sale initialized');

            return $sale;
        });
    }

    /**
     * Add item to sale with automatic calculations and stock validation
     */
    public function addItem(PosSale $sale, array $itemData): PosSaleItem
    {
        return DB::transaction(function () use ($sale, $itemData) {
            if ($sale->status !== 'open') {
                throw new \Exception('Cannot add items to a '.$sale->status.' sale.');
            }

            $product = null;
            if (isset($itemData['product_id'])) {
                $product = InventoryProduct::findOrFail($itemData['product_id']);
            } elseif (isset($itemData['sku'])) {
                $product = InventoryProduct::where('sku', $itemData['sku'])
                    ->orWhere('barcode', $itemData['sku'])
                    ->first();
            }

            if ($product) {
                // Stock Validation
                $requestedQuantity = $itemData['quantity'] ?? 1;
                if ($product->current_stock < $requestedQuantity) {
                    $available = (float) $product->current_stock;
                    throw new \Exception("Stock Alert: '{$product->name}' is out of stock. Only {$available} available in inventory.");
                }
            }

            // Get current gold rate if not provided
            if (! isset($itemData['gold_rate'])) {
                $purity = $itemData['gold_purity'] ?? $product?->purity?->name ?? null;
                $currentGoldRate = \App\Models\GoldRate::getTodayRate();
                if ($currentGoldRate) {
                    $itemData['gold_rate'] = $currentGoldRate->getRateByPurity($purity);
                } else {
                    $itemData['gold_rate'] = 0;
                }
            }

            // Advanced Enterprise Pricing Engine Integration
            $pricingData = $this->pricingEngine->calculatePrice(
                $product,
                $sale->customer,
                (int) ($itemData['quantity'] ?? 1)
            );

            $unitPrice = $itemData['unit_price'] ?? $pricingData['base_price'];
            $makingCharge = $itemData['making_charge'] ?? $pricingData['making_charges'];
            $wastageAmount = $pricingData['wastage_charges'];

            // Fetch primary stone attributes if available
            $stoneAttr = $product ? $product->stoneAttributes()->first() : null;

            $item = $sale->items()->create([
                'product_id' => $product?->id,
                'sku' => $itemData['sku'] ?? $product?->sku,
                'description' => $itemData['description'] ?? $product?->name,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit' => $itemData['unit'] ?? $product?->unit ?? 'pcs',
                'unit_price' => $unitPrice,
                'making_charge' => $itemData['making_charge'] ?? $product?->making_charge_value ?? 0,
                'making_charge_type' => $itemData['making_charge_type'] ?? $product?->making_charge_type ?? 'fixed',
                'wastage_percent' => $itemData['wastage_percent'] ?? $product?->wastage_percentage ?? 0,
                'tax_percent' => $itemData['tax_percent'] ?? $product?->tax_rate ?? 0,
                'weight' => $itemData['weight'] ?? $product?->weight ?? null,
                'gross_weight' => $itemData['gross_weight'] ?? $product?->gross_weight ?? null,
                'stone_weight' => $itemData['stone_weight'] ?? ($product ? ($product->gross_weight - $product->net_weight) : 0),
                'net_weight' => $itemData['net_weight'] ?? $product?->net_weight ?? null,
                'gold_rate' => $itemData['gold_rate'] ?? null,
                'gold_purity' => $itemData['gold_purity'] ?? $product?->purity?->name ?? null,
                'stone_count' => $itemData['stone_count'] ?? $product?->stone_count ?? 0,
                'stone_carat' => $itemData['stone_carat'] ?? $product?->stone_carat ?? 0,
                'stone_type' => $itemData['stone_type'] ?? $stoneAttr?->stone_type ?? $product?->stone_type ?? null,
                'stone_cut' => $itemData['stone_cut'] ?? $stoneAttr?->cut,
                'stone_clarity' => $itemData['stone_clarity'] ?? $stoneAttr?->clarity,
                'stone_color' => $itemData['stone_color'] ?? $stoneAttr?->color,
                'stone_certification' => $itemData['stone_certification'] ?? $stoneAttr?->certification,
                'certificate_no' => $itemData['certificate_no'] ?? $stoneAttr?->certificate_number ?? $product?->certificate_no,
                'stone_price' => $itemData['stone_price'] ?? 0,
                'discount_percent' => $itemData['discount_percent'] ?? 0,
                'discount_amount' => $itemData['discount_amount'] ?? 0,
            ]);

            $item->calculateLineTotals();
            $sale->recalculateTotals();

            // Purity Override Audit Log
            if ($product && isset($itemData['gold_purity']) && $product->purity) {
                $oldPurity = (float) filter_var($product->purity->name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $newPurity = (float) filter_var($itemData['gold_purity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                if ($oldPurity != $newPurity) {
                    \App\Models\PurityOverrideLog::logOverride(
                        $item,
                        $oldPurity,
                        $newPurity,
                        $itemData['purity_override_reason'] ?? 'Manual override during POS'
                    );
                }
            }

            return $item;
        });
    }

    /**
     * Apply wholesale pricing tier
     */
    public function applyPricingTier(PosSale $sale, int $tierId): void
    {
        $tier = PosPricingTier::findOrFail($tierId);
        $sale->update(['meta->pricing_tier_id' => $tierId]);

        foreach ($sale->items as $item) {
            $discountAmount = ($item->line_total * $tier->discount_percent) / 100;
            $item->update(['line_total' => $item->line_total - $discountAmount]);
        }

        $sale->recalculateTotals();
    }

    /**
     * Calculate multi-currency conversion
     */
    public function convertCurrency(PosSale $sale, string $toCurrency, float $rate): void
    {
        $sale->update([
            'currency' => $toCurrency,
            'exchange_rate' => $rate,
            'total' => $sale->total * $rate,
        ]);
    }

    /**
     * Complete sale and finalize totals
     */
    public function completeSale(PosSale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if (in_array($sale->status, ['open', 'held'])) {
                // Final calculation
                $sale->recalculateTotals();

                $sale->update([
                    'status' => 'completed',
                    'sale_time' => $sale->sale_time ?? now(),
                ]);

                // Deduct stock if not already moved
                if (empty($sale->stock_moved)) {
                    foreach ($sale->items as $item) {
                        if ($item->product_id) {
                            $product = InventoryProduct::find($item->product_id);
                            if ($product) {
                                // Enterprise Safeguard: Double check stock before final deduction
                                if ($product->current_stock < $item->quantity) {
                                    $available = (float) $product->current_stock;
                                    throw new \Exception("Stock Depleted: '{$product->name}' ran out during processing. Only {$available} units remaining.");
                                }
                                $product->updateStock($item->quantity, 'subtract', 'sale:'.$sale->id);
                            }
                        }
                    }
                    $sale->update(['stock_moved' => true]);
                }

                // Finalized Payment Status
                $sale->update([
                    'payment_status' => $sale->outstanding_balance <= 0 ? 'paid' : ($sale->outstanding_balance < $sale->total ? 'partial' : 'unpaid'),
                ]);

                // Enterprise Credit Exposure Check
                if ($sale->customer && $sale->outstanding_balance > 0) {
                    $report = $sale->customer->getCreditExposureReport();
                    if ($report['is_over_limit'] || $report['has_overdue']) {
                        throw new \Exception("Credit Block: Customer '{$sale->customer->name}' has ".
                            ($report['is_over_limit'] ? 'exceeded credit limit' : 'overdue invoices').
                            ". Current Exposure: {$report['total_exposure']}, Overdue: {$report['overdue_balance']}");
                    }
                }

                // Post to Accounting
                $this->accountingService->postSaleToAccounting($sale);

                $sale->logAudit('completed', 'Sale finalized and accounting entries generated');
            }
        });
    }

    /**
     * Record a payment for a sale
     */
    public function recordPayment(PosSale $sale, array $paymentData): \App\Models\PosPayment
    {
        return DB::transaction(function () use ($sale, $paymentData) {
            $payment = $sale->payments()->create([
                'pos_customer_id' => $sale->pos_customer_id,
                'payment_method' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'currency' => $sale->currency,
                'exchange_rate' => $sale->exchange_rate ?? 1,
                'bank_name' => $paymentData['bank_name'] ?? null,
                'cheque_number' => $paymentData['cheque_number'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by' => Auth::id(),
                'status' => 'completed',
            ]);

            // If sale is already completed, we need to update accounting entries
            if ($sale->status === 'completed') {
                $this->accountingService->postSaleToAccounting($sale);
            }

            $sale->recalculateTotals();

            // Update payment_method on sale for quick reference (primary method or 'multi')
            $methods = $sale->payments()->pluck('payment_method')->unique();
            $sale->update([
                'payment_method' => $methods->count() > 1 ? 'multi' : $methods->first(),
                'payment_status' => $sale->outstanding_balance <= 0 ? 'paid' : 'partial',
            ]);

            $sale->logAudit('payment_received', "Payment of {$payment->amount} received via {$payment->payment_method}");

            return $payment;
        });
    }

    /**
     * Hold current sale (pause for later)
     */
    public function holdSale(PosSale $sale, ?string $note = null): void
    {
        $sale->hold(Auth::id(), $note);
    }

    /**
     * Resume a held sale
     */
    public function resumeHeldSale(int $holdId): PosSale
    {
        $hold = \App\Models\PosHold::findOrFail($holdId);
        $snapshot = $hold->sale_snapshot;

        // Create new sale from snapshot
        $sale = PosSale::create($snapshot);

        // Restore items
        if (isset($snapshot['items'])) {
            foreach ($snapshot['items'] as $itemData) {
                $sale->items()->create($itemData);
            }
        }

        return $sale;
    }

    /**
     * Calculate totals with GST and charges
     */
    public function calculateSaleTotals(PosSale $sale): void
    {
        $sale->recalculateTotals();
    }

    /**
     * Get applicable pricing tier for customer
     */
    public function getApplicableTier(Customer $customer): ?PosPricingTier
    {
        $context = ['customer_type' => $customer->customer_type];

        return PosPricingTier::where('branch_id', $customer->branch_id)
            ->get()
            ->first(fn ($tier) => $tier->appliesTo($context));
    }

    /**
     * Convert an open sale into a Memo (Consignment)
     */
    public function convertToMemo(PosSale $sale, string $expiryDate): void
    {
        DB::transaction(function () use ($sale, $expiryDate) {
            if ($sale->status !== 'open') {
                throw new \Exception('Only open sales can be converted to memos.');
            }

            $sale->update([
                'is_memo' => true,
                'memo_expiry_date' => $expiryDate,
                'consignment_status' => 'issued',
                'status' => 'memo',
            ]);

            // Deduct stock for memo
            if (empty($sale->stock_moved)) {
                foreach ($sale->items as $item) {
                    if ($item->product_id) {
                        $product = InventoryProduct::find($item->product_id);
                        if ($product) {
                            $product->updateStock($item->quantity, 'subtract', 'memo:'.$sale->id);
                        }
                    }
                }
                $sale->update(['stock_moved' => true]);
            }

            // Post to accounting (will handle is_memo check)
            $this->accountingService->postSaleToAccounting($sale);

            $sale->logAudit('memo_converted', "Sale converted to Memo expiring on {$expiryDate}");
        });
    }

    /**
     * Finalize a memo into a completed sale
     */
    public function finalizeMemo(PosSale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status !== 'memo') {
                throw new \Exception('Only memos can be finalized.');
            }

            $sale->update([
                'is_memo' => false,
                'consignment_status' => 'converted_to_sale',
                'status' => 'completed',
                'sale_time' => now(),
            ]);

            // Re-post to accounting as a real sale
            $this->accountingService->postSaleToAccounting($sale);

            $sale->logAudit('memo_finalized', 'Memo finalized into permanent sale');
        });
    }

    /**
     * Cancel a memo and return stock
     */
    public function cancelMemo(PosSale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status !== 'memo') {
                throw new \Exception('Only memos can be cancelled.');
            }

            $this->restockSale($sale);

            $sale->update([
                'consignment_status' => 'returned',
                'status' => 'cancelled',
            ]);

            // Reverse accounting if any
            $this->accountingService->reverseSaleAccounting($sale->invoice_no, 'Memo cancelled/returned');

            $sale->logAudit('memo_cancelled', 'Memo cancelled and stock returned');
        });
    }

    /**
     * Restock a sale's items if stock was previously moved.
     * This will add back quantities for inventory-tracked items and
     * clear the `stock_moved` flag on the sale to allow idempotent operations.
     */
    public function restockSale(PosSale $sale): void
    {
        if (empty($sale->stock_moved)) {
            return; // nothing to do
        }

        foreach ($sale->items as $item) {
            if ($item->product_id) {
                $product = \App\Models\InventoryProduct::find($item->product_id);
                if ($product) {
                    $product->updateStock($item->quantity, 'add', 'restock:sale:'.$sale->id);
                }
            }
        }

        $sale->update(['stock_moved' => false]);
    }
}
