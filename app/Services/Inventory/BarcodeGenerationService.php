<?php

namespace App\Services\Inventory;

use App\Models\ProductBarcode;
use App\Models\InventoryProduct;

class BarcodeGenerationService
{
    public function generateBarcode(InventoryProduct $product, string $barcodeNumber, string $type = 'qr'): ProductBarcode
    {
        $barcode = ProductBarcode::create([
            'inventory_product_id' => $product->id,
            'barcode_number' => $barcodeNumber,
            'barcode_type' => $type,
        ]);

        return $barcode;
    }

    public function generateBulkBarcodes($productIds, string $type = 'qr'): array
    {
        $barcodes = [];
        
        foreach ($productIds as $productId) {
            $product = InventoryProduct::find($productId);
            if ($product) {
                $barcodeNumber = $this->generateBarcodeNumber($product);
                $barcode = $this->generateBarcode($product, $barcodeNumber, $type);
                $barcodes[] = $barcode;
            }
        }

        return $barcodes;
    }

    private function generateBarcodeNumber(InventoryProduct $product): string
    {
        return 'BC' . str_pad($product->id, 10, '0', STR_PAD_LEFT);
    }

    public function validateBarcode(string $barcodeNumber, string $type): bool
    {
        return match($type) {
            'ean13' => strlen($barcodeNumber) == 13 && is_numeric($barcodeNumber),
            'code128' => strlen($barcodeNumber) > 0,
            'qr' => strlen($barcodeNumber) > 0,
            default => false,
        };
    }

    public function getBarcode(string $barcodeNumber): ?ProductBarcode
    {
        return ProductBarcode::where('barcode_number', $barcodeNumber)->first();
    }
}
