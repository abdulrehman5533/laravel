<?php

namespace App\Services\Inventory;

use App\Models\InventoryProduct;
use App\Models\PosSale;
use Illuminate\Support\Facades\DB;

class InventoryIntegrationService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function validateAndReserveStock($items): array
    {
        $errors = [];
        $reservations = [];

        foreach ($items as $item) {
            $product = InventoryProduct::find($item['product_id']);

            if (! $product) {
                $errors[] = "Product ID {$item['product_id']} not found";

                continue;
            }

            if ($product->current_stock < $item['quantity']) {
                $errors[] = "{$product->name}: Only {$product->current_stock} units available, requested {$item['quantity']}";

                continue;
            }

            $reservations[$product->id] = $item['quantity'];
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'reservations' => $reservations,
        ];
    }

    public function deductStockFromSale(PosSale $sale): bool
    {
        return DB::transaction(function () use ($sale) {
            if ($sale->stock_moved) {
                return false;
            }

            foreach ($sale->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $this->inventoryService->updateStock(
                    $item->product_id,
                    $item->quantity,
                    'subtract',
                    'sale:'.$sale->id,
                    'POS Sale - '.$sale->invoice_no
                );
            }

            $sale->update(['stock_moved' => true]);

            return true;
        });
    }

    public function restoreStockFromSale(PosSale $sale): bool
    {
        return DB::transaction(function () use ($sale) {
            if (! $sale->stock_moved) {
                return false;
            }

            foreach ($sale->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $this->inventoryService->updateStock(
                    $item->product_id,
                    $item->quantity,
                    'add',
                    'sale_revert:'.$sale->id,
                    'Sale Cancelled/Returned - '.$sale->invoice_no
                );
            }

            $sale->update(['stock_moved' => false]);

            return true;
        });
    }

    public function getProductStockStatus($productId): array
    {
        $product = InventoryProduct::findOrFail($productId);

        return [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'current_stock' => $product->current_stock,
            'reorder_level' => $product->reorder_level,
            'reorder_quantity' => $product->reorder_quantity,
            'is_low_stock' => $product->current_stock <= $product->reorder_level,
            'is_out_of_stock' => $product->current_stock <= 0,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
            'profit_per_unit' => $product->selling_price - $product->cost_price,
            'total_stock_value' => $product->current_stock * $product->cost_price,
            'total_retail_value' => $product->current_stock * $product->selling_price,
        ];
    }

    public function getAvailableStockForSale($productId, $requestedQuantity): array
    {
        $product = InventoryProduct::findOrFail($productId);
        $available = max(0, $product->current_stock);

        return [
            'product_id' => $product->id,
            'requested' => $requestedQuantity,
            'available' => $available,
            'can_fulfill' => $available >= $requestedQuantity,
            'shortage' => max(0, $requestedQuantity - $available),
            'fulfillment_percentage' => $product->current_stock > 0 ? min(100, ($available / $requestedQuantity) * 100) : 0,
        ];
    }

    public function getSalesRecommendations(): array
    {
        $metrics = $this->inventoryService->getInventoryMetrics();
        $lowStock = $this->inventoryService->getLowStockProducts();
        $fastMoving = $this->inventoryService->getFastMovingProducts();

        return [
            'reorder_urgently' => $lowStock->filter(fn ($p) => $p->current_stock <= ($p->reorder_level * 0.5))->map(fn ($p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'current_stock' => $p->current_stock,
                'reorder_quantity' => $p->reorder_quantity,
                'days_to_stockout' => $p->current_stock > 0 ? ceil($p->current_stock / 1) : 0,
            ]),
            'promote_these_products' => $fastMoving->map(fn ($item) => [
                'product_id' => $item['product']->id,
                'name' => $item['product']->name,
                'monthly_sales' => number_format($item['movements'], 0),
                'profit_per_unit' => $item['product']->selling_price - $item['product']->cost_price,
            ]),
            'optimize_stock' => [
                'total_profit_potential' => $metrics['profit_potential'],
                'profit_margin' => $metrics['profit_margin_percent'],
            ],
        ];
    }

    public function checkInventoryAlerts(): array
    {
        $alerts = [];

        $lowStockProducts = $this->inventoryService->getLowStockProducts();
        if ($lowStockProducts->count() > 0) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'count' => $lowStockProducts->count(),
                'message' => "{$lowStockProducts->count()} products have low stock",
            ];
        }

        $outOfStock = InventoryProduct::where('current_stock', '<=', 0)->count();
        if ($outOfStock > 0) {
            $alerts[] = [
                'type' => 'out_of_stock',
                'severity' => 'danger',
                'count' => $outOfStock,
                'message' => "{$outOfStock} products are out of stock",
            ];
        }

        $expiredProducts = InventoryProduct::where('created_at', '<', now()->subMonths(12))->count();
        if ($expiredProducts > 0) {
            $alerts[] = [
                'type' => 'old_stock',
                'severity' => 'warning',
                'count' => $expiredProducts,
                'message' => "{$expiredProducts} products have been in stock for over 1 year",
            ];
        }

        return $alerts;
    }

    public function getInventorySummaryForDashboard(): array
    {
        $metrics = $this->inventoryService->getInventoryMetrics();

        return [
            'total_products' => $metrics['total_products'],
            'low_stock_count' => $metrics['low_stock_count'],
            'out_of_stock_count' => $metrics['out_of_stock_count'],
            'total_inventory_value' => number_format($metrics['total_inventory_value'], 2),
            'total_retail_value' => number_format($metrics['total_retail_value'], 2),
            'profit_potential' => number_format($metrics['profit_potential'], 2),
            'profit_margin_percent' => number_format($metrics['profit_margin_percent'], 1),
            'alerts' => $this->checkInventoryAlerts(),
        ];
    }
}
