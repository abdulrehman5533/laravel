<?php

namespace App\Services;

use App\Models\AIAgentLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class AIAgentLoggingService
{
    /**
     * Log AI Agent activity
     */
    public static function logActivity(
        $message,
        $intent = null,
        $response = null,
        $language = 'ur',
        $processingTime = null,
        $status = 'success',
        $errorMessage = null,
        $metadata = null
    ) {
        try {
            AIAgentLog::create([
                'user_id' => Auth::id(),
                'message' => $message,
                'intent' => $intent,
                'response' => $response,
                'language' => $language,
                'processing_time' => $processingTime,
                'status' => $status,
                'error_message' => $errorMessage,
                'metadata' => $metadata
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log AI Agent activity: ' . $e->getMessage());
        }
    }

    /**
     * Log error
     */
    public static function logError($message, $exception = null, $context = [])
    {
        $errorMessage = $exception ? $exception->getMessage() : $message;
        
        Log::error('AI Agent Error: ' . $errorMessage, array_merge([
            'user_id' => Auth::id(),
            'timestamp' => now()
        ], $context));

        self::logActivity(
            $message,
            null,
            null,
            'ur',
            null,
            'error',
            $errorMessage,
            $context
        );
    }

    /**
     * Log warning
     */
    public static function logWarning($message, $context = [])
    {
        Log::warning('AI Agent Warning: ' . $message, array_merge([
            'user_id' => Auth::id(),
            'timestamp' => now()
        ], $context));
    }

    /**
     * Log info
     */
    public static function logInfo($message, $context = [])
    {
        Log::info('AI Agent Info: ' . $message, array_merge([
            'user_id' => Auth::id(),
            'timestamp' => now()
        ], $context));
    }

    /**
     * Get activity statistics
     */
    public static function getStatistics($days = 30)
    {
        $logs = AIAgentLog::where('created_at', '>=', now()->subDays($days))->get();

        return [
            'total_requests' => $logs->count(),
            'successful_requests' => $logs->where('status', 'success')->count(),
            'failed_requests' => $logs->where('status', 'error')->count(),
            'success_rate' => AIAgentLog::getSuccessRate($days),
            'avg_processing_time' => AIAgentLog::getAverageProcessingTime($days),
            'most_used_intents' => AIAgentLog::getMostUsedIntents(10, $days),
            'language_distribution' => AIAgentLog::getLanguageDistribution($days)
        ];
    }

    /**
     * Get recent errors
     */
    public static function getRecentErrors($limit = 10)
    {
        return AIAgentLog::errors()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity
     */
    public static function getUserActivity($userId, $limit = 50)
    {
        return AIAgentLog::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean old logs
     */
    public static function cleanOldLogs($days = 90)
    {
        $deleted = AIAgentLog::where('created_at', '<', now()->subDays($days))
            ->delete();

        Log::info("Cleaned {$deleted} old AI Agent logs");
        return $deleted;
    }
}
