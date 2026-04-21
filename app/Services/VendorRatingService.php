<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class VendorRatingService
{
    /**
     * Calculate and update the rating for a specific supplier.
     */
    public function updateSupplierRating(Supplier $supplier)
    {
        $orders = $supplier->purchaseOrders()
            ->where('status', 'Received')
            ->whereNotNull('received_at')
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $deliveryScore = $this->calculateDeliveryScore($orders);
        $qualityScore = $this->calculateQualityScore($supplier, $orders);
        
        // Final rating calculation (weighted average)
        $rating = ($deliveryScore * 0.4) + ($qualityScore * 0.6);

        $supplier->update([
            'delivery_score' => $deliveryScore,
            'quality_score' => $qualityScore,
            'rating' => round($rating / 20, 2), // Scale to 5 stars
        ]);
    }

    /**
     * Score based on punctuality of deliveries.
     */
    protected function calculateDeliveryScore($orders)
    {
        $totalOrders = $orders->count();
        $onTimeOrders = 0;

        foreach ($orders as $order) {
            if ($order->received_at && $order->expected_delivery_date) {
                if (Carbon::parse($order->received_at)->lte(Carbon::parse($order->expected_delivery_date))) {
                    $onTimeOrders++;
                }
            }
        }

        return ($onTimeOrders / $totalOrders) * 100;
    }

    /**
     * Score based on returns/rejections.
     */
    protected function calculateQualityScore(Supplier $supplier, $orders)
    {
        $totalPurchasedValue = $orders->sum('total_amount');
        $totalReturnAmount = $supplier->purchaseReturns()
            ->where('status', 'Processed')
            ->sum('refund_amount');

        if ($totalPurchasedValue == 0) return 100;

        $returnRate = ($totalReturnAmount / $totalPurchasedValue) * 100;
        
        return max(0, 100 - ($returnRate * 5)); // Penalty factor of 5 for returns
    }
}
