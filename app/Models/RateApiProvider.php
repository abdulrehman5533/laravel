<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateApiProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'api_url',
        'api_key',
        'api_secret',
        'is_active',
        'update_frequency',
        'last_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get API logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(RateApiLog::class, 'provider_id');
    }

    /**
     * Get API mappings
     */
    public function mappings(): HasMany
    {
        return $this->hasMany(RateApiMapping::class, 'provider_id');
    }

    /**
     * Get historical data
     */
    public function historicalData(): HasMany
    {
        return $this->hasMany(HistoricalRateApiData::class, 'provider_id');
    }

    /**
     * Get recent logs
     */
    public function recentLogs($limit = 50)
    {
        return $this->logs()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get last sync log
     */
    public function getLastSyncLog()
    {
        return $this->logs()
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Check if sync is due
     */
    public function isSyncDue(): bool
    {
        if (!$this->last_sync_at) {
            return true;
        }

        $minutesSinceLastSync = now()->diffInMinutes($this->last_sync_at);
        return $minutesSinceLastSync >= $this->update_frequency;
    }

    /**
     * Update last sync time
     */
    public function updateLastSyncTime(): void
    {
        $this->last_sync_at = now();
        $this->save();
    }

    /**
     * Get success rate
     */
    public function getSuccessRate(): float
    {
        $totalLogs = $this->logs()->count();
        if ($totalLogs == 0) {
            return 0;
        }

        $successLogs = $this->logs()->where('status', 'success')->count();
        return round(($successLogs / $totalLogs) * 100, 2);
    }

    /**
     * Get average execution time
     */
    public function getAverageExecutionTime(): float
    {
        return $this->logs()
            ->whereNotNull('execution_time_ms')
            ->avg('execution_time_ms') ?? 0;
    }

    /**
     * Test connection
     */
    public function testConnection(): bool
    {
        try {
            $response = \Http::timeout(10)->get($this->api_url, [
                'api_key' => $this->api_key,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Activate provider
     */
    public function activate(): bool
    {
        $this->is_active = true;
        $this->save();

        return true;
    }

    /**
     * Deactivate provider
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        $this->save();

        return true;
    }
}
