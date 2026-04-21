<?php

namespace App\Services;

use App\Models\PosSale;
use App\Models\TaxSlab;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComplianceService
{
    /**
     * Detect Anti-Money Laundering (AML) suspicious activity
     */
    public function detectAmlSuspiciousActivity()
    {
        // 1. Transactions over threshold (Statutory Threshold usually 200k+ in some regions, keeping 50k for demo)
        $threshold = 50000;
        
        return PosSale::where('total', '>', $threshold)
            ->where('payment_status', 'paid')
            ->with('customer')
            ->get()
            ->map(function ($sale) {
                return [
                    'sale_id' => $sale->id,
                    'customer' => $sale->customer->name ?? 'Walk-in',
                    'amount' => $sale->total,
                    'risk_level' => $sale->total > 100000 ? 'High' : 'Medium',
                    'reason' => 'Transaction exceeds statutory reporting threshold',
                    'date' => $sale->sale_time->toDateString(),
                ];
            });
    }

    /**
     * Generate Tax Report for a specific period
     */
    public function getTaxReport($startDate, $endDate)
    {
        $sales = PosSale::whereBetween('sale_time', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select(
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('SUM(tax_amount) as total_tax'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->first();

        // Group by tax rates (simulated from items if needed, but here we sum from sales)
        $taxByRate = DB::table('pos_sale_items')
            ->join('pos_sales', 'pos_sale_items.pos_sale_id', '=', 'pos_sales.id')
            ->whereBetween('pos_sales.sale_time', [$startDate, $endDate])
            ->where('pos_sales.status', 'completed')
            ->select('pos_sale_items.tax_percent', DB::raw('SUM(pos_sale_items.tax_amount) as total_tax_at_rate'))
            ->groupBy('pos_sale_items.tax_percent')
            ->get();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'summary' => $sales,
            'tax_breakdown' => $taxByRate,
        ];
    }

    /**
     * Generate Monthly Tax Summary
     */
    public function getMonthlyTaxSummary($year, $month)
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->getTaxReport($start, $end);
    }

    /**
     * Generate Quarterly Tax Summary
     */
    public function getQuarterlyTaxSummary($year, $quarter)
    {
        $start = Carbon::create($year)->quarter($quarter)->startOfQuarter();
        $end = $start->copy()->endOfQuarter();

        return $this->getTaxReport($start, $end);
    }

    /**
     * Generate Yearly Tax Summary
     */
    public function getYearlyTaxSummary($year)
    {
        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = $start->copy()->endOfYear();

        return $this->getTaxReport($start, $end);
    }

    /**
     * Validate Tax Compliance for a Sale
     */
    public function validateSaleCompliance(PosSale $sale)
    {
        $errors = [];

        // 1. Check if customer details are sufficient for high-value transactions
        if ($sale->total > 50000 && (!$sale->pos_customer_id || !$sale->customer->tax_id)) {
            $errors[] = "High-value transaction requires customer Tax ID (CNIC/NTN).";
        }

        // 2. Check for suspicious split transactions (multiple small sales to same customer in short time)
        // ... logic here ...

        return [
            'is_compliant' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Mockup statutory filing generation
     */
    public function generateStatutoryFiling($countryCode, $period)
    {
        $report = $this->getTaxReport(Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth());

        return [
            'country' => $countryCode,
            'period' => $period,
            'vat_gst_collected' => $report['summary']->total_tax ?? 0.00,
            'total_taxable_sales' => $report['summary']->total_subtotal ?? 0.00,
            'filing_status' => 'Draft',
            'digital_signature' => hash('sha256', json_encode($report) . time()),
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
