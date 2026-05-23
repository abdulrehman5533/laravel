<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MobilePosController;
use App\Http\Controllers\Api\V1\InventorySyncController;
use App\Http\Controllers\API\HR\AttendanceApiController;
use App\Http\Controllers\API\AIAgentAPIController;

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
        Route::post('/attendance/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut']);
        Route::post('/attendance/manual-override', [\App\Http\Controllers\AttendanceController::class, 'manualOverride']);
        Route::get('/attendance/today-map', [\App\Http\Controllers\AttendanceController::class, 'todayMap']);
    });

    // ==================== AI AGENT ROUTES ====================
    Route::prefix('ai-agent')->name('ai-agent.')->group(function () {
        // Analytics Routes
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('sales', [AIAgentAPIController::class, 'getSalesAnalytics'])->name('sales');
            Route::get('inventory', [AIAgentAPIController::class, 'getInventoryAnalytics'])->name('inventory');
            Route::get('customers', [AIAgentAPIController::class, 'getCustomerAnalytics'])->name('customers');
            Route::get('employees', [AIAgentAPIController::class, 'getEmployeeAnalytics'])->name('employees');
            Route::get('purchases', [AIAgentAPIController::class, 'getPurchaseAnalytics'])->name('purchases');
            Route::get('services', [AIAgentAPIController::class, 'getServiceAnalytics'])->name('services');
            Route::get('financial', [AIAgentAPIController::class, 'getFinancialAnalytics'])->name('financial');
            Route::get('dashboard', [AIAgentAPIController::class, 'getDashboardSummary'])->name('dashboard');
            Route::get('trends', [AIAgentAPIController::class, 'getTrends'])->name('trends');
        });

        // Notification Routes
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::post('email', [AIAgentAPIController::class, 'sendEmail'])->name('email');
            Route::post('sms', [AIAgentAPIController::class, 'sendSMS'])->name('sms');
            Route::post('in-app', [AIAgentAPIController::class, 'sendInAppNotification'])->name('in-app');
            Route::post('whatsapp', [AIAgentAPIController::class, 'sendWhatsApp'])->name('whatsapp');
            Route::post('bulk', [AIAgentAPIController::class, 'sendBulkNotifications'])->name('bulk');
            Route::get('user', [AIAgentAPIController::class, 'getUserNotifications'])->name('user');
            Route::post('mark-read', [AIAgentAPIController::class, 'markNotificationAsRead'])->name('mark-read');
        });

        // Voice Routes
        Route::prefix('voice')->name('voice.')->group(function () {
            Route::post('transcribe', [AIAgentAPIController::class, 'transcribeAudio'])->name('transcribe');
            Route::post('synthesize', [AIAgentAPIController::class, 'synthesizeSpeech'])->name('synthesize');
            Route::post('detect-language', [AIAgentAPIController::class, 'detectLanguage'])->name('detect-language');
        });

        // AI Agent Routes
        Route::post('message', [AIAgentAPIController::class, 'processMessage'])->name('message');
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


// AI Agent API Routes (Public)
Route::prefix('ai-agent')->group(function () {
    Route::get('/analytics', [AIAgentApiController::class, 'analytics']);
    Route::post('/upload', [AIAgentApiController::class, 'uploadFile']);
    Route::get('/today-sales', [AIAgentApiController::class, 'todaySales']);
});
