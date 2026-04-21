<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagPrintingController extends Controller
{
    /**
     * Preview tags for selected products
     */
    public function preview(Request $request): View
    {
        $productIds = $request->input('product_ids', []);

        if (is_string($productIds)) {
            $productIds = explode(',', $productIds);
        }

        $products = InventoryProduct::whereIn('id', $productIds)->with(['category', 'purity'])->get();

        return view('inventory.tags.preview', compact('products'));
    }

    /**
     * Print tags for a single product
     */
    public function printSingle(InventoryProduct $product): View
    {
        $products = collect([$product]);

        return view('inventory.tags.print', compact('products'));
    }

    /**
     * Bulk print tags
     */
    public function bulkPrint(Request $request): View
    {
        $productIds = explode(',', $request->input('product_ids', ''));
        $products = InventoryProduct::whereIn('id', $productIds)->with(['category', 'purity'])->get();

        return view('inventory.tags.print', compact('products'));
    }
}
