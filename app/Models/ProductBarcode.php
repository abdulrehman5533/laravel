<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBarcode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_product_id',
        'barcode_number',
        'barcode_type',
        'barcode_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    /**
     * Generate barcode image
     */
    public function generateBarcodeImage(): void
    {
        try {
            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            
            $barcode = match($this->barcode_type) {
                'ean13' => $generator->getBarcode($this->barcode_number, \Picqer\Barcode\BarcodeGenerator::TYPE_EAN_13),
                'code128' => $generator->getBarcode($this->barcode_number, \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128),
                'qr' => $this->generateQRCode(),
                default => null,
            };

            if ($barcode) {
                $this->barcode_image = $barcode;
                $this->save();
            }
        } catch (\Exception $e) {
            \Log::error('Barcode generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR code
     */
    private function generateQRCode(): ?string
    {
        try {
            $qrCode = new \Endroid\QrCode\QrCode($this->barcode_number);
            $qrCode->setSize(300);
            
            return $qrCode->writeString();
        } catch (\Exception $e) {
            \Log::error('QR code generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate barcode format
     */
    public function isValidFormat(): bool
    {
        return match($this->barcode_type) {
            'ean13' => strlen($this->barcode_number) == 13 && is_numeric($this->barcode_number),
            'code128' => strlen($this->barcode_number) > 0,
            'qr' => strlen($this->barcode_number) > 0,
            default => false,
        };
    }

    /**
     * Activate barcode
     */
    public function activate(): bool
    {
        $this->is_active = true;
        $this->save();

        return true;
    }

    /**
     * Deactivate barcode
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        $this->save();

        return true;
    }

    /**
     * Get barcode image as base64
     */
    public function getBase64Image(): ?string
    {
        if (!$this->barcode_image) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($this->barcode_image);
    }
}
