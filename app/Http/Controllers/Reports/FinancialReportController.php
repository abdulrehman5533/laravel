<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Expense;
use App\Models\GeneralLedger;
use App\Models\PosSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    /**
     * Display financial reports
     */
    public function index(): View
    {
        $stats = $this->getFinancialOverview();

        return view('reports.financial.index', $stats);
    }

    /**
     * Get financial overview statistics
     */
    private function getFinancialOverview(): array
    {
        $startDate = now()->startOfMonth();
        $endDate = now();

        $monthlySales = PosSale::whereBetween('sale_time', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total');

        $todaySales = PosSale::whereDate('sale_time', today())
            ->where('status', 'completed')
            ->sum('total');

        // Calculate MTD Profit
        $salesData = PosSale::whereBetween('sale_time', [$startDate, $endDate])
            ->where('status', 'completed')
            ->with(['items'])
            ->get();

        $cogs = $this->calculateCOGS($salesData);
        $monthlyProfit = $monthlySales - $cogs;
        $monthlyMargin = $monthlySales > 0 ? ($monthlyProfit / $monthlySales) * 100 : 0;

        return [
            'monthlySales' => $monthlySales,
            'todaySales' => $todaySales,
            'monthlyProfit' => $monthlyProfit,
            'monthlyMargin' => round($monthlyMargin, 2),
        ];
    }

    /**
     * Profit and loss report (integrated with accounting)
     */
    public function profitLoss(Request $request): View
    {
        $data = $this->getProfitLossData($request);

        return view('reports.financial.profit-loss', $data);
    }

    /**
     * Print profit and loss report
     */
    public function printProfitLoss(Request $request): View
    {
        $data = $this->getProfitLossData($request);

        return view('reports.financial.profit-loss-print', $data);
    }

    /**
     * Get profit and loss data
     */
    private function getProfitLossData(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $salesData = PosSale::whereBetween('sale_time', [$startDate, $endDate])
            ->with(['items', 'payments'])
            ->get();

        $revenue = $salesData->sum('total');
        $tax = $salesData->sum('tax_amount');
        $discount = $salesData->sum('discount');
        $makingCharges = $salesData->sum('making_charges');
        $wastage = $salesData->sum('wastage_amount');

        $costOfGoodsSold = $this->calculateCOGS($salesData);
        $grossProfit = $revenue - $costOfGoodsSold;
        $grossMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        $expenses = Expense::whereBetween('expenses.created_at', [$startDate, $endDate])
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name')
            ->get();

        $totalExpenses = $expenses->sum('total');

        $operatingProfit = $grossProfit - $totalExpenses;
        $operatingMargin = $revenue > 0 ? ($operatingProfit / $revenue) * 100 : 0;

        $accountingEntries = GeneralLedger::whereBetween('date', [$startDate, $endDate])->get();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'revenue' => $revenue,
            'tax' => $tax,
            'discount' => $discount,
            'makingCharges' => $makingCharges,
            'wastage' => $wastage,
            'costOfGoodsSold' => $costOfGoodsSold,
            'grossProfit' => $grossProfit,
            'grossMargin' => round($grossMargin, 2),
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'operatingProfit' => $operatingProfit,
            'operatingMargin' => round($operatingMargin, 2),
            'netProfit' => $operatingProfit,
            'netMargin' => $revenue > 0 ? ($operatingProfit / $revenue) * 100 : 0,
            'accountingEntries' => $accountingEntries,
        ];
    }

    /**
     * Cash flow report (integrated with accounting cashbook)
     */
    public function cashFlow(Request $request): View
    {
        $data = $this->getCashFlowData($request);

        return view('reports.financial.cash-flow', $data);
    }

    /**
     * Print cash flow report
     */
    public function printCashFlow(Request $request): View
    {
        $data = $this->getCashFlowData($request);

        return view('reports.financial.cash-flow-print', $data);
    }

    /**
     * Get cash flow data
     */
    private function getCashFlowData(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $cashbookEntries = Cashbook::whereBetween('date', [$startDate, $endDate])
            ->get();

        $inflows = Cashbook::whereBetween('date', [$startDate, $endDate])
            ->where('entry_type', 'receipt')
            ->sum('amount');

        $outflows = Cashbook::whereBetween('date', [$startDate, $endDate])
            ->where('entry_type', 'payment')
            ->sum('amount');

        $salesInflows = PosSale::whereBetween('sale_time', [$startDate, $endDate])
            ->sum(DB::raw('(SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_sale_id = pos_sales.id)'));

        $netCashFlow = $inflows - $outflows;

        $inflowsByCategory = Cashbook::whereBetween('date', [$startDate, $endDate])
            ->where('entry_type', 'receipt')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        $outflowsByCategory = Cashbook::whereBetween('date', [$startDate, $endDate])
            ->where('entry_type', 'payment')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'salesInflows' => $salesInflows,
            'netCashFlow' => $netCashFlow,
            'inflowsByCategory' => $inflowsByCategory,
            'outflowsByCategory' => $outflowsByCategory,
            'cashbookEntries' => $cashbookEntries,
        ];
    }

    /**
     * Metal Book Report (Metal Inventory Movement)
     */
    public function metalBook(Request $request): View
    {
        $data = $this->getMetalBookData($request);

        return view('reports.financial.metal-book', $data);
    }

    /**
     * Print metal book report
     */
    public function printMetalBook(Request $request): View
    {
        $data = $this->getMetalBookData($request);

        return view('reports.financial.metal-book-print', $data);
    }

    /**
     * Get metal book data
     */
    private function getMetalBookData(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $movements = \App\Models\StockMovement::with(['product.purity'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $openingFine = \App\Models\InventoryProduct::sum('fine_weight'); // Simplified

        return [
            'movements' => $movements,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingFine' => $openingFine,
        ];
    }

    /**
     * Trading Profit & Loss Report
     */
    public function tradingProfitLoss(Request $request): View
    {
        $data = $this->getTradingProfitLossData($request);

        return view('reports.financial.trading-profit-loss', $data);
    }

    /**
     * Print trading profit & loss report
     */
    public function printTradingProfitLoss(Request $request): View
    {
        $data = $this->getTradingProfitLossData($request);

        return view('reports.financial.trading-profit-loss-print', $data);
    }

    /**
     * Get trading profit & loss data
     */
    private function getTradingProfitLossData(Request $request): array
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $sales = PosSale::whereBetween('sale_time', [$startDate, $endDate])->sum('total');
        $purchases = \App\Models\PurchaseOrder::whereBetween('po_date', [$startDate, $endDate])->sum('total_amount');

        $openingStock = 0; // Should be calculated from history
        $closingStock = \App\Models\InventoryProduct::sum(DB::raw('current_stock * cost_price'));

        $grossProfit = ($sales + $closingStock) - ($openingStock + $purchases);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sales' => $sales,
            'purchases' => $purchases,
            'openingStock' => $openingStock,
            'closingStock' => $closingStock,
            'grossProfit' => $grossProfit,
        ];
    }

    /**
     * Calculate cost of goods sold from inventory
     */
    private function calculateCOGS($sales)
    {
        $cogs = 0;

        foreach ($sales as $sale) {
            if (isset($sale->items)) {
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
        }

        return $cogs;
    }
}
