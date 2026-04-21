<?php

namespace App\Http\Controllers\Reports;

use App\Exports\SalesReportSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PosSale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    /**
     * Display sales reports index
     */
    public function index(): View
    {
        $stats = $this->getQuickStats();

        return view('reports.sales.index', $stats);
    }

    /**
     * Daily sales report
     */
    public function daily(Request $request): View
    {
        $data = $this->getDailySalesData($request);

        return view('reports.sales.daily', $data);
    }

    /**
     * Print daily sales report
     */
    public function printDaily(Request $request): View
    {
        $data = $this->getDailySalesData($request);

        return view('reports.sales.daily-print', $data);
    }

    /**
     * Get daily sales data
     */
    private function getDailySalesData(Request $request): array
    {
        $date = $request->get('date', today());

        $sales = PosSale::whereDate('sale_time', $date)
            ->with(['customer', 'items', 'payments'])
            ->get();

        $metrics = $this->calculateMetrics($sales);
        $metrics['date'] = $date;

        return $metrics;
    }

    /**
     * Weekly sales report
     */
    public function weekly(Request $request): View
    {
        $data = $this->getWeeklySalesData($request);

        return view('reports.sales.weekly', $data);
    }

    /**
     * Print weekly sales report
     */
    public function printWeekly(Request $request): View
    {
        $data = $this->getWeeklySalesData($request);

        return view('reports.sales.weekly-print', $data);
    }

    /**
     * Get weekly sales data
     */
    private function getWeeklySalesData(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfWeek()->toDateString());
        $endDate = $request->get('end_date', now()->endOfWeek()->toDateString());

        $sales = PosSale::whereBetween('sale_time', [$startDate, $endDate])
            ->with(['customer', 'items', 'payments'])
            ->get();

        $metrics = $this->calculateMetrics($sales);
        $metrics['startDate'] = $startDate;
        $metrics['endDate'] = $endDate;

        // Calculate trend data for the chart
        $trendData = collect();
        $currentDate = \Carbon\Carbon::parse($startDate);
        $lastDate = \Carbon\Carbon::parse($endDate);

        while ($currentDate <= $lastDate) {
            $dateString = $currentDate->format('M d');
            $dayTotal = $sales->filter(function ($sale) use ($currentDate) {
                return \Carbon\Carbon::parse($sale->sale_time)->isSameDay($currentDate);
            })->sum('total');

            $trendData->put($dateString, $dayTotal);
            $currentDate->addDay();
        }
        $metrics['trendData'] = $trendData;

        return $metrics;
    }

    /**
     * Monthly sales report
     */
    public function monthly(Request $request): View
    {
        $data = $this->getMonthlySalesData($request);

        return view('reports.sales.monthly', $data);
    }

    /**
     * Print monthly sales report
     */
    public function printMonthly(Request $request): View
    {
        $data = $this->getMonthlySalesData($request);

        return view('reports.sales.monthly-print', $data);
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySalesData(Request $request): array
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $sales = PosSale::whereYear('sale_time', $year)
            ->whereMonth('sale_time', $month)
            ->with(['customer', 'items', 'payments'])
            ->get();

        $metrics = $this->calculateMetrics($sales);
        $metrics['month'] = $month;
        $metrics['year'] = $year;

        // Calculate daily trend for the month
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $dailyTrend = collect();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::create($year, $month, $day);
            $total = $sales->filter(function ($sale) use ($date) {
                return \Carbon\Carbon::parse($sale->sale_time)->isSameDay($date);
            })->sum('total');

            $dailyTrend->push([
                'date' => $date->format('d M'),
                'total' => $total,
            ]);
        }
        $metrics['dailyTrend'] = $dailyTrend;

        return $metrics;
    }

    /**
     * Calculate comprehensive metrics from sales
     */
    private function calculateMetrics($sales)
    {
        $totalSales = 0;
        $totalItems = 0;
        $totalTax = 0;
        $totalDiscount = 0;
        $totalMakingCharges = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;

        foreach ($sales as $sale) {
            $totalSales += $sale->total;
            $totalItems += $sale->items->count();
            $totalTax += $sale->tax_amount;
            $totalDiscount += $sale->discount;
            $totalMakingCharges += $sale->making_charges;
            $totalPaid += $sale->payments()->sum('amount');
            $totalOutstanding += $sale->outstanding_balance;
        }

        $costOfGoodsSold = $this->calculateCOGS($sales);
        $grossProfit = $totalSales - $costOfGoodsSold;
        $grossMargin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

        return [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalItems' => $totalItems,
            'totalTax' => $totalTax,
            'totalDiscount' => $totalDiscount,
            'totalMakingCharges' => $totalMakingCharges,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'costOfGoodsSold' => $costOfGoodsSold,
            'grossProfit' => $grossProfit,
            'grossMargin' => round($grossMargin, 2),
            'averageOrderValue' => $sales->count() > 0 ? $totalSales / $sales->count() : 0,
            'count' => $sales->count(),
        ];
    }

    /**
     * Calculate cost of goods sold from inventory
     */
    private function calculateCOGS($sales)
    {
        $cogs = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $product = \App\Models\InventoryProduct::find($item->product_id);
                    if ($product) {
                        $itemCost = ($product->cost_price ?? 0) * $item->quantity;
                        $cogs += $itemCost;
                    }
                }
            }
        }

        return $cogs;
    }

    /**
     * Get quick statistics
     */
    private function getQuickStats()
    {
        $today = today();
        $thisMonth = now()->startOfMonth();

        $todaySales = PosSale::whereDate('sale_time', $today)->get();
        $monthSales = PosSale::whereBetween('sale_time', [$thisMonth, now()])->get();

        return [
            'todayMetrics' => $this->calculateMetrics($todaySales),
            'monthMetrics' => $this->calculateMetrics($monthSales),
            'topProducts' => $this->getTopProducts(),
            'topCustomers' => $this->getTopCustomers(),
        ];
    }

    /**
     * Get top performing products
     */
    private function getTopProducts($limit = 10)
    {
        return DB::table('pos_sale_items')
            ->select('description', 'sku', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(quantity * unit_price) as revenue'))
            ->whereDate('created_at', '>=', now()->startOfMonth())
            ->groupBy('sku', 'description')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top customers by revenue
     */
    private function getTopCustomers($limit = 10)
    {
        return PosSale::whereDate('sale_time', '>=', now()->startOfMonth())
            ->with('customer')
            ->select('pos_customer_id', DB::raw('SUM(total) as total_spent'), DB::raw('COUNT(*) as orders'))
            ->groupBy('pos_customer_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->load('customer');
    }

    /**
     * Export sales report
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $period = $request->get('period', 'month');

        if ($format === 'excel') {
            return $this->exportToExcel($period);
        } else {
            return $this->exportToPdf($period);
        }
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($period)
    {
        $sales = $this->getSalesByPeriod($period);
        $metrics = $this->calculateMetrics($sales);

        $fileName = "sales_report_{$period}_".now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new SalesReportSummaryExport($sales, $period, $metrics),
            $fileName
        );
    }

    /**
     * Export to PDF
     */
    private function exportToPdf($period)
    {
        $sales = $this->getSalesByPeriod($period);
        $metrics = $this->calculateMetrics($sales);

        $dateRange = $this->getDateRangeForPeriod($period);

        $pdf = Pdf::loadView('exports.sales-report-pdf', [
            'sales' => $sales,
            'metrics' => $metrics,
            'dateRange' => $dateRange,
            'period' => $period,
        ]);

        $fileName = "sales_report_{$period}_".now()->format('Y-m-d').'.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Get sales data by period
     */
    private function getSalesByPeriod($period)
    {
        $query = PosSale::with(['customer', 'items', 'payments']);

        switch ($period) {
            case 'day':
                $query->whereDate('sale_time', today());
                break;
            case 'week':
                $query->whereBetween('sale_time', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('sale_time', now()->month)
                    ->whereYear('sale_time', now()->year);
                break;
            case 'year':
                $query->whereYear('sale_time', now()->year);
                break;
        }

        return $query->get();
    }

    /**
     * Get date range description for period
     */
    private function getDateRangeForPeriod($period)
    {
        return match ($period) {
            'day' => today()->format('F j, Y'),
            'week' => now()->startOfWeek()->format('M d').' - '.now()->format('M d, Y'),
            'month' => now()->format('F Y'),
            'year' => now()->format('Y'),
            default => now()->format('F Y'),
        };
    }

    /**
     * Outstanding Reports (Age-wise and Bill-wise)
     */
    public function outstanding(Request $request): View
    {
        $data = $this->getOutstandingData($request);
        $type = $request->get('type', 'bill');

        if ($type === 'age') {
            return view('reports.sales.outstanding-ageing', $data);
        }

        return view('reports.sales.outstanding-billwise', $data);
    }

    /**
     * Print Outstanding Reports
     */
    public function printOutstanding(Request $request): View
    {
        $data = $this->getOutstandingData($request);
        $type = $request->get('type', 'bill');

        if ($type === 'age') {
            return view('reports.sales.outstanding-ageing-print', $data);
        }

        return view('reports.sales.outstanding-billwise-print', $data);
    }

    /**
     * Get outstanding data
     */
    private function getOutstandingData(Request $request): array
    {
        $type = $request->get('type', 'bill'); // bill or age

        $query = \App\Models\PosSale::where('outstanding_balance', '>', 0)
            ->with('customer');

        if ($request->customer_id) {
            $query->where('pos_customer_id', $request->customer_id);
        }

        $outstandingSales = $query->get();
        $totalOutstanding = $outstandingSales->sum('outstanding_balance');

        $data = [
            'outstandingSales' => $outstandingSales,
            'totalOutstanding' => $totalOutstanding,
            'type' => $type,
            'customers' => Customer::whereHas('sales', function ($q) {
                $q->where('outstanding_balance', '>', 0);
            })->get(),
        ];

        if ($type === 'age') {
            $data = array_merge($data, $this->calculateAgeing($outstandingSales));
        }

        return $data;
    }

    private function calculateAgeing($sales)
    {
        $ageing = [
            '0-30' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '90+' => ['count' => 0, 'amount' => 0],
        ];

        foreach ($sales as $sale) {
            $days = now()->diffInDays($sale->sale_time);
            $amount = $sale->outstanding_balance;

            if ($days <= 30) {
                $ageing['0-30']['count']++;
                $ageing['0-30']['amount'] += $amount;
            } elseif ($days <= 60) {
                $ageing['31-60']['count']++;
                $ageing['31-60']['amount'] += $amount;
            } elseif ($days <= 90) {
                $ageing['61-90']['count']++;
                $ageing['61-90']['amount'] += $amount;
            } else {
                $ageing['90+']['count']++;
                $ageing['90+']['amount'] += $amount;
            }
        }

        return compact('ageing');
    }

    /**
     * Customer-wise Profit & Loss
     */
    public function customerProfitLoss(Request $request): View
    {
        $data = $this->getCustomerProfitLossData($request);

        return view('reports.sales.customer-profit-loss', $data);
    }

    /**
     * Print Customer-wise Profit & Loss
     */
    public function printCustomerProfitLoss(Request $request): View
    {
        $data = $this->getCustomerProfitLossData($request);

        return view('reports.sales.customer-profit-loss-print', $data);
    }

    /**
     * Get customer profit loss data
     */
    private function getCustomerProfitLossData(Request $request): array
    {
        $customers = Customer::whereHas('sales')->with(['sales.items'])->get();
        $report = [];
        $totalSales = 0;
        $totalProfit = 0;

        foreach ($customers as $customer) {
            $salesTotal = $customer->sales->sum('total');
            $cogsTotal = $this->calculateCOGS($customer->sales);
            $profit = $salesTotal - $cogsTotal;

            if ($salesTotal > 0) {
                $report[] = [
                    'customer' => $customer,
                    'sales' => $salesTotal,
                    'cogs' => $cogsTotal,
                    'profit' => $profit,
                    'margin' => ($profit / $salesTotal) * 100,
                ];
                $totalSales += $salesTotal;
                $totalProfit += $profit;
            }
        }

        // Sort by profit descending
        usort($report, function ($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        return [
            'report' => $report,
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'overallMargin' => $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0,
        ];
    }
}
