<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventorySyncController extends Controller
{
    public function levels()
    {
        return response()->json([
            'data' => [] // Stock levels by warehouse
        ]);
    }

    public function show($sku)
    {
        return response()->json([
            'sku' => $sku,
            'stock' => 0
        ]);
    }
}
