<?php

namespace App\Http\Controllers\API;

use App\Models\ApiSyncQueue;
use App\Services\POS\MobilePOSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MobilePOSController extends BaseApiController
{
    public function __construct(private MobilePOSService $mobilePosService) {}

    /**
     * Mobile POS Dashboard/Index (Web view if used)
     */
    public function index()
    {
        return view('pos.mobile.index');
    }

    /**
     * Search products with stock check
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        $product = $this->mobilePosService->quickStockCheck($query);

        if (!$product) {
            return $this->sendError('Product not found');
        }

        return $this->sendResponse($product);
    }

    /**
     * Handle offline sync from mobile app
     */
    public function sync(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'operations' => 'required|array',
        ]);

        $syncQueue = ApiSyncQueue::create([
            'batch_id' => $request->batch_id,
            'device_id' => $request->header('X-Device-ID'),
            'user_id' => Auth::id(),
            'payload' => $request->only('operations'),
            'status' => 'pending',
        ]);

        try {
            $results = $this->mobilePosService->processSyncBatch($syncQueue);
            return $this->sendResponse($results, 'Batch processed successfully');
        } catch (\Exception $e) {
            return $this->sendError('Batch processing failed', [$e->getMessage()], 422);
        }
    }

    /**
     * Register device
     */
    public function register(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $result = $this->mobilePosService->registerDevice($request->all(), Auth::id());
        return $this->sendResponse($result, 'Device registered successfully');
    }
}
