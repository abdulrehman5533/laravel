<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxEntry extends Model
{
    use BelongsToTenant, SoftDeletes;
    use HasAuditLog, HasFactory;

    protected $fillable = [
        'tax_config_id', 'date', 'transaction_type', 'reference_type',
        'reference_id', 'taxable_amount', 'tax_amount', 'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function taxConfig(): BelongsTo
    {
        return $this->belongsTo(TaxConfiguration::class, 'tax_config_id');
    }
}
