<?php

// Add these routes to routes/api.php

Route::prefix('v1/ai-agent')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    
    // ==================== ANALYTICS ROUTES ====================
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

    // ==================== NOTIFICATION ROUTES ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::post('email', [AIAgentAPIController::class, 'sendEmail'])->name('email');
        Route::post('sms', [AIAgentAPIController::class, 'sendSMS'])->name('sms');
        Route::post('in-app', [AIAgentAPIController::class, 'sendInAppNotification'])->name('in-app');
        Route::post('whatsapp', [AIAgentAPIController::class, 'sendWhatsApp'])->name('whatsapp');
        Route::post('bulk', [AIAgentAPIController::class, 'sendBulkNotifications'])->name('bulk');
        Route::get('user', [AIAgentAPIController::class, 'getUserNotifications'])->name('user');
        Route::post('mark-read', [AIAgentAPIController::class, 'markNotificationAsRead'])->name('mark-read');
    });

    // ==================== VOICE ROUTES ====================
    Route::prefix('voice')->name('voice.')->group(function () {
        Route::post('transcribe', [AIAgentAPIController::class, 'transcribeAudio'])->name('transcribe');
        Route::post('synthesize', [AIAgentAPIController::class, 'synthesizeSpeech'])->name('synthesize');
        Route::post('detect-language', [AIAgentAPIController::class, 'detectLanguage'])->name('detect-language');
    });

    // ==================== AI AGENT ROUTES ====================
    Route::post('message', [AIAgentAPIController::class, 'processMessage'])->name('message');
});
