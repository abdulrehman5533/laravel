<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\InventoryTransfer;
use App\Models\StockAlert;
use App\Services\Inventory\InventoryService;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function dashboard(): View
    {
        $metrics = $this->inventoryService->getInventoryMetrics();
        $lowStock = $this->inventoryService->getLowStockProducts();
        $fastMoving = $this->inventoryService->getFastMovingProducts();
        $slowMoving = $this->inventoryService->getSlowMovingProducts();

        $activeAlertsCount = StockAlert::where('is_acknowledged', false)->count();
        $pendingTransfersCount = InventoryTransfer::whereIn('status', ['requested', 'approved', 'dispatched'])->count();

        $inventory = [
            'metrics' => $metrics,
            'low_stock' => $lowStock,
            'fast_moving' => $fastMoving,
            'slow_moving' => $slowMoving,
            'by_category' => $metrics['by_category'],
            'alerts_count' => $activeAlertsCount,
            'transfers_count' => $pendingTransfersCount,
        ];

        return view('inventory.dashboard', compact('inventory'));
    }

    public function consolidated(): View
    {
        $branches = \App\Models\Branch::withCount('products')->get();
        $branchStock = $branches->map(function ($branch) {
            $products = InventoryProduct::where('branch_id', $branch->id)->get();

            return [
                'branch' => $branch,
                'total_items' => $products->count(),
                'total_stock' => $products->sum('current_stock'),
                'total_value' => $products->sum(fn ($p) => $p->current_stock * $p->cost_price),
            ];
        });

        return view('inventory.consolidated', compact('branchStock'));
    }

    public function analytics(): View
    {
        $metrics = $this->inventoryService->getInventoryMetrics();
        $fastMoving = $this->inventoryService->getFastMovingProducts();
        $slowMoving = $this->inventoryService->getSlowMovingProducts();
        $turnover = $this->inventoryService->getInventoryTurnover();

        $categoryMetrics = $metrics['by_category']->map(function ($cat) {
            $products = $cat->products ?? InventoryProduct::where('category_id', $cat->id)->get();

            return [
                'category' => $cat,
                'product_count' => count($products),
                'inventory_value' => $products->sum(fn ($p) => $p->current_stock * $p->cost_price),
                'retail_value' => $products->sum(fn ($p) => $p->current_stock * $p->selling_price),
            ];
        });

        return view('inventory.analytics', [
            'metrics' => $metrics,
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
            'turnover' => $turnover,
            'categoryMetrics' => $categoryMetrics,
        ]);
    }
}
