<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApiService
{
    public function generateKey(string $name, ?int $tenantId = null, array $scopes = [], int $daysValid = 365)
    {
        return ApiKey::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'key' => 'zk_'.Str::random(32),
            'secret' => Str::random(64),
            'status' => 'active',
            'expires_at' => now()->addDays($daysValid),
            'scopes' => $scopes,
            'rate_limit' => 1000,
        ]);
    }

    public function rotateKey(ApiKey $apiKey)
    {
        $apiKey->update([
            'secret' => Str::random(64),
            'last_rotated_at' => now(),
        ]);

        return $apiKey;
    }

    public function isValid(string $key, string $secret, string $ip)
    {
        $apiKey = ApiKey::where('key', $key)
            ->where('secret', $secret)
            ->where('status', 'active')
            ->first();

        if (! $apiKey) {
            return false;
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return false;
        }

        if ($apiKey->ip_whitelist && ! in_array($ip, $apiKey->ip_whitelist)) {
            return false;
        }

        // Rate Limiting
        $cacheKey = 'api_rate_limit:'.$apiKey->id.':'.now()->format('Y-m-d-H');
        $requests = Cache::get($cacheKey, 0);
        if ($requests >= $apiKey->rate_limit) {
            return false;
        }
        Cache::put($cacheKey, $requests + 1, 3600);

        $apiKey->update(['last_used_at' => now()]);

        return true;
    }

    /**
     * Check if the API key has the required scope.
     */
    public function hasScope(string $key, string $requiredScope)
    {
        $apiKey = ApiKey::where('key', $key)->first();
        if (! $apiKey || ! $apiKey->scopes) {
            return false;
        }

        return in_array($requiredScope, $apiKey->scopes) || in_array('*', $apiKey->scopes);
    }

    /**
     * Log an API request.
     */
    public function logRequest(int $apiKeyId, string $endpoint, string $method, int $statusCode, float $responseTimeMs, string $ipAddress)
    {
        return \App\Models\ApiUsageLog::create([
            'api_key_id' => $apiKeyId,
            'endpoint' => $endpoint,
            'method' => $method,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Trigger a webhook.
     */
    public function triggerWebhook(string $eventType, array $payload, ?int $tenantId = null)
    {
        $webhooks = \App\Models\ApiWebhook::where('event_type', $eventType)
            ->where('is_active', true)
            ->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->get();

        foreach ($webhooks as $webhook) {
            dispatch(new \App\Jobs\ProcessWebhook($webhook, $payload));
        }
    }
}
