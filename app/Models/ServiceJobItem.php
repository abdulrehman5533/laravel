<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceJobItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'service_job_id', 'wastage_category_id', 'ornament_name', 'metal_type', 'purity_expected', 'purity_issued',
        'purity_received', 'weight_issued', 'weight_received', 'wastage_allowed', 'wastage_limit_applied',
        'wastage_actual', 'is_excess_wastage', 'labor_charge', 'barcode', 'status', 'quality_remarks',
        'is_approved',
    ];

    protected $casts = [
        'purity_expected' => 'decimal:2',
        'purity_issued' => 'decimal:2',
        'purity_received' => 'decimal:2',
        'weight_issued' => 'decimal:3',
        'weight_received' => 'decimal:3',
        'wastage_allowed' => 'decimal:3',
        'wastage_limit_applied' => 'decimal:3',
        'wastage_actual' => 'decimal:3',
        'is_excess_wastage' => 'boolean',
        'labor_charge' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->barcode)) {
                $item->barcode = 'ORN-'.strtoupper(uniqid());
            }
        });
    }

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function wastageCategory()
    {
        return $this->belongsTo(ServiceCategoryWastage::class, 'wastage_category_id');
    }

    public function invoiceItem()
    {
        return $this->hasOne(KarigarInvoiceItem::class);
    }

    public function getPurityDifference()
    {
        if ($this->purity_expected && $this->purity_received) {
            return $this->purity_expected - $this->purity_received;
        }

        return 0;
    }

    public function getWeightLoss()
    {
        if ($this->weight_issued && $this->weight_received) {
            return $this->weight_issued - $this->weight_received;
        }

        return 0;
    }

    public function getFineWeightIssued()
    {
        return ($this->weight_issued * ($this->purity_issued ?? 100)) / 100;
    }

    public function getFineWeightReceived()
    {
        $fineWeight = ($this->weight_received * ($this->purity_received ?? 0)) / 100;

        return $fineWeight + ($this->wastage_allowed ?? 0);
    }

    /**
     * Enterprise Check: Purity Variance
     * Flags if Karigar has diluted the gold beyond acceptable tolerance (e.g., 0.10%)
     */
    public function hasPurityVariance(float $tolerance = 0.10): bool
    {
        if (! $this->purity_expected || ! $this->purity_received) {
            return false;
        }

        $variance = abs($this->purity_expected - $this->purity_received);

        return $variance > $tolerance;
    }

    public function getPurityVarianceValue(): float
    {
        return (float) ($this->purity_expected - $this->purity_received);
    }
}
