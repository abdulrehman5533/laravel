<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GoldRate;
use App\Models\PosSale;
use App\Models\Vendor;
use App\Services\Inventory\InventoryService;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    private $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
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

            'total_products' => $inventoryMetrics['total_products'],
            'low_stock' => $inventoryMetrics['low_stock_count'],

            'total_customers' => Customer::count(),
            'total_vendors' => Vendor::count(),

            'recent_sales' => PosSale::with('customer')->latest()->take(5)->get(),
            'low_stock_products' => $this->inventoryService->getLowStockProducts()->take(5),

            'sales_chart' => $this->getSalesChartData(),
            'gold_rate' => GoldRate::getTodayRate(),
        ];

        return view('manager.dashboard', $stats);
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
}
