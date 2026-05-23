<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIAgentController;

// Public health endpoint (no auth) so frontend can poll agent status
Route::prefix('ai-agent')->group(function () {
    Route::get('/health', [AIAgentController::class, 'health'])->name('ai-agent.health');
    Route::get('/status', [AIAgentController::class, 'status'])->name('ai-agent.status');
    Route::get('/analytics', [AIAgentController::class, 'analytics'])->name('ai-agent.analytics');
    Route::get('/conversations', [AIAgentController::class, 'conversations'])->name('ai-agent.conversations');
    Route::get('/history/{id}', [AIAgentController::class, 'history'])->name('ai-agent.history');
    Route::delete('/conversations/{id}', [AIAgentController::class, 'clearConversation'])->name('ai-agent.conversations.clear');
});

// Chat UI and chat processing require authentication
Route::middleware(['auth'])->prefix('ai-agent')->group(function () {
    Route::get('/chat', [AIAgentController::class, 'chat'])->name('ai-agent.chat');
    Route::post('/chat', [AIAgentController::class, 'processChat'])->name('ai-agent.process');
});
