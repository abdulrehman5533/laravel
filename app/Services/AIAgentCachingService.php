<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class AIAgentCachingService
{
    const CACHE_PREFIX = 'ai_agent_';
    const DEFAULT_TTL = 3600; // 1 hour

    /**
     * Get cached data
     */
    public static function get($key, $default = null)
    {
        try {
            return Cache::get(self::CACHE_PREFIX . $key, $default);
        } catch (Exception $e) {
            Log::error('Cache get error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Set cached data
     */
    public static function put($key, $value, $ttl = self::DEFAULT_TTL)
    {
        try {
            Cache::put(self::CACHE_PREFIX . $key, $value, $ttl);
            return true;
        } catch (Exception $e) {
            Log::error('Cache put error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remember cached data
     */
    public static function remember($key, $ttl = self::DEFAULT_TTL, $callback)
    {
        try {
            return Cache::remember(
                self::CACHE_PREFIX . $key,
                $ttl,
                $callback
            );
        } catch (Exception $e) {
            Log::error('Cache remember error: ' . $e->getMessage());
            return $callback();
        }
    }

    /**
     * Forget cached data
     */
    public static function forget($key)
    {
        try {
            Cache::forget(self::CACHE_PREFIX . $key);
            return true;
        } catch (Exception $e) {
            Log::error('Cache forget error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Flush all AI Agent cache
     */
    public static function flush()
    {
        try {
            // Get all keys and delete those with our prefix
            $keys = Cache::getStore()->getPrefix() . self::CACHE_PREFIX . '*';
            Cache::flush();
            return true;
        } catch (Exception $e) {
            Log::error('Cache flush error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache analytics data
     */
    public static function cacheAnalytics($type, $data, $ttl = 1800) // 30 minutes
    {
        return self::put("analytics_{$type}", $data, $ttl);
    }

    /**
     * Get cached analytics
     */
    public static function getAnalytics($type)
    {
        return self::get("analytics_{$type}");
    }

    /**
     * Cache user notifications
     */
    public static function cacheUserNotifications($userId, $notifications, $ttl = 600) // 10 minutes
    {
        return self::put("notifications_user_{$userId}", $notifications, $ttl);
    }

    /**
     * Get cached user notifications
     */
    public static function getUserNotifications($userId)
    {
        return self::get("notifications_user_{$userId}");
    }

    /**
     * Cache intent detection result
     */
    public static function cacheIntentDetection($message, $intent, $ttl = 3600)
    {
        $key = 'intent_' . md5($message);
        return self::put($key, $intent, $ttl);
    }

    /**
     * Get cached intent detection
     */
    public static function getIntentDetection($message)
    {
        $key = 'intent_' . md5($message);
        return self::get($key);
    }

    /**
     * Cache voice transcription
     */
    public static function cacheTranscription($audioHash, $transcript, $ttl = 7200) // 2 hours
    {
        return self::put("transcription_{$audioHash}", $transcript, $ttl);
    }

    /**
     * Get cached transcription
     */
    public static function getTranscription($audioHash)
    {
        return self::get("transcription_{$audioHash}");
    }

    /**
     * Cache API response
     */
    public static function cacheAPIResponse($endpoint, $response, $ttl = 1800)
    {
        $key = 'api_' . md5($endpoint);
        return self::put($key, $response, $ttl);
    }

    /**
     * Get cached API response
     */
    public static function getAPIResponse($endpoint)
    {
        $key = 'api_' . md5($endpoint);
        return self::get($key);
    }

    /**
     * Get cache statistics
     */
    public static function getStatistics()
    {
        try {
            $store = Cache::getStore();
            
            return [
                'driver' => config('cache.default'),
                'prefix' => $store->getPrefix(),
                'status' => 'active'
            ];
        } catch (Exception $e) {
            Log::error('Cache statistics error: ' . $e->getMessage());
            return ['status' => 'error'];
        }
    }

    /**
     * Warm up cache
     */
    public static function warmUp()
    {
        try {
            // Cache frequently accessed data
            Log::info('Warming up AI Agent cache...');
            
            // Add your cache warming logic here
            
            Log::info('Cache warm up completed');
            return true;
        } catch (Exception $e) {
            Log::error('Cache warm up error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Monitor cache performance
     */
    public static function monitorPerformance()
    {
        try {
            $stats = self::getStatistics();
            
            Log::info('Cache Performance Monitor', [
                'driver' => $stats['driver'] ?? 'unknown',
                'timestamp' => now()
            ]);

            return $stats;
        } catch (Exception $e) {
            Log::error('Cache monitoring error: ' . $e->getMessage());
            return null;
        }
    }
}
