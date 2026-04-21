<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cashbook extends Model
{
    use BelongsToTenant, SoftDeletes;
    use HasAuditLog, HasFactory;

    protected $fillable = [
        'branch_id', 'user_id', 'date', 'entry_type', 'category', 'subcategory',
        'amount', 'reference_type', 'reference_id', 'description', 'payment_method',
        'status', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
