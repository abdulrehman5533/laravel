<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\StockAlert;
use App\Services\Inventory\BarcodeService;
use App\Services\Inventory\ReorderService;
use App\Services\Inventory\StockAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryExtensionController extends Controller
{
    public function __construct(
        private BarcodeService $barcodeService,
        private ReorderService $reorderService,
        private StockAIService $stockAIService
    ) {}

    /**
     * Display stock alerts
     */
    public function alerts()
    {
        $alerts = StockAlert::with('product.category')
            ->where('is_acknowledged', false)
            ->latest()
            ->paginate(15);

        return view('inventory.extensions.alerts', compact('alerts'));
    }

    /**
     * Trigger reorder check
     */
    public function checkReorder()
    {
        $count = $this->reorderService->checkAndTriggerAlerts();

        return back()->with('success', "Reorder check completed. {$count} new alerts triggered.");
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(StockAlert $alert)
    {
        $this->reorderService->acknowledge($alert->id, Auth::id());

        return back()->with('success', 'Alert acknowledged.');
    }

    /**
     * AI Stock Intelligence Dashboard
     */
    public function intelligence()
    {
        $products = InventoryProduct::with('intelligence')
            ->where('status', 'active')
            ->get()
            ->map(function ($product) {
                if (! $product->intelligence) {
                    $this->stockAIService->analyzeProduct($product);
                    $product->load('intelligence');
                }

                return $product;
            });

        $stats = [
            'dead_stock' => $products->where('intelligence.movement_speed', 'dead')->count(),
            'slow_moving' => $products->where('intelligence.movement_speed', 'slow')->count(),
            'high_risk_value' => $products->where('intelligence.risk_score', '>', 70)->sum('total_value'),
            'branch_distribution' => $products->groupBy('branch.name')->map->count(),
            'purity_distribution' => $products->groupBy('purity.name')->map->count(),
        ];

        return view('inventory.extensions.intelligence', compact('products', 'stats'));
    }

    /**
     * Scan Barcode / RFID
     */
    public function scan(Request $request)
    {
        $barcode = $request->barcode;
        $result = $this->barcodeService->validateScan($barcode);

        if (! $result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Print Barcode Label
     */
    public function printLabel(InventoryProduct $product)
    {
        $barcodeData = $this->barcodeService->generateProductBarcode($product);

        return view('inventory.extensions.print-label', compact('product', 'barcodeData'));
    }
}
