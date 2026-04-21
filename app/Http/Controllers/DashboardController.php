<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GoldRate;
use App\Models\PosSale;
use App\Models\ProductCategory;
use App\Models\Vendor;
use App\Services\AiInsightsService;
use App\Services\Inventory\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $inventoryService;

    private $aiInsights;

    public function __construct(InventoryService $inventoryService, AiInsightsService $aiInsights)
    {
        $this->inventoryService = $inventoryService;
        $this->aiInsights = $aiInsights;
    }

    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $inventoryMetrics = $this->inventoryService->getInventoryMetrics();

        $stats = [
            'today_sales' => PosSale::whereDate('sale_time', $today)->where('status', 'completed')->sum('total'),
            'weekly_sales' => PosSale::where('sale_time', '>=', $weekStart)->where('status', 'completed')->sum('total'),
            'monthly_sales' => PosSale::where('sale_time', '>=', $monthStart)->where('status', 'completed')->sum('total'),
            'total_sales' => PosSale::where('status', 'completed')->sum('total'),

            'ai_insights' => $this->aiInsights->getExecutiveInsights(),

            'total_products' => $inventoryMetrics['total_products'],
            'low_stock' => $inventoryMetrics['low_stock_count'],
            'inventory_value' => $inventoryMetrics['total_inventory_value'],
            'inventory_selling_value' => $inventoryMetrics['total_retail_value'],
            'profit_potential' => $inventoryMetrics['profit_potential'],

            'total_receivables' => PosSale::where('status', 'completed')->sum('outstanding_balance'),
            'total_customers' => Customer::count(),
            'new_customers_this_month' => Customer::whereMonth('created_at', Carbon::now()->month)->count(),

            'total_vendors' => Vendor::count(),
            'active_vendors' => Vendor::where('is_active', true)->count(),

            'recent_sales' => PosSale::with(['customer', 'payments'])->latest('sale_time')->take(10)->get(),
            'low_stock_products' => $this->inventoryService->getLowStockProducts()->take(5),
            'recent_customers' => Customer::latest()->take(5)->get(),

            'sales_chart' => $this->getSalesChartData(),
            'inventory_chart' => $this->getInventoryChartData(),
            'category_sales_chart' => $this->getCategorySalesChartData(),
            'purity_sales_chart' => $this->getPuritySalesChartData(),
            'payment_methods_chart' => $this->getPaymentMethodsChartData(),
            'gold_rate' => GoldRate::getTodayRate(),
        ];

        return view('dashboard', $stats);
    }

    private function getCategorySalesChartData()
    {
        $data = \App\Models\PosSaleItem::join('inventory_products', 'pos_sale_items.product_id', '=', 'inventory_products.id')
            ->join('product_categories', 'inventory_products.category_id', '=', 'product_categories.id')
            ->selectRaw('product_categories.name, sum(pos_sale_items.line_total) as total')
            ->groupBy('product_categories.name')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }

    private function getPuritySalesChartData()
    {
        $data = \App\Models\PosSaleItem::whereNotNull('gold_purity')
            ->selectRaw('gold_purity, sum(line_total) as total')
            ->groupBy('gold_purity')
            ->get();

        return [
            'labels' => $data->pluck('gold_purity')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }

    private function getPaymentMethodsChartData()
    {
        $data = \App\Models\PosPayment::selectRaw('payment_method, sum(amount) as total')
            ->groupBy('payment_method')
            ->get();

        return [
            'labels' => $data->pluck('payment_method')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ];
    }

    private function getSalesChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $sales = PosSale::whereDate('sale_time', $date)->where('status', 'completed')->sum('total');

            $data['labels'][] = $date->format('D');
            $data['sales'][] = $sales ?? 0;
        }

        return $data;
    }

    private function getInventoryChartData()
    {
        $categories = ProductCategory::active()->withSum('products', 'selling_price')->get();

        return [
            'labels' => $categories->pluck('name')->toArray(),
            'values' => $categories->pluck('products_sum_selling_price')->map(fn ($val) => $val ?? 0)->toArray(),
            'colors' => ['#D4AF37', '#C0C0C0', '#E5E4E2', '#B87333', '#FFD700', '#DAA520', '#1a1a2e'],
        ];
    }

    public function goldRate()
    {
        $goldRate = GoldRate::getTodayRate();

        return view('gold-rate', compact('goldRate'));
    }

    public function updateGoldRate(Request $request)
    {
        $request->validate([
            'rate_22k' => 'required|numeric|min:0',
            'rate_18k' => 'required|numeric|min:0',
            'rate_24k' => 'required|numeric|min:0',
            'silver_rate' => 'required|numeric|min:0',
        ]);

        GoldRate::create([
            'rate_22k' => $request->rate_22k,
            'rate_18k' => $request->rate_18k,
            'rate_24k' => $request->rate_24k,
            'silver_rate' => $request->silver_rate,
            'date' => today(),
        ]);

        return redirect()->route('gold-rate')->with('success', 'Gold rates updated successfully!');
    }

    public function getGoldRateApi()
    {
        $goldRate = GoldRate::getTodayRate();

        return response()->json($goldRate);
    }
}
