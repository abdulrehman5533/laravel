<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionJobItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'production_job_id', 'type', 'item_name', 'issued_qty',
        'issued_weight', 'received_qty', 'received_weight',
        'consumed_qty', 'broken_qty',
    ];

    protected $casts = [
        'issued_qty' => 'decimal:4',
        'issued_weight' => 'decimal:4',
        'received_qty' => 'decimal:4',
        'received_weight' => 'decimal:4',
        'consumed_qty' => 'decimal:4',
        'broken_qty' => 'decimal:4',
    ];

    public function productionJob(): BelongsTo
    {
        return $this->belongsTo(ProductionJob::class);
    }
}
