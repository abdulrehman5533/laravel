<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MobilePosController;
use App\Http\Controllers\Api\V1\InventorySyncController;
use App\Http\Controllers\API\HR\AttendanceApiController;

/*
|--------------------------------------------------------------------------
| API Routes - V1 (Production)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // User Context
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Mobile POS
    Route::prefix('pos')->group(function () {
        Route::post('/sync', [MobilePosController::class, 'sync']);
        Route::get('/catalog', [MobilePosController::class, 'getCatalog']);
        Route::post('/sale', [MobilePosController::class, 'processSale']);
    });

    // Inventory
    Route::prefix('inventory')->group(function () {
        Route::get('/stock-levels', [InventorySyncController::class, 'levels']);
        Route::get('/products/{sku}', [InventorySyncController::class, 'show']);
    });

    // HR & Attendance
    Route::prefix('hr')->group(function () {
        Route::post('/attendance/mark', [AttendanceApiController::class, 'mark']);
        Route::post('/attendance/break', [AttendanceApiController::class, 'toggleBreak']);
        Route::get('/attendance/status', [AttendanceApiController::class, 'status']);
        Route::get('/attendance/history', [AttendanceApiController::class, 'history']);
        // New production-level attendance endpoints
        Route::post('/attendance/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut']);
        Route::post('/attendance/manual-override', [\App\Http\Controllers\AttendanceController::class, 'manualOverride']);
        Route::get('/attendance/today-map', [\App\Http\Controllers\AttendanceController::class, 'todayMap']);
    });
});

// Session Management Routes (outside auth:sanctum for heartbeat)
Route::middleware(['web'])->group(function () {
    Route::post('/api/heartbeat', function (Request $request) {
        if (!auth()->check()) {
            return response()->json(['status' => 'expired', 'reason' => 'not_authenticated'], 401);
        }
        
        $request->session()->put('last_heartbeat_time', time());
        return response()->json(['status' => 'active']);
    })->name('api.heartbeat');
    
    Route::post('/api/session-logout', function (Request $request) {
        if (auth()->check()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return response()->json(['status' => 'logged_out']);
    })->name('api.session-logout');
    
    Route::get('/api/gold-rate', function () {
        $rate = \App\Models\GoldRate::latest()->first();
        return response()->json([
            'rate_22k' => $rate->rate_22k ?? 6000,
            'rate_24k' => $rate->rate_24k ?? 6500,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| API Routes - V2 (Beta/Development)
|--------------------------------------------------------------------------
*/
Route::prefix('v2')->middleware(['auth:sanctum'])->group(function () {
    // Future expansion
});
