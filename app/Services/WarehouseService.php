<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Models\WarehouseReconciliation;
use App\Models\InventoryProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class WarehouseService
{
    /**
     * Monitor IoT Sensors for a Warehouse
     */
    public function monitorIotSensors(Warehouse $warehouse)
    {
        // Mockup IoT data retrieval
        $currentTemp = rand(15, 35);
        $currentHumidity = rand(30, 70);

        $baseThresholds = [
            'temp_max' => 28,
            'temp_critical' => 35,
            'humidity_max' => 55,
            'humidity_critical' => 65,
        ];

        $thresholds = array_merge($baseThresholds, $warehouse->iot_thresholds ?? []);

        $alerts = [];
        $severity = 'normal';

        if ($currentTemp > $thresholds['temp_critical']) {
            $alerts[] = ['message' => "CRITICAL: Temperature spike at {$currentTemp}°C", 'severity' => 'critical'];
            $severity = 'critical';
        } elseif ($currentTemp > $thresholds['temp_max']) {
            $alerts[] = ['message' => "Warning: High temperature detected ({$currentTemp}°C)", 'severity' => 'warning'];
            $severity = 'warning';
        }

        if ($currentHumidity > $thresholds['humidity_critical']) {
            $alerts[] = ['message' => "CRITICAL: Humidity spike at {$currentHumidity}%", 'severity' => 'critical'];
            $severity = 'critical';
        } elseif ($currentHumidity > $thresholds['humidity_max']) {
            $alerts[] = ['message' => "Warning: High humidity detected ({$currentHumidity}%)", 'severity' => 'warning'];
            if ($severity !== 'critical') {
                $severity = 'warning';
            }
        }

        return [
            'status' => $severity,
            'timestamp' => now()->toIso8601String(),
            'readings' => [
                'temperature' => $currentTemp,
                'humidity' => $currentHumidity,
            ],
            'alerts' => $alerts,
        ];
    }

    /**
     * Start a Stock Reconciliation Session
     */
    public function startReconciliation(int $warehouseId, ?string $notes = null): WarehouseReconciliation
    {
        return WarehouseReconciliation::create([
            'warehouse_id' => $warehouseId,
            'reconciliation_number' => 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
            'status' => 'pending',
            'conducted_by' => Auth::id(),
            'notes' => $notes,
        ]);
    }

    /**
     * Add items to reconciliation from warehouse stock
     */
    public function addItemsToReconciliation(WarehouseReconciliation $reconciliation): void
    {
        $products = InventoryProduct::where('warehouse_id', $reconciliation->warehouse_id)
            ->where('status', 'active')
            ->get();

        foreach ($products as $product) {
            $reconciliation->items()->create([
                'product_id' => $product->id,
                'system_quantity' => $product->current_stock,
                'physical_quantity' => $product->current_stock, // Default to system, user will update
                'discrepancy' => 0,
            ]);
        }
    }

    /**
     * Finalize Reconciliation and apply adjustments
     */
    public function finalizeReconciliation(WarehouseReconciliation $reconciliation): void
    {
        DB::transaction(function () use ($reconciliation) {
            foreach ($reconciliation->items as $item) {
                if ($item->discrepancy != 0 && $item->adjustment_action === 'update_stock') {
                    $product = $item->product;
                    $type = $item->discrepancy > 0 ? 'add' : 'subtract';
                    $product->updateStock(abs($item->discrepancy), $type, 'reconciliation:' . $reconciliation->reconciliation_number);
                    $item->update(['is_adjusted' => true]);
                }
            }

            $reconciliation->update([
                'status' => 'completed',
                'conducted_at' => now(),
            ]);
        });
    }

    /**
     * Record wastage for a warehouse item
     */
    public function recordWastage(int $productId, float $quantity, string $reason, ?string $notes = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $notes) {
            $product = InventoryProduct::findOrFail($productId);
            return $product->recordWastage($quantity, $reason, $notes);
        });
    }

    /**
     * Track a logistics shipment
     */
    public function trackShipment($trackingNumber)
    {
        // Enterprise API Integration mockup
        return [
            'tracking_number' => $trackingNumber,
            'last_location' => 'Dubai Logistics Hub',
            'status' => 'In Transit',
            'eta' => now()->addDays(2)->toDateTimeString(),
            'checkpoints' => [
                ['time' => now()->subDay(), 'location' => 'Origin Warehouse', 'status' => 'Picked Up'],
                ['time' => now()->subHours(12), 'location' => 'Transit Center', 'status' => 'Processing'],
            ]
        ];
    }
}
