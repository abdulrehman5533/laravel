<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\PosSaleItem;
use App\Services\POS\POSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaleItemController extends Controller
{
    public function __construct(private POSService $posService) {}

    /**
     * Search inventory products by SKU or name
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }

        $products = InventoryProduct::where(function ($q) use ($query) {
            $q
                ->where('sku', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('name', 'LIKE', "%{$query}%");
        })
            ->where('status', 'active')
            ->where('current_stock', '>', 0)
            ->select('id', 'sku', 'barcode', 'name', 'current_stock', 'selling_price', 'weight', 'gross_weight', 'net_weight', 'purity_id', 'making_charge_type', 'making_charge_value', 'wastage_percentage', 'tax_rate')
            ->with('purity')
            ->limit(15)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'name' => $product->name,
                    'current_stock' => (float) $product->current_stock,
                    'selling_price' => (float) $product->selling_price,
                    'weight' => (float) $product->weight,
                    'gross_weight' => (float) $product->gross_weight,
                    'net_weight' => (float) $product->net_weight,
                    'purity' => $product->purity?->name ?? 'N/A',
                    'making_charge' => (float) $product->making_charge_value,
                    'making_charge_type' => $product->making_charge_type ?? 'fixed',
                    'wastage_percent' => (float) $product->wastage_percentage,
                    'tax_rate' => (float) $product->tax_rate,
                ];
            });

        return response()->json(['products' => $products]);
    }

    /**
     * Get single product details
     */
    public function getProduct(InventoryProduct $product): JsonResponse
    {
        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'current_stock' => (float) $product->current_stock,
            'selling_price' => (float) $product->selling_price,
            'weight' => (float) $product->weight,
            'gross_weight' => (float) $product->gross_weight,
            'net_weight' => (float) $product->net_weight,
            'purity' => $product->purity?->name ?? 'N/A',
            'making_charge' => (float) $product->making_charge_value,
            'making_charge_type' => $product->making_charge_type ?? 'fixed',
            'wastage_percent' => (float) $product->wastage_percentage,
            'tax_rate' => (float) $product->tax_rate,
            'description' => $product->name,
        ]);
    }

    /**
     * Destroy a sale item (remove from sale)
     */
    public function destroy(PosSaleItem $item): RedirectResponse
    {
        $sale = $item->sale;

        if ($sale->status !== 'open') {
            return redirect()->back()->with('error', 'Cannot remove items from a non-open sale');
        }

        $item->delete();

        // Recalculate totals
        $this->posService->calculateSaleTotals($sale);

        return redirect()->back()->with('success', 'Item removed from sale');
    }
}
