<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Models\GoldRate;
use App\Models\InventoryProduct;
use App\Models\PosSale;
use Carbon\Carbon;

class ViewerDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'today_sales_count' => PosSale::whereDate('sale_time', $today)->where('status', 'completed')->count(),
            'total_products' => InventoryProduct::count(),
            'gold_rate' => GoldRate::getTodayRate(),
            'recent_sales' => PosSale::with('customer')->latest()->take(5)->get(),
        ];

        return view('viewer.dashboard', $stats);
    }
}
