<?php

namespace App\Services\Inventory;

use App\Mail\Inventory\StockAlertMail;
use App\Models\InventoryProduct;
use App\Models\StockAlert;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReorderService
{
    /**
     * Check stock levels and trigger alerts
     */
    public function checkAndTriggerAlerts()
    {
        $products = InventoryProduct::where('status', 'active')->get();
        $purityReorderLevels = SystemSetting::get('purity_reorder_levels', []);
        $triggeredCount = 0;

        foreach ($products as $product) {
            $effectiveReorderLevel = $this->getEffectiveReorderLevel($product, $purityReorderLevels);

            if ($product->current_stock <= $effectiveReorderLevel) {
                if ($this->shouldTriggerAlert($product)) {
                    $this->createAlert($product);
                    $triggeredCount++;
                }
            }
        }

        if ($triggeredCount > 0) {
            Log::info("Stock check completed. {$triggeredCount} new alerts triggered.");
        }

        return $triggeredCount;
    }

    /**
     * Get effective reorder level considering Item and Purity defaults
     */
    protected function getEffectiveReorderLevel(InventoryProduct $product, array $purityLevels)
    {
        // 1. Check item specific level (if set and > 0)
        if ($product->reorder_level > 0) {
            return $product->reorder_level;
        }

        // 2. Check purity specific default level
        if ($product->purity_id && isset($purityLevels[$product->purity_id])) {
            return $purityLevels[$product->purity_id];
        }

        // 3. System default
        return SystemSetting::get('default_reorder_level', 5);
    }

    /**
     * Determine if an alert should be triggered (prevents flooding)
     */
    protected function shouldTriggerAlert(InventoryProduct $product)
    {
        $lastAlert = StockAlert::where('product_id', $product->id)
            ->where('is_acknowledged', false)
            ->latest()
            ->first();

        if (! $lastAlert) {
            return true;
        }

        // Snooze logic
        if ($lastAlert->snoozed_until && $lastAlert->snoozed_until->isFuture()) {
            return false;
        }

        // Only trigger once every 24 hours if not acknowledged
        return $lastAlert->created_at->diffInHours(now()) >= 24;
    }

    /**
     * Create a new stock alert
     */
    protected function createAlert(InventoryProduct $product)
    {
        $severity = $product->current_stock <= 0 ? 'critical' : 'warning';
        $message = "Stock level for {$product->name} ({$product->sku}) is low: ".
                  number_format($product->current_stock, 2).' '.$product->unit.' remaining.';

        if ($product->current_stock <= 0) {
            $message = "OUT OF STOCK: {$product->name} ({$product->sku}) is depleted.";
        }

        $alert = StockAlert::create([
            'product_id' => $product->id,
            'severity' => $severity,
            'message' => $message,
            'is_acknowledged' => false,
        ]);

        // Optional: Send Email if configured
        if (SystemSetting::get('enable_stock_alert_emails', false)) {
            $adminEmail = SystemSetting::get('admin_notification_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new StockAlertMail($alert));
            }
        }

        return $alert;
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledge($alertId, $userId)
    {
        $alert = StockAlert::findOrFail($alertId);
        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
        ]);

        return $alert;
    }

    /**
     * Snooze an alert
     */
    public function snooze($alertId, $hours = 24)
    {
        $alert = StockAlert::findOrFail($alertId);
        $alert->update([
            'snoozed_until' => now()->addHours($hours),
        ]);

        return $alert;
    }
}
