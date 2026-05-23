<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIAgentLog extends Model
{
    use SoftDeletes;

    protected $table = 'ai_agent_logs';

    protected $fillable = [
        'user_id',
        'message',
        'intent',
        'response',
        'language',
        'processing_time',
        'status',
        'error_message',
        'metadata',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'metadata' => 'json',
        'processing_time' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: by intent
     */
    public function scopeByIntent($query, $intent)
    {
        return $query->where('intent', $intent);
    }

    /**
     * Scope: by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: by language
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope: errors only
     */
    public function scopeErrors($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope: successful only
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Get average processing time
     */
    public static function getAverageProcessingTime($days = 30)
    {
        return self::where('created_at', '>=', now()->subDays($days))
            ->avg('processing_time');
    }

    /**
     * Get success rate
     */
    public static function getSuccessRate($days = 30)
    {
        $total = self::where('created_at', '>=', now()->subDays($days))->count();
        $successful = self::where('created_at', '>=', now()->subDays($days))
            ->where('status', 'success')
            ->count();

        return $total > 0 ? ($successful / $total) * 100 : 0;
    }

    /**
     * Get most used intents
     */
    public static function getMostUsedIntents($limit = 10, $days = 30)
    {
        return self::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('intent, COUNT(*) as count')
            ->groupBy('intent')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get language distribution
     */
    public static function getLanguageDistribution($days = 30)
    {
        return self::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->get();
    }
}
