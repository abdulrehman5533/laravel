<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    /**
     * Display inventory reports
     */
    public function index(): View
    {
        $products = InventoryProduct::all();
        $totalItems = $products->count();
        $lowStockItems = InventoryProduct::lowStock()->count();
        $totalValuation = $products->sum(function ($product) {
            return $product->current_stock * $product->cost_price;
        });

        return view('reports.inventory.index', compact('products', 'totalItems', 'lowStockItems', 'totalValuation'));
    }

    /**
     * Low stock report
     */
    public function lowStock(Request $request): View
    {
        $products = InventoryProduct::lowStock()->get();

        return view('reports.inventory.low-stock', compact('products'));
    }

    /**
     * Inventory valuation report
     */
    public function valuation(): View
    {
        $products = InventoryProduct::all();

        $totalValue = $products->sum(function ($product) {
            return $product->current_stock * $product->cost_price;
        });

        $totalRetailValue = $products->sum(function ($product) {
            return $product->current_stock * $product->selling_price;
        });

        return view('reports.inventory.valuation', compact('products', 'totalValue', 'totalRetailValue'));
    }

    /**
     * Weight Trial Report
     */
    public function weightTrial(Request $request): View
    {
        $products = InventoryProduct::with(['purity', 'category'])->get();

        return view('reports.inventory.weight-trial', compact('products'));
    }

    /**
     * Detailed Stock Ledger
     */
    public function stockLedger(Request $request): View
    {
        $query = \App\Models\StockMovement::with(['product', 'location', 'createdBy']);

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $movements = $query->latest()->paginate(50);
        $products = InventoryProduct::active()->get();

        return view('reports.inventory.stock-ledger', compact('movements', 'products'));
    }
}
