<?php

namespace App\Services\Inventory;

use App\Models\DamageRepairTracking;
use App\Models\InventoryProduct;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\WastageTracking;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function updateStock($productId, $quantity, $type, $reference = null, $notes = null, $weight = 0, $pieces = 0)
    {
        return DB::transaction(function () use ($productId, $quantity, $type, $reference, $notes, $weight, $pieces) {
            $product = InventoryProduct::findOrFail($productId);

            if ($type === 'add') {
                $product->current_stock += $quantity;
                $product->current_pieces += $pieces;
                $product->net_weight += $weight;
            } elseif ($type === 'subtract') {
                if ($product->current_stock < $quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
                $product->current_stock -= $quantity;
                $product->current_pieces -= $pieces;
                $product->net_weight -= $weight;
            }

            $product->calculateFineWeight();
            $product->save();

            $movement = StockMovement::create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'weight' => $weight,
                'pieces' => $pieces,
                'type' => $type,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            return $movement;
        });
    }

    public function recordWastage($productId, $quantity, $reason, $notes = null, $weight = 0, $pieces = 0)
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $notes, $weight, $pieces) {
            $product = InventoryProduct::findOrFail($productId);

            $w = $weight ?: $quantity;
            $this->updateStock($productId, $quantity, 'subtract', 'wastage', null, $w, $pieces);

            $wastage = WastageTracking::create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'reason' => $reason,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            return $wastage;
        });
    }

    public function recordDamage($productId, $quantity, $status, $notes = null, $weight = 0, $pieces = 0)
    {
        return DB::transaction(function () use ($productId, $quantity, $status, $notes, $weight, $pieces) {
            $product = InventoryProduct::findOrFail($productId);

            $w = $weight ?: $quantity;
            $this->updateStock($productId, $quantity, 'subtract', 'damage', null, $w, $pieces);

            $damage = DamageRepairTracking::create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'status' => $status,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            return $damage;
        });
    }

    public function getLowStockProducts()
    {
        return InventoryProduct::lowStock()
            ->with(['category', 'purity'])
            ->get();
    }

    public function getLowStockAlert($perPage = 20)
    {
        $lowStockQuery = InventoryProduct::lowStock()->with(['category', 'purity']);
        $lowStockCount = $lowStockQuery->count();
        $products = $lowStockQuery->paginate($perPage);

        return [
            'count' => $lowStockCount,
            'products' => $products,
            'alert_status' => $lowStockCount > 0 ? 'warning' : 'success',
        ];
    }

    public function getStockAgingReport($days = 90, $perPage = 20)
    {
        return InventoryProduct::active()
            ->with(['category', 'purity'])
            ->paginate($perPage)
            ->through(function ($product) {
                $product->age_days = $product->getAgeInDays();
                $product->age_category = $this->getAgeCategory($product->age_days);

                return $product;
            });
    }

    public function getAgeGroupSummary()
    {
        $products = InventoryProduct::active()->get();

        return [
            'recent' => $products->filter(fn ($p) => $this->getAgeCategory($p->getAgeInDays()) === 'Recent')->count(),
            'medium' => $products->filter(fn ($p) => $this->getAgeCategory($p->getAgeInDays()) === 'Medium')->count(),
            'old' => $products->filter(fn ($p) => $this->getAgeCategory($p->getAgeInDays()) === 'Old')->count(),
            'very_old' => $products->filter(fn ($p) => $this->getAgeCategory($p->getAgeInDays()) === 'Very Old')->count(),
        ];
    }

    private function getAgeCategory($days)
    {
        if ($days < 30) {
            return 'Recent';
        }
        if ($days < 90) {
            return 'Medium';
        }
        if ($days < 180) {
            return 'Old';
        }

        return 'Very Old';
    }

    public function getWastageReport($fromDate = null, $toDate = null)
    {
        $query = WastageTracking::with(['product', 'createdBy']);

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        $records = $query->latest()->get();

        return [
            'records' => $records,
            'total_quantity' => $records->sum('quantity'),
            'total_cost' => $records->load('product')->reduce(function ($carry, $record) {
                return $carry + (($record->product->cost_price ?? 0) * $record->quantity);
            }, 0),
            'by_reason' => $records->groupBy('reason')->map(fn ($g) => [
                'count' => $g->count(),
                'quantity' => $g->sum('quantity'),
                'cost' => $g->load('product')->sum(fn ($r) => ($r->product->cost_price ?? 0) * $r->quantity),
            ]),
        ];
    }

    public function getMultiLocationInventory()
    {
        $locations = StockLocation::active()->get();
        $products = InventoryProduct::active()->with(['category'])->get();

        // Get all movements grouped by product and location to avoid N+1
        $movements = StockMovement::select('product_id', 'location_id', 'type', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id', 'location_id', 'type')
            ->get()
            ->groupBy('product_id');

        $inventory = [];
        foreach ($products as $product) {
            $productMovements = $movements->get($product->id, collect());

            $inventory[$product->id] = [
                'product' => $product,
                'locations' => [],
            ];

            foreach ($locations as $location) {
                $locMovements = $productMovements->where('location_id', $location->id);

                $addQty = $locMovements->filter(fn ($m) => in_array($m->type, ['add', 'transfer_in']))->sum('total_qty');
                $subQty = $locMovements->filter(fn ($m) => in_array($m->type, ['subtract', 'transfer_out', 'wastage', 'damage']))->sum('total_qty');

                $quantity = $addQty - $subQty;

                if ($quantity != 0) {
                    $inventory[$product->id]['locations'][$location->id] = [
                        'location' => $location,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        return $inventory;
    }

    public function getInventoryMetrics()
    {
        $totalProducts = InventoryProduct::active()->count();
        $lowStockCount = InventoryProduct::lowStock()->count();
        $outOfStockCount = InventoryProduct::where('current_stock', '<=', 0)->count();

        $totalInventoryValue = InventoryProduct::active()
            ->get()
            ->sum(fn ($p) => $p->current_stock * $p->cost_price);

        $totalRetailValue = InventoryProduct::active()
            ->get()
            ->sum(fn ($p) => $p->current_stock * $p->selling_price);

        $recentWastage = WastageTracking::where('created_at', '>=', now()->subDays(30))
            ->sum('quantity');

        $categories = InventoryProduct::active()
            ->selectRaw('category_id, COUNT(*) as count, SUM(current_stock * cost_price) as value')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'total_inventory_value' => $totalInventoryValue,
            'total_retail_value' => $totalRetailValue,
            'profit_potential' => $totalRetailValue - $totalInventoryValue,
            'profit_margin_percent' => $totalInventoryValue > 0 ? (($totalRetailValue - $totalInventoryValue) / $totalInventoryValue * 100) : 0,
            'recent_wastage' => $recentWastage,
            'by_category' => $categories,
        ];
    }

    public function getFastMovingProducts($days = 30)
    {
        $products = InventoryProduct::active()->get();

        $fastMoving = $products->map(function ($product) use ($days) {
            $movements = $product->stockMovements()
                ->where('created_at', '>=', now()->subDays($days))
                ->sum('quantity');

            return [
                'product' => $product,
                'movements' => abs($movements),
                'turnover_rate' => $product->current_stock > 0 ? (abs($movements) / $product->current_stock) : 0,
            ];
        })->sortByDesc('movements')->take(10);

        return $fastMoving;
    }

    public function getSlowMovingProducts($days = 90)
    {
        $products = InventoryProduct::active()->get();

        $slowMoving = $products->map(function ($product) use ($days) {
            $movements = $product->stockMovements()
                ->where('created_at', '>=', now()->subDays($days))
                ->count();

            $ageInDays = $product->getAgeInDays();

            return [
                'product' => $product,
                'movements' => $movements,
                'age_days' => $ageInDays,
                'stock_value' => $product->current_stock * $product->cost_price,
            ];
        })->filter(fn ($item) => $item['movements'] === 0 && $item['age_days'] > 60)
            ->sortByDesc('stock_value')
            ->take(10);

        return $slowMoving;
    }

    public function checkReorderThreshold($productId)
    {
        $product = InventoryProduct::findOrFail($productId);

        if ($product->current_stock <= $product->reorder_level) {
            return [
                'should_reorder' => true,
                'product' => $product,
                'quantity_to_order' => $product->reorder_quantity,
                'current_stock' => $product->current_stock,
                'reorder_level' => $product->reorder_level,
            ];
        }

        return ['should_reorder' => false];
    }

    public function getInventoryTurnover($days = 30)
    {
        $products = InventoryProduct::active()->get();

        $turnover = $products->map(function ($product) use ($days) {
            $salesValue = $product->stockMovements()
                ->where('type', 'subtract')
                ->where('reference', 'like', 'sale:%')
                ->where('created_at', '>=', now()->subDays($days))
                ->sum(DB::raw('quantity * '.$product->selling_price));

            $inventoryValue = $product->current_stock * $product->cost_price;

            return [
                'product' => $product,
                'sales_value' => $salesValue,
                'inventory_value' => $inventoryValue,
                'turnover_ratio' => $inventoryValue > 0 ? ($salesValue / $inventoryValue) : 0,
            ];
        })->sortByDesc('turnover_ratio');

        return $turnover;
    }

    public function transferStock($productId, $fromLocation, $toLocation, $quantity, $notes = null, $weight = 0, $pieces = 0)
    {
        return DB::transaction(function () use ($productId, $fromLocation, $toLocation, $quantity, $notes, $weight, $pieces) {
            $product = InventoryProduct::findOrFail($productId);

            StockMovement::create([
                'product_id' => $productId,
                'location_id' => $fromLocation,
                'quantity' => $quantity,
                'weight' => $weight,
                'pieces' => $pieces,
                'type' => 'transfer_out',
                'reference' => $toLocation,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            StockMovement::create([
                'product_id' => $productId,
                'location_id' => $toLocation,
                'quantity' => $quantity,
                'weight' => $weight,
                'pieces' => $pieces,
                'type' => 'transfer_in',
                'reference' => $fromLocation,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            return ['success' => true];
        });
    }
}
