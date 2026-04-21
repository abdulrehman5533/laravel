<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobilePosController extends Controller
{
    public function sync(Request $request)
    {
        // Logic for offline-first sync queue
        Log::info('Mobile POS sync received', ['user_id' => auth()->id()]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Sync completed',
            'timestamp' => now()
        ]);
    }

    public function getCatalog()
    {
        // Return active catalog for mobile POS
        return response()->json([
            'products' => [], // Add product logic here
            'categories' => []
        ]);
    }

    public function processSale(Request $request)
    {
        // Process sale from mobile device
        return response()->json([
            'status' => 'success',
            'invoice_no' => 'M-'.now()->timestamp
        ]);
    }
}
