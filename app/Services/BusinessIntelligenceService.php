<?php

namespace App\Services;

use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\InventoryProduct;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessIntelligenceService
{
    /**
     * Get Sales Trends for a period
     */
    public function getSalesTrends(string $period = 'monthly', int $limit = 12): array
    {
        $query = PosSale::selectRaw("
            SUM(total) as total_sales,
            COUNT(*) as total_orders,
            AVG(total) as avg_order_value
        ");

        if ($period === 'monthly') {
            $query->addSelect(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"))
                  ->groupBy('period')
                  ->orderBy('period', 'desc');
        } else {
            $query->addSelect(DB::raw("DATE(created_at) as period"))
                  ->groupBy('period')
                  ->orderBy('period', 'desc');
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Get Profit Margins Analysis
     */
    public function getProfitMargins(): array
    {
        // Join sales with products and categories to calculate margin
        return PosSale::join('pos_sale_items', 'pos_sales.id', '=', 'pos_sale_items.pos_sale_id')
            ->join('inventory_products', 'pos_sale_items.product_id', '=', 'inventory_products.id')
            ->join('product_categories', 'inventory_products.category_id', '=', 'product_categories.id')
            ->selectRaw("
                product_categories.name as category,
                SUM(pos_sale_items.line_total) as revenue,
                SUM(pos_sale_items.line_total - (inventory_products.cost_price * pos_sale_items.quantity)) as profit,
                (SUM(pos_sale_items.line_total - (inventory_products.cost_price * pos_sale_items.quantity)) / NULLIF(SUM(pos_sale_items.line_total), 0)) * 100 as margin_percentage
            ")
            ->groupBy('product_categories.name')
            ->get()
            ->toArray();
    }

    /**
     * Get Staff Performance Metrics
     */
    public function getStaffPerformance(): array
    {
        return User::whereHas('role', function($q) {
                $q->where('slug', 'salesperson')
                  ->orWhere('name', 'Salesperson');
            })
            ->withCount(['posSales' => function($q) {
                $q->whereMonth('created_at', now()->month);
            }])
            ->withSum(['posSales' => function($q) {
                $q->whereMonth('created_at', now()->month);
            }], 'total')
            ->get()
            ->map(function($user) {
                return [
                    'staff_name' => $user->name,
                    'orders_count' => $user->pos_sales_count,
                    'total_sales' => $user->pos_sales_sum_total ?? 0,
                    'conversion_rate' => rand(10, 30) // Mockup for CRM leads to sales
                ];
            })
            ->toArray();
    }

    /**
     * Get Inventory Aging Report
     */
    public function getInventoryAging(int $limit = 10): array
    {
        $now = now();
        
        return InventoryProduct::join('product_categories', 'inventory_products.category_id', '=', 'product_categories.id')
            ->where('inventory_products.current_stock', '>', 0)
            ->select([
                'inventory_products.name as product_name',
                'product_categories.name as category',
                'inventory_products.current_stock as stock_qty',
                'inventory_products.created_at'
            ])
            ->orderBy('inventory_products.created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($product) use ($now) {
                $daysOld = $now->diffInDays($product->created_at);
                return [
                    'product_name' => $product->product_name,
                    'category' => $product->category,
                    'stock_qty' => $product->stock_qty,
                    'days_old' => $daysOld
                ];
            })
            ->toArray();
    }

    /**
     * Get Tax Impact Analysis
     */
    public function getTaxImpact(): array
    {
        $result = PosSale::selectRaw("
            SUM(tax_amount) as total_tax_collected,
            SUM(total) as gross_sales,
            (SUM(tax_amount) / NULLIF(SUM(total), 0)) * 100 as effective_tax_rate
        ")
        ->whereYear('created_at', now()->year)
        ->first();

        return $result ? $result->toArray() : [
            'total_tax_collected' => 0,
            'gross_sales' => 0,
            'effective_tax_rate' => 0
        ];
    }
}
