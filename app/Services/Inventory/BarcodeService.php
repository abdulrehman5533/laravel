<?php

namespace App\Services\Inventory;

use App\Models\InventoryProduct;
use Illuminate\Support\Str;

class BarcodeService
{
    /**
     * Generate barcode data for a product
     */
    public function generateProductBarcode(InventoryProduct $product)
    {
        // Ensure SKU exists
        if (! $product->sku) {
            $product->update(['sku' => $this->generateUniqueSKU()]);
        }

        return [
            'value' => $product->sku,
            'format' => 'CODE128',
            'display_name' => $product->name,
            'meta' => [
                'purity' => $product->purity->name ?? 'N/A',
                'weight' => number_format($product->weight, 3).'g',
                'price' => number_format($product->selling_price, 2),
            ],
        ];
    }

    /**
     * Generate barcode for a batch
     */
    public function generateBatchBarcode($batchId)
    {
        return [
            'value' => 'BATCH-'.$batchId,
            'format' => 'CODE128',
            'display_name' => 'Batch #'.$batchId,
        ];
    }

    /**
     * Generate barcode for Karigar stock
     */
    public function generateKarigarBarcode($karigarId, $stockId)
    {
        return [
            'value' => 'KAR-'.$karigarId.'-'.$stockId,
            'format' => 'CODE128',
            'display_name' => 'Karigar Stock #'.$stockId,
        ];
    }

    /**
     * Generate barcode for Girvi Item
     */
    public function generateGirviBarcode($girviId, $itemId)
    {
        return [
            'value' => 'GRV-'.$girviId.'-'.$itemId,
            'format' => 'CODE128',
            'display_name' => 'Girvi Item #'.$itemId,
        ];
    }

    /**
     * Validate scanned barcode
     */
    public function validateScan($barcode)
    {
        $product = InventoryProduct::where('sku', $barcode)
            ->orWhere('barcode', $barcode)
            ->orWhere('tag_id', $barcode)
            ->first();

        if (! $product) {
            return [
                'success' => false,
                'message' => 'Product not found for scanned barcode: '.$barcode,
            ];
        }

        return [
            'success' => true,
            'product' => $product,
        ];
    }

    /**
     * Helper to generate unique SKU if missing
     */
    private function generateUniqueSKU()
    {
        do {
            $sku = 'PRD-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (InventoryProduct::where('sku', $sku)->exists());

        return $sku;
    }
}
