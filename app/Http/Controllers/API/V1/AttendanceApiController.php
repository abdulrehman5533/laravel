<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceApiController extends Controller
{
    public function mark(Request $request)
    {
        $request->validate([
            'lat' => 'required',
            'lng' => 'required',
            'type' => 'required|in:check_in,check_out'
        ]);

        // Geofencing and marking logic
        return response()->json([
            'status' => 'success',
            'time' => now()
        ]);
    }

    public function history()
    {
        return response()->json([
            'history' => []
        ]);
    }
}
