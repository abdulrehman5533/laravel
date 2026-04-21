<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GirviInterestPosting extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'girvi_id', 'posting_date', 'period_start', 'period_end',
        'interest_amount', 'principal_balance_at_posting',
        'is_manual', 'posted_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'interest_amount' => 'decimal:2',
        'principal_balance_at_posting' => 'decimal:2',
        'is_manual' => 'boolean',
    ];

    public function girvi(): BelongsTo
    {
        return $this->belongsTo(Girvi::class);
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
