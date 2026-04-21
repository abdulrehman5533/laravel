<?php

namespace App\Services;

use App\Models\Girvi;
use App\Models\GirviItem;
use App\Models\GirviPayment;
use App\Models\SystemSetting;
use App\Services\Accounting\AccountingService;
use App\Services\Inventory\BarcodeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GirviService
{
    protected $accountingService;

    protected $marketRateService;

    protected $barcodeService;

    public function __construct(
        AccountingService $accountingService,
        MarketRateService $marketRateService,
        BarcodeService $barcodeService
    ) {
        $this->accountingService = $accountingService;
        $this->marketRateService = $marketRateService;
        $this->barcodeService = $barcodeService;
    }

    /**
     * Calculate valuation for an item
     */
    public function calculateItemValuation(array $item, ?float $goldRate = null, ?float $silverRate = null)
    {
        $type = strtolower($item['item_type'] ?? 'gold');
        $purityStr = $item['purity'] ?? '22K';
        $purity = $this->parsePurity($purityStr);
        $grossWeight = (float) ($item['gross_weight'] ?? 0);
        $stoneWeight = (float) ($item['stone_weight'] ?? 0);
        $netWeight = $grossWeight - $stoneWeight;

        // Get rate if not provided
        if (! $goldRate || ! $silverRate) {
            $rates = $this->getMarketRates();
            $goldRate = $goldRate ?: $rates['gold'];
            $silverRate = $silverRate ?: $rates['silver'];
        }

        $fineWeight = 0;
        $estimatedValue = 0;
        $ratePerGram = ($type === 'silver') ? $silverRate : $goldRate;

        if ($type === 'silver') {
            // Silver purity is usually expressed as percentage (e.g., 92.5)
            // If purity is > 24, we assume it's a percentage (0-100)
            // If it's <= 24, it might be Karat (unusual for silver but possible)
            $purityPercentage = ($purity > 24) ? $purity : ($purity / 24 * 100);
            $fineWeight = ($netWeight * $purityPercentage) / 100;
            $estimatedValue = $fineWeight * $silverRate;
        } elseif ($type === 'gold') {
            // Gold Karat to fine weight (24K base)
            $purityKarat = ($purity > 24) ? ($purity / 100 * 24) : $purity;
            $fineWeight = ($netWeight * $purityKarat) / 24;
            $estimatedValue = $fineWeight * $goldRate;
        } elseif ($type === 'diamond') {
            // For diamonds, valuation is often manual or based on a different rate
            // Here we might just use provided estimated_value if available,
            // otherwise fallback to a generic valuation or weight-based if rate provided
            $estimatedValue = $item['estimated_value'] ?? ($netWeight * ($item['valuation_rate'] ?? 0));
        }

        return [
            'net_weight' => round($netWeight, 3),
            'fine_weight' => round($fineWeight, 3),
            'valuation_rate' => $ratePerGram,
            'estimated_value' => round($estimatedValue, 2),
        ];
    }

    protected function parsePurity($purityStr)
    {
        if (is_numeric($purityStr)) {
            return (float) $purityStr;
        }

        $purityStr = strtolower($purityStr);
        if (str_contains($purityStr, '24')) {
            return 24.0;
        }
        if (str_contains($purityStr, '22')) {
            return 22.0;
        }
        if (str_contains($purityStr, '20')) {
            return 20.0;
        }
        if (str_contains($purityStr, '18')) {
            return 18.0;
        }
        if (str_contains($purityStr, '14')) {
            return 14.0;
        }
        if (str_contains($purityStr, '916')) {
            return 22.0;
        }
        if (str_contains($purityStr, '750')) {
            return 18.0;
        }
        if (str_contains($purityStr, '925')) {
            return 92.5;
        }
        if (str_contains($purityStr, '90')) {
            return 90.0;
        }

        preg_match('/(\d+(\.\d+)?)/', $purityStr, $matches);

        return isset($matches[1]) ? (float) $matches[1] : 22.0;
    }

    /**
     * Get current market rates
     */
    public function getMarketRates()
    {
        return [
            'gold' => $this->marketRateService->getLatestRate('gold')['rate_per_gram'],
            'silver' => $this->marketRateService->getLatestRate('silver')['rate_per_gram'],
        ];
    }

    /**
     * Validate LTV (Loan to Value) ratio
     */
    public function validateLTV($loanAmount, $totalValuation)
    {
        if ($totalValuation <= 0) {
            return [
                'ltv_ratio' => 0,
                'is_valid' => false,
                'limit' => SystemSetting::get('girvi_ltv_limit', 75),
                'message' => 'Valuation is zero.',
            ];
        }

        $ltv = ($loanAmount / $totalValuation) * 100;
        $limit = SystemSetting::get('girvi_ltv_limit', 75);
        $highRiskLimit = SystemSetting::get('girvi_high_risk_ltv', 85);

        $ltv_rounded = round($ltv, 2);

        return [
            'ltv_ratio' => $ltv_rounded,
            'is_valid' => $ltv <= $limit,
            'is_high_risk' => $ltv > $highRiskLimit,
            'limit' => $limit,
            'high_risk_limit' => $highRiskLimit,
            'message' => $ltv > $limit ? "LTV ratio ({$ltv_rounded}%) exceeds limit ({$limit}%)." : 'LTV is within limits.',
        ];
    }

    /**
     * Calculate maximum loan amount based on valuation and configured LTV
     */
    public function calculateMaxLoanAmount($totalValuation, $customLtvPercentage = null)
    {
        $ltvPercentage = $customLtvPercentage ?: SystemSetting::get('girvi_ltv_limit', 75);

        return round(($totalValuation * $ltvPercentage) / 100, 2);
    }

    /**
     * Get interest rate based on loan amount and configured slabs
     */
    public function getRateFromSlabs($amount)
    {
        $slabs = SystemSetting::get('girvi_interest_slabs', []);
        if (empty($slabs)) {
            return SystemSetting::get('girvi_default_interest_rate', 1.5);
        }

        foreach ($slabs as $slab) {
            if ($slab['limit'] === null || $amount <= $slab['limit']) {
                return $slab['rate'];
            }
        }

        return end($slabs)['rate'] ?? 1.5;
    }

    /**
     * Create a new Girvi (Pledge)
     */
    public function createGirvi(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $rates = $this->getMarketRates();
            $goldRate = $data['locked_gold_rate'] ?? $rates['gold'];
            $silverRate = $data['locked_silver_rate'] ?? $rates['silver'];

            $processedItems = [];
            $totalValuation = 0;

            foreach ($items as $item) {
                $valuation = $this->calculateItemValuation($item, $goldRate, $silverRate);
                $processedItems[] = array_merge($item, [
                    'net_weight' => $valuation['net_weight'],
                    'fine_weight' => $valuation['fine_weight'],
                    'valuation_rate' => $valuation['valuation_rate'],
                    'estimated_value' => $valuation['estimated_value'],
                ]);
                $totalValuation += $valuation['estimated_value'];
            }

            $ltvData = $this->validateLTV($data['loan_amount'], $totalValuation);

            $girvi = Girvi::create([
                'girvi_number' => $this->generateGirviNumber(),
                'customer_id' => $data['customer_id'],
                'branch_id' => $data['branch_id'],
                'loan_amount' => $data['loan_amount'],
                'interest_rate' => $data['interest_rate'] ?? $this->getRateFromSlabs($data['loan_amount']),
                'interest_cycle' => $data['interest_cycle'] ?? SystemSetting::get('girvi_default_interest_cycle', 'monthly'),
                'interest_type' => $data['interest_type'] ?? SystemSetting::get('girvi_default_interest_type', 'simple'),
                'locked_gold_rate' => $goldRate,
                'locked_silver_rate' => $silverRate,
                'rate_source' => $data['rate_source'] ?? 'Market',
                'girvi_date' => $data['girvi_date'] ?? now(),
                'maturity_date' => $data['maturity_date'] ?? null,
                'outstanding_amount' => $data['loan_amount'],
                'kyc_verified' => $data['kyc_verified'] ?? false,
                'ltv_ratio' => $ltvData['ltv_ratio'] ?? 0,
                'is_high_risk' => $ltvData['is_high_risk'] ?? false,
                'risk_score' => $this->calculateRiskScore(['ltv_ratio' => $ltvData['ltv_ratio'] ?? 0]),
                'risk_alert_threshold' => $ltvData['high_risk_limit'] ?? 85,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => Auth::id(),
                'grace_period_days' => $data['grace_period_days'] ?? SystemSetting::get('girvi_default_grace_period', 3),
                'penal_interest_rate' => $data['penal_interest_rate'] ?? SystemSetting::get('girvi_default_penal_interest', 2),
                'rounding_rule' => $data['rounding_rule'] ?? SystemSetting::get('girvi_rounding_rule', 'nearest'),
                
                // New Production Fields
                'approval_status' => $data['approval_status'] ?? 'pending',
                'loan_purpose' => $data['loan_purpose'] ?? null,
                'guarantor_name' => $data['guarantor_name'] ?? null,
                'guarantor_phone' => $data['guarantor_phone'] ?? null,
                'guarantor_id_type' => $data['guarantor_id_type'] ?? null,
                'guarantor_id_number' => $data['guarantor_id_number'] ?? null,
                'auto_renew' => $data['auto_renew'] ?? false,
            ]);

            foreach ($processedItems as $index => $pItem) {
                $photoPath = null;
                if (isset($items[$index]['item_photo']) && $items[$index]['item_photo'] instanceof \Illuminate\Http\UploadedFile) {
                    $photoPath = $items[$index]['item_photo']->store('girvi/items', 'public');
                }

                $girviItem = $girvi->items()->create(array_merge($pItem, [
                    'inventory_product_id' => $items[$index]['inventory_product_id'] ?? null,
                    'item_photo' => $photoPath,
                    'locker_location' => $items[$index]['locker_location'] ?? null,
                    'bag_number' => $items[$index]['bag_number'] ?? null,
                    'box_number' => $items[$index]['box_number'] ?? null,
                    'tag_number' => $items[$index]['tag_number'] ?? null,
                ]));

                // Update Inventory if linked
                if ($girviItem->inventory_product_id) {
                    $product = \App\Models\InventoryProduct::find($girviItem->inventory_product_id);
                    if ($product) {
                        $product->updateStock(
                            1,
                            'subtract',
                            "Pledged in Girvi #{$girvi->girvi_number}",
                            $girviItem->net_weight,
                            1
                        );
                        $product->update(['status' => 'pledged']);
                    }
                }

                // Generate Barcode & QR Code
                $barcodeData = $this->barcodeService->generateGirviBarcode($girvi->id, $girviItem->id);
                $girviItem->update([
                    'barcode' => $barcodeData['value'],
                    'qr_code' => $barcodeData['value'], // Use same value for QR code for now
                ]);

                // Purity Override Audit Log for Girvi
                if ($product && isset($item['purity']) && $product->purity) {
                    $oldPurity = (float) filter_var($product->purity->name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $newPurity = $this->parsePurity($item['purity']);

                    if ($oldPurity != $newPurity) {
                        \App\Models\PurityOverrideLog::logOverride(
                            $girviItem,
                            $oldPurity,
                            $newPurity,
                            $item['purity_override_reason'] ?? 'Manual override during Girvi Pledge'
                        );
                    }
                }
            }

            // Accounting: Post Loan Disbursement
            $this->postLoanDisbursement($girvi);

            return $girvi;
        });
    }

    /**
     * Calculate and Post Interest for all active Girvis
     */
    public function postInterestForAllActive()
    {
        $activeGirvis = Girvi::where('status', 'active')->get();
        $count = 0;

        foreach ($activeGirvis as $girvi) {
            if ($this->shouldPostInterest($girvi)) {
                $this->calculateAndPostInterest($girvi);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Calculate interest for a specific Girvi
     */
    public function calculateAndPostInterest(Girvi $girvi, $isManual = false)
    {
        return DB::transaction(function () use ($girvi, $isManual) {
            $lastPosting = $girvi->interestPostings()->orderBy('period_end', 'desc')->first();
            $startDate = $lastPosting ? Carbon::parse($lastPosting->period_end) : Carbon::parse($girvi->girvi_date);
            $endDate = now();

            if ($startDate->isSameDay($endDate)) {
                return null;
            }

            $interestAmount = $this->calculateInterestAmount($girvi, $startDate, $endDate);

            if ($interestAmount <= 0) {
                return null;
            }

            $posting = $girvi->interestPostings()->create([
                'posting_date' => now(),
                'period_start' => $startDate,
                'period_end' => $endDate,
                'interest_amount' => $interestAmount,
                'principal_balance_at_posting' => $girvi->loan_amount - $girvi->principal_paid,
                'is_manual' => $isManual,
                'posted_by' => Auth::id(),
            ]);

            $girvi->increment('interest_accrued', $interestAmount);
            $this->updateOutstandingBalance($girvi);

            // Accounting: Post Interest Accrual
            $this->postInterestAccrual($girvi, $interestAmount);

            return $posting;
        });
    }

    /**
     * Get the total amount required for full settlement
     */
    public function getFullSettlementAmount(Girvi $girvi)
    {
        // 1. Calculate pending interest up to today
        $lastPosting = $girvi->interestPostings()->orderBy('period_end', 'desc')->first();
        $startDate = $lastPosting ? Carbon::parse($lastPosting->period_end) : Carbon::parse($girvi->girvi_date);

        $pendingInterest = 0;
        if (now()->isAfter($startDate)) {
            $pendingInterest = $this->calculateInterestAmount($girvi, $startDate, now());
        }

        $totalInterestAccrued = $girvi->interest_accrued + $pendingInterest;
        $totalOutstanding = ($girvi->loan_amount + $totalInterestAccrued + $girvi->penalty_amount)
                            - ($girvi->principal_paid + $girvi->interest_paid);

        return [
            'principal_due' => $girvi->loan_amount - $girvi->principal_paid,
            'interest_accrued' => $girvi->interest_accrued - $girvi->interest_paid,
            'new_interest' => $pendingInterest,
            'penalty' => $girvi->penalty_amount,
            'total_settlement' => max(0, $totalOutstanding),
        ];
    }

    /**
     * Record a payment against a Girvi
     */
    public function recordPayment(Girvi $girvi, array $data)
    {
        return DB::transaction(function () use ($girvi, $data) {
            $amount = $data['amount'];
            $waiver = $data['waiver_amount'] ?? 0;

            // Allocation logic (Interest first, then principal)
            $interestDue = $girvi->interest_accrued - $girvi->interest_paid;
            $interestComponent = min($amount, $interestDue);
            $remaining = $amount - $interestComponent;

            $principalDue = $girvi->loan_amount - $girvi->principal_paid;
            $principalComponent = min($remaining, $principalDue);

            $payment = $girvi->payments()->create([
                'payment_date' => $data['payment_date'] ?? now(),
                'amount' => $amount,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'waiver_amount' => $waiver,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'received_by' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $girvi->increment('principal_paid', $principalComponent);
            $girvi->increment('interest_paid', $interestComponent);

            if ($waiver > 0) {
                $girvi->increment('interest_paid', $waiver);
            }

            $this->updateOutstandingBalance($girvi);

            if ($girvi->outstanding_amount <= 0.01) {
                $girvi->update(['status' => 'settled']);
            }

            // Accounting: Post Payment
            $this->postPaymentToLedger($girvi, $payment);

            return $payment;
        });
    }

    /**
     * Update statuses for all active Girvis (Run daily)
     */
    public function updateStatuses()
    {
        $girvis = Girvi::whereIn('status', ['active', 'overdue'])->get();
        $updatedCount = 0;

        foreach ($girvis as $girvi) {
            $oldStatus = $girvi->status;

            // Overdue detection: Maturity date passed + grace period
            if ($girvi->maturity_date && now()->isAfter($girvi->maturity_date->addDays($girvi->grace_period_days))) {
                $girvi->status = 'overdue';
            } elseif ($this->isInterestOverdue($girvi)) {
                $girvi->status = 'interest_due';
            }

            if ($girvi->status !== $oldStatus) {
                $girvi->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Renew a Girvi loan (Extend maturity)
     */
    public function renewGirvi(Girvi $girvi, array $data)
    {
        return DB::transaction(function () use ($girvi, $data) {
            // 1. Must post all outstanding interest first
            $this->calculateAndPostInterest($girvi, true);

            // 2. Record renewal fee or partial payment if any
            if (isset($data['payment_amount']) && $data['payment_amount'] > 0) {
                $this->recordPayment($girvi, [
                    'amount' => $data['payment_amount'],
                    'payment_method' => $data['payment_method'] ?? 'Cash',
                    'notes' => 'Renewal Payment',
                ]);
            }

            // 3. Update maturity and possibly rate
            $girvi->update([
                'maturity_date' => $data['new_maturity_date'],
                'interest_rate' => $data['new_interest_rate'] ?? $girvi->interest_rate,
                'status' => 'active',
                'internal_notes' => ($girvi->internal_notes ? $girvi->internal_notes."\n" : '').'Renewed on '.now()->toDateString().'. New maturity: '.$data['new_maturity_date'],
            ]);

            return $girvi;
        });
    }

    public function sendReminders()
    {
        $activeGirvis = Girvi::where('status', 'active')->with('customer')->get();
        $count = 0;

        foreach ($activeGirvis as $girvi) {
            // 1. Due soon (3 days before monthly cycle)
            if ($this->isInterestDueSoon($girvi)) {
                $this->dispatchReminder($girvi, 'due_soon');
                $count++;
            }

            // 2. Overdue (5 days after due date)
            if ($this->isInterestOverdue($girvi)) {
                $this->dispatchReminder($girvi, 'overdue');
                $count++;
            }

            // 3. Maturity Warning
            if ($girvi->maturity_date && $girvi->maturity_date->diffInDays(now()) <= 7) {
                $this->dispatchReminder($girvi, 'maturity');
                $count++;
            }
        }

        return $count;
    }

    protected function isInterestDueSoon(Girvi $girvi)
    {
        // Logic to check if next cycle is in 3 days
        $lastPosting = $girvi->interestPostings()->orderBy('period_end', 'desc')->first();
        $baseDate = $lastPosting ? Carbon::parse($lastPosting->period_end) : Carbon::parse($girvi->girvi_date);

        $nextDue = match ($girvi->interest_cycle) {
            'daily' => $baseDate->addDay(),
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'yearly' => $baseDate->addYear(),
        };

        return now()->diffInDays($nextDue, false) === 3;
    }

    protected function isInterestOverdue(Girvi $girvi)
    {
        $lastPosting = $girvi->interestPostings()->orderBy('period_end', 'desc')->first();
        $baseDate = $lastPosting ? Carbon::parse($lastPosting->period_end) : Carbon::parse($girvi->girvi_date);

        $nextDue = match ($girvi->interest_cycle) {
            'daily' => $baseDate->addDay(),
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'yearly' => $baseDate->addYear(),
        };

        return now()->diffInDays($nextDue, false) === -5;
    }

    public function dispatchReminder(Girvi $girvi, $type)
    {
        $message = $this->prepareTemplate($girvi, $type);

        // Mock Gateway Call
        // $response = $this->smsGateway->send($girvi->customer->phone, $message);

        return $girvi->reminders()->create([
            'channel' => 'whatsapp',
            'reminder_type' => $type,
            'recipient_contact' => $girvi->customer->phone,
            'message_content' => $message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    protected function prepareTemplate(Girvi $girvi, $type)
    {
        $customerName = $girvi->customer->name;
        $amount = number_format($girvi->outstanding_amount, 2);

        return match ($type) {
            'due_soon' => "Dear {$customerName}, your Girvi #{$girvi->girvi_number} interest payment of Rs. {$amount} is due in 3 days. Please settle to avoid penalties.",
            'overdue' => "URGENT: Dear {$customerName}, interest for Girvi #{$girvi->girvi_number} is OVERDUE. Current outstanding: Rs. {$amount}. Please visit the branch immediately.",
            'maturity' => "Dear {$customerName}, your Girvi #{$girvi->girvi_number} is reaching maturity on {$girvi->maturity_date->format('d/m/y')}. Please plan for settlement.",
            default => "Reminder for Girvi #{$girvi->girvi_number}",
        };
    }

    protected function updateOutstandingBalance(Girvi $girvi)
    {
        $outstanding = ($girvi->loan_amount + $girvi->interest_accrued + $girvi->penalty_amount)
                        - ($girvi->principal_paid + $girvi->interest_paid);
        $girvi->update(['outstanding_amount' => max(0, $outstanding)]);
    }

    protected function calculateInterestAmount(Girvi $girvi, Carbon $start, Carbon $end)
    {
        $principal = $girvi->loan_amount - $girvi->principal_paid;
        if ($principal <= 0) {
            return 0;
        }

        $isOverdue = $girvi->maturity_date && $end->isAfter($girvi->maturity_date->addDays($girvi->grace_period_days));
        $baseRate = $isOverdue ? ($girvi->interest_rate + $girvi->penal_interest_rate) : $girvi->interest_rate;

        $mode = SystemSetting::get('girvi_calculation_mode', '30_days');

        $interest = 0;
        if ($girvi->interest_type === 'simple') {
            $days = $start->diffInDays($end);
            if ($girvi->interest_cycle === 'daily') {
                // Rate is per day
                $interest = ($principal * ($baseRate / 100) * $days);
            } else {
                // Rate is per month (Standard in Jewellery)
                if ($mode === 'actual') {
                    // Actual days basis (assuming 365 days a year)
                    $interest = ($principal * ($baseRate / 100) * 12 * $days) / 365;
                } else {
                    // Standard 30 days divisor for daily interest if rate is monthly
                    $interest = ($principal * ($baseRate / 100) * $days) / 30;
                }
            }
        } else {
            // Compound interest (monthly compounding)
            $months = ($mode === 'actual') ? $start->floatDiffInMonths($end) : ($start->diffInDays($end) / 30);
            $interest = $principal * (pow(1 + ($baseRate / 100), $months) - 1);
        }

        return $this->applyRounding($interest, $girvi->rounding_rule);
    }

    protected function applyRounding($amount, $rule)
    {
        return match ($rule) {
            'up' => ceil($amount),
            'down' => floor($amount),
            'nearest' => round($amount),
            default => round($amount, 2),
        };
    }

    /**
     * Release Girvi and Close
     */
    public function releaseGirvi(Girvi $girvi, array $data)
    {
        if ($girvi->status !== 'active') {
            throw new \Exception('Only active Girvi loans can be released.');
        }

        return DB::transaction(function () use ($girvi, $data) {
            $closureType = $data['closure_type'] ?? 'normal'; // normal, early, loss, auction

            // 1. Calculate final interest
            $this->calculateAndPostInterest($girvi, true);

            // 2. Process final payment if any
            // Support both flat data structure and nested payment array from controller
            $paymentData = $data['payment'] ?? $data;
            if (isset($paymentData['amount']) && $paymentData['amount'] > 0) {
                $this->recordPayment($girvi, [
                    'amount' => $paymentData['amount'],
                    'payment_method' => $paymentData['payment_method'] ?? 'Cash',
                    'payment_date' => $paymentData['payment_date'] ?? now(),
                    'waiver_amount' => $paymentData['waiver_amount'] ?? 0,
                    'notes' => $paymentData['notes'] ?? 'Final settlement payment',
                ]);
            }

            // 3. Validation: Must be fully paid for normal/early release
            if (in_array($closureType, ['normal', 'early']) && $girvi->outstanding_amount > 0.01) {
                throw new \Exception('Cannot release Girvi. Outstanding balance: Rs. '.$girvi->outstanding_amount);
            }

            $girvi->update([
                'status' => $closureType === 'auction' ? 'auctioned' : 'settled',
                'internal_notes' => ($girvi->internal_notes ? $girvi->internal_notes."\n" : '').'Released as: '.strtoupper($closureType).' on '.now()->toDateTimeString(),
            ]);

            // 4. Handle Inventory Movements
            foreach ($girvi->items as $item) {
                if ($closureType === 'auction') {
                    $this->acquireStockFromAuction($girvi, $item);
                } elseif (in_array($closureType, ['normal', 'early']) && $item->inventory_product_id) {
                    // If it was a stock item, return it to stock
                    $product = \App\Models\InventoryProduct::find($item->inventory_product_id);
                    if ($product) {
                        $product->updateStock(
                            1,
                            'add',
                            "Returned from Released Girvi #{$girvi->girvi_number}",
                            $item->net_weight,
                            1
                        );
                        $product->update(['status' => 'active']);
                    }
                }
            }

            // 5. Accounting for Loss/Auction closure
            if ($closureType === 'loss' || $closureType === 'auction') {
                $this->postClosureAccounting($girvi, $closureType, $data);
            }

            return $girvi;
        });
    }

    /**
     * Handle Waiver Request (Approval Workflow)
     */
    public function requestWaiver(Girvi $girvi, $amount, $reason)
    {
        return \App\Models\TransactionApproval::create([
            'user_id' => Auth::id(),
            'entity_type' => Girvi::class,
            'entity_id' => $girvi->id,
            'action' => 'pending',
            'remarks' => "Waiver Request: Rs. {$amount}. Reason: {$reason}",
        ]);
    }

    public function approveWaiver(\App\Models\TransactionApproval $approval)
    {
        return DB::transaction(function () use ($approval) {
            $girvi = Girvi::find($approval->entity_id);
            preg_match('/Rs\. ([\d\.]+)/', $approval->remarks, $matches);
            $amount = $matches[1] ?? 0;

            if ($amount > 0) {
                $girvi->increment('interest_paid', $amount);
                $this->updateOutstandingBalance($girvi);

                // Accounting: Dr Girvi Interest Revenue (Reversal/Waiver)
                // Cr Girvi Interest Receivable
                $this->postWaiverAccounting($girvi, $amount);
            }

            $approval->update([
                'action' => 'approve',
                'approved_at' => now(),
            ]);

            return $girvi;
        });
    }

    protected function postWaiverAccounting(Girvi $girvi, $amount)
    {
        try {
            $receivableAccount = $this->accountingService->getAccountByCode('1221');
            $incomeAccount = $this->accountingService->getAccountByCode('4400');

            $this->accountingService->createJournalEntry([
                'entry_date' => now()->toDateString(),
                'reference_number' => "WAV-{$girvi->girvi_number}",
                'narration' => "Interest Waiver Approved for Girvi #{$girvi->girvi_number}",
                'items' => [
                    ['account_id' => $incomeAccount->id, 'debit' => $amount, 'credit' => 0], // Dr Income (Reduce)
                    ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => $amount], // Cr Receivable (Reduce)
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Girvi Accounting Error (Waiver): '.$e->getMessage());
        }
    }

    protected function postClosureAccounting(Girvi $girvi, $type, $data)
    {
        // Custom logic for Auction/Loss writing off
        if ($type === 'loss' && $girvi->outstanding_amount > 0) {
            try {
                $lossAccount = $this->accountingService->getAccountByCode('5410'); // Loss on Gold/Wastage or Bad Debt
                $loanAccount = $this->accountingService->getAccountByCode('1220');
                $receivableAccount = $this->accountingService->getAccountByCode('1221');

                $interestDue = $girvi->interest_accrued - $girvi->interest_paid;
                $principalDue = $girvi->loan_amount - $girvi->principal_paid;

                $items = [];
                if ($principalDue > 0) {
                    $items[] = ['account_id' => $loanAccount->id, 'debit' => 0, 'credit' => $principalDue];
                }
                if ($interestDue > 0) {
                    $items[] = ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => $interestDue];
                }

                $items[] = ['account_id' => $lossAccount->id, 'debit' => $girvi->outstanding_amount, 'credit' => 0];

                $this->accountingService->createJournalEntry([
                    'entry_date' => now()->toDateString(),
                    'reference_number' => "LOSS-{$girvi->girvi_number}",
                    'narration' => "Girvi Loss Closure (Write-off) - #{$girvi->girvi_number}",
                    'items' => $items,
                ]);
            } catch (\Exception $e) {
                Log::error('Girvi Accounting Error (Loss Closure): '.$e->getMessage());
            }
        } elseif ($type === 'auction') {
            try {
                $loanAccount = $this->accountingService->getAccountByCode('1220');
                $receivableAccount = $this->accountingService->getAccountByCode('1221');

                $interestDue = $girvi->interest_accrued - $girvi->interest_paid;
                $principalDue = $girvi->loan_amount - $girvi->principal_paid;

                $items = [];
                if ($principalDue > 0) {
                    $items[] = ['account_id' => $loanAccount->id, 'debit' => 0, 'credit' => $principalDue];
                }
                if ($interestDue > 0) {
                    $items[] = ['account_id' => $receivableAccount->id, 'debit' => 0, 'credit' => $interestDue];
                }

                // Debit Inventory for the total value acquired
                $distribution = $this->getMetalWiseDistribution($girvi);
                foreach ($distribution as $metal => $amount) {
                    $code = match (strtolower($metal)) {
                        'gold' => '1310',
                        'silver' => '1330',
                        'diamond' => '1320',
                        default => '1310'
                    };
                    $inventoryAccount = $this->accountingService->getAccountByCode($code);
                    $items[] = ['account_id' => $inventoryAccount->id, 'debit' => $amount, 'credit' => 0];
                }

                $this->accountingService->createJournalEntry([
                    'entry_date' => now()->toDateString(),
                    'reference_number' => "AUC-{$girvi->girvi_number}",
                    'narration' => "Girvi Auction Acquisition - #{$girvi->girvi_number}",
                    'items' => $items,
                ]);
            } catch (\Exception $e) {
                Log::error('Girvi Accounting Error (Auction Closure): '.$e->getMessage());
            }
        }
    }

    protected function getMetalWiseDistribution(Girvi $girvi)
    {
        $distribution = [];
        $totalValuation = $girvi->items->sum('estimated_value');
        if ($totalValuation <= 0) {
            return ['Gold' => $girvi->outstanding_amount];
        }

        foreach ($girvi->items as $item) {
            $type = $item->item_type ?? 'Gold';
            $share = ($item->estimated_value / $totalValuation) * $girvi->outstanding_amount;
            $distribution[$type] = ($distribution[$type] ?? 0) + $share;
        }

        return $distribution;
    }

    protected function generateGirviNumber()
    {
        $prefix = SystemSetting::get('girvi_number_prefix', 'GRV-');
        $padding = SystemSetting::get('girvi_number_padding', 6);
        $datePart = date('Ymd');

        $count = Girvi::whereDate('created_at', today())->count() + 1;

        return $prefix.$datePart.'-'.str_pad($count, $padding, '0', STR_PAD_LEFT);
    }

    protected function calculateRiskScore($data)
    {
        $ltv = $data['ltv_ratio'] ?? 0;
        $highRiskLimit = SystemSetting::get('girvi_high_risk_ltv', 85);
        $mediumRiskLimit = SystemSetting::get('girvi_ltv_limit', 75);

        if ($ltv > $highRiskLimit) {
            return 'High';
        }
        if ($ltv > $mediumRiskLimit) {
            return 'Medium';
        }

        return 'Low';
    }

    protected function shouldPostInterest(Girvi $girvi)
    {
        $lastPosting = $girvi->interestPostings()->orderBy('period_end', 'desc')->first();
        $lastDate = $lastPosting ? Carbon::parse($lastPosting->period_end) : Carbon::parse($girvi->girvi_date);

        return match ($girvi->interest_cycle) {
            'daily' => $lastDate->diffInDays(now()) >= 1,
            'monthly' => $lastDate->diffInMonths(now()) >= 1,
            'quarterly' => $lastDate->diffInMonths(now()) >= 3,
            'yearly' => $lastDate->diffInYears(now()) >= 1,
            default => false,
        };
    }

    // --- Accounting Integrations ---

    protected function postLoanDisbursement(Girvi $girvi)
    {
        // Dr Loan Asset (Receivable)
        // Cr Cash/Bank
        try {
            $loanAccount = $this->accountingService->getAccountByCode('1220'); // Assumed Girvi Loan account
            $cashAccount = $this->accountingService->getAccountByCode('1010'); // Cash in Hand

            $this->accountingService->createJournalEntry([
                'entry_date' => $girvi->girvi_date,
                'reference_number' => $girvi->girvi_number,
                'narration' => "Girvi Loan Disbursement - #{$girvi->girvi_number} - Customer: {$girvi->customer->name}",
                'items' => [
                    ['account_id' => $loanAccount->id, 'debit' => $girvi->loan_amount, 'credit' => 0],
                    ['account_id' => $cashAccount->id, 'debit' => 0, 'credit' => $girvi->loan_amount],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Girvi Accounting Error (Disbursement): '.$e->getMessage());
        }
    }

    protected function postInterestAccrual(Girvi $girvi, $amount)
    {
        // Dr Interest Receivable
        // Cr Interest Income
        try {
            $receivableAccount = $this->accountingService->getAccountByCode('1221'); // Girvi Interest Receivable
            $incomeAccount = $this->accountingService->getAccountByCode('4400'); // Girvi Interest Income

            $this->accountingService->createJournalEntry([
                'entry_date' => now()->toDateString(),
                'reference_number' => "INT-{$girvi->girvi_number}",
                'narration' => "Interest Accrued for Girvi #{$girvi->girvi_number}",
                'items' => [
                    ['account_id' => $receivableAccount->id, 'debit' => $amount, 'credit' => 0],
                    ['account_id' => $incomeAccount->id, 'debit' => 0, 'credit' => $amount],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Girvi Accounting Error (Accrual): '.$e->getMessage());
        }
    }

    protected function postPaymentToLedger(Girvi $girvi, GirviPayment $payment)
    {
        // Dr Cash/Bank
        // Cr Loan Asset (Principal)
        // Cr Interest Receivable (Interest)
        try {
            $cashAccount = $this->accountingService->getAccountByCode('1010');
            $loanAccount = $this->accountingService->getAccountByCode('1220');
            $interestReceivableAccount = $this->accountingService->getAccountByCode('1221');

            $items = [
                ['account_id' => $cashAccount->id, 'debit' => $payment->amount, 'credit' => 0],
            ];

            if ($payment->principal_component > 0) {
                $items[] = ['account_id' => $loanAccount->id, 'debit' => 0, 'credit' => $payment->principal_component];
            }

            if ($payment->interest_component > 0) {
                $items[] = ['account_id' => $interestReceivableAccount->id, 'debit' => 0, 'credit' => $payment->interest_component];
            }

            $this->accountingService->createJournalEntry([
                'entry_date' => $payment->payment_date,
                'reference_number' => "PAY-{$payment->id}",
                'narration' => "Girvi Payment - #{$girvi->girvi_number}",
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Girvi Accounting Error (Payment): '.$e->getMessage());
        }
    }

    /**
     * Transfer Girvi to Bank or Third Party
     */
    public function transferGirvi(Girvi $girvi, array $data)
    {
        return DB::transaction(function () use ($girvi, $data) {
            $girvi->update([
                'status' => 'transferred',
                'transfer_type' => $data['transfer_type'],
                'transferred_to' => $data['transferred_to'],
                'transfer_amount' => $data['transfer_amount'] ?? $girvi->outstanding_amount,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'transfer_charges' => $data['transfer_charges'] ?? 0,
                'transfer_terms' => $data['transfer_terms'] ?? null,
            ]);

            // Accounting: Dr Cash/Bank (from Transfer)
            // Cr Girvi Loan Asset
            try {
                $transferAmount = $data['transfer_amount'] ?? $girvi->outstanding_amount;
                $charges = $data['transfer_charges'] ?? 0;
                $outstanding = $girvi->loan_amount - $girvi->principal_paid;

                $cashAccount = $this->accountingService->getAccountByCode('1010');
                $loanAccount = $this->accountingService->getAccountByCode('1220');
                $expenseAccount = $this->accountingService->getAccountByCode('5010'); // Bank/Transfer Charges

                $items = [
                    ['account_id' => $cashAccount->id, 'debit' => $transferAmount, 'credit' => 0],
                ];

                if ($charges > 0) {
                    $items[] = ['account_id' => $expenseAccount->id, 'debit' => $charges, 'credit' => 0];
                }

                // Credit the principal amount from the loan account
                $items[] = ['account_id' => $loanAccount->id, 'debit' => 0, 'credit' => $outstanding];

                // If there's a difference, it goes to Gain/Loss
                $diff = $transferAmount - ($outstanding + $charges);
                if ($diff != 0) {
                    $gainLossAccount = $this->accountingService->getAccountByCode($diff > 0 ? '4500' : '5410');
                    $items[] = [
                        'account_id' => $gainLossAccount->id,
                        'debit' => $diff < 0 ? abs($diff) : 0,
                        'credit' => $diff > 0 ? $diff : 0,
                    ];
                }

                $this->accountingService->createJournalEntry([
                    'entry_date' => now()->toDateString(),
                    'reference_number' => "TRF-{$girvi->girvi_number}",
                    'narration' => "Girvi Transfer to {$data['transferred_to']} - #{$girvi->girvi_number}",
                    'items' => $items,
                ]);
            } catch (\Exception $e) {
                \Log::error('Girvi Accounting Error (Transfer): '.$e->getMessage());
            }

            return $girvi;
        });
    }

    /**
     * Generate Girvi Loss Report
     */
    public function getGirviLossReport()
    {
        // Girvis where valuation is less than outstanding (Potential Loss)
        return Girvi::where('status', 'active')
            ->with('items')
            ->get()
            ->filter(function ($girvi) {
                $currentValuation = $girvi->items->sum('estimated_value');

                return $girvi->outstanding_amount > $currentValuation;
            });
    }

    /**
     * Handle Auction stock acquisition
     */
    protected function acquireStockFromAuction(Girvi $girvi, GirviItem $item)
    {
        $totalValuation = $girvi->items->sum('estimated_value');
        $proportionalCost = ($item->estimated_value / max(1, $totalValuation)) * $girvi->outstanding_amount;

        // If already linked to inventory, we just reactivate it with new cost
        if ($item->inventory_product_id) {
            $product = \App\Models\InventoryProduct::find($item->inventory_product_id);
            if ($product) {
                $product->updateStock(
                    1,
                    'add',
                    "Acquired via Auction from Girvi #{$girvi->girvi_number}",
                    $item->net_weight,
                    1
                );
                // Update cost price to be the share of outstanding loan
                $product->update([
                    'cost_price' => $proportionalCost,
                    'status' => 'active',
                ]);

                return;
            }
        }

        // Otherwise, create a new "Old Gold" or "Auctioned" inventory item
        $purity = \App\Models\PurityLevel::where('name', 'like', "%{$item->purity}%")->first();
        $category = \App\Models\ProductCategory::where('name', 'like', "%{$item->item_type}%")->first();

        \App\Models\InventoryProduct::create([
            'name' => "Auctioned {$item->item_type} - {$item->item_name}",
            'sku' => 'AUC-'.$item->barcode,
            'category_id' => $category?->id,
            'purity_id' => $purity?->id,
            'weight' => $item->gross_weight,
            'net_weight' => $item->net_weight,
            'fine_weight' => $item->fine_weight,
            'current_stock' => 1,
            'current_pieces' => 1,
            'cost_price' => $proportionalCost,
            'selling_price' => $item->estimated_value,
            'status' => 'active',
            'branch_id' => $girvi->branch_id,
            'created_by' => Auth::id(),
            'description' => "Acquired from Auction of Girvi #{$girvi->girvi_number}. Original item: {$item->description}",
        ]);
    }
}
