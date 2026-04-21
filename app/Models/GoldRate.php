<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoldRate extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'rate_24k', 'rate_22k', 'rate_21k', 'rate_20k', 'rate_18k', 'rate_14k',
        'silver_rate', 'buy_rate_24k', 'buy_rate_22k', 'buy_rate_21k', 'buy_silver_rate',
        'date', 'is_locked', 'created_by', 'updated_by', 'comment',
        'status', 'approved_by', 'approved_at', 'published_at',
    ];

    protected $casts = [
        'rate_24k' => 'decimal:2',
        'rate_22k' => 'decimal:2',
        'rate_21k' => 'decimal:2',
        'rate_20k' => 'decimal:2',
        'rate_18k' => 'decimal:2',
        'rate_14k' => 'decimal:2',
        'silver_rate' => 'decimal:2',
        'buy_rate_24k' => 'decimal:2',
        'buy_rate_22k' => 'decimal:2',
        'buy_rate_21k' => 'decimal:2',
        'buy_silver_rate' => 'decimal:2',
        'date' => 'date',
        'is_locked' => 'boolean',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public static function getTodayRate()
    {
        return self::whereDate('date', today())
            ->where('status', 'approved')
            ->latest() // Take the most recent intra-day update
            ->first() ?? self::where('status', 'approved')->latest()->first();
    }

    /**
     * Professional auto-calculation of rates based on 24K base rate
     */
    public function calculateRatesFromBase(float $base24kRate): void
    {
        $this->rate_24k = $base24kRate;
        $this->rate_22k = round($base24kRate * 0.9167, 2); // 91.67% purity
        $this->rate_21k = round($base24kRate * 0.875, 2);  // 87.5% purity
        $this->rate_18k = round($base24kRate * 0.75, 2);   // 75% purity
        $this->rate_14k = round($base24kRate * 0.5833, 2); // 58.33% purity

        // Typical buying rate is 2-5% lower than selling rate (market spread)
        $this->buy_rate_24k = round($base24kRate * 0.98, 2);
        $this->buy_rate_22k = round($this->rate_22k * 0.98, 2);
    }

    public function getRateByPurity(?string $purity, bool $isBuying = false): float
    {
        if (! $purity) {
            return $isBuying ? (float) ($this->buy_rate_24k ?: $this->rate_24k) : (float) $this->rate_24k;
        }

        $purity = strtoupper($purity);

        $rateField = 'rate_24k';
        if (str_contains($purity, '22K')) {
            $rateField = 'rate_22k';
        } elseif (str_contains($purity, '21K')) {
            $rateField = 'rate_21k';
        } elseif (str_contains($purity, '20K')) {
            $rateField = 'rate_20k';
        } elseif (str_contains($purity, '18K')) {
            $rateField = 'rate_18k';
        } elseif (str_contains($purity, '14K')) {
            $rateField = 'rate_14k';
        }

        if ($isBuying) {
            $buyField = str_replace('rate_', 'buy_rate_', $rateField);

            return (float) ($this->{$buyField} ?: $this->{$rateField} * 0.98);
        }

        return (float) $this->{$rateField};
    }
}
