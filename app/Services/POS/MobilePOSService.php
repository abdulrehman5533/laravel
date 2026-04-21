<?php

namespace App\Services\POS;

use App\Models\ApiSyncQueue;
use App\Models\InventoryProduct;
use App\Models\PosSale;
use App\Services\POS\POSService;
use Illuminate\Support\Facades\DB;
use Exception;

class MobilePOSService
{
    public function __construct(private POSService $posService) {}

    /**
     * Process a sync batch from mobile app
     */
    public function processSyncBatch(ApiSyncQueue $syncQueue)
    {
        $payload = $syncQueue->payload;
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($payload['operations'] as $operation) {
                $results[] = $this->executeOperation($operation, $syncQueue->user_id);
            }

            $syncQueue->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            $syncQueue->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Execute individual operation from sync batch
     */
    private function executeOperation(array $operation, int $userId)
    {
        $type = $operation['type'];
        $data = $operation['data'];

        switch ($type) {
            case 'create_sale':
                return $this->posService->createSale(array_merge($data, ['created_by' => $userId]));
            
            case 'add_payment':
                $sale = PosSale::findOrFail($data['sale_id']);
                return $this->posService->recordPayment($sale, $data);

            default:
                throw new Exception("Unknown operation type: {$type}");
        }
    }

    /**
     * Quick stock check for mobile device
     */
    public function quickStockCheck(string $query)
    {
        return InventoryProduct::where('sku', $query)
            ->orWhere('barcode', $query)
            ->select('id', 'sku', 'name', 'current_stock', 'selling_price')
            ->first();
    }

    /**
     * Register a mobile device for secure API access
     */
    public function registerDevice(array $deviceData, int $userId)
    {
        // Logic to link device_id with user and generate long-lived token if needed
        return [
            'device_id' => $deviceData['device_id'],
            'registered_at' => now(),
            'status' => 'approved',
        ];
    }
}
