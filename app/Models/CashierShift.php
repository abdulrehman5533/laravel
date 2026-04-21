<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashierShift extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'branch_id', 'cashier_id', 'shift_date', 'opened_at', 'closed_at',
        'opening_balance', 'total_cash_in', 'total_cash_out', 'closing_balance',
        'physical_count', 'variance', 'status', 'notes', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function calculateVariance(): void
    {
        if ($this->closing_balance !== null && $this->physical_count !== null) {
            $this->variance = $this->physical_count - $this->closing_balance;
        }
    }
}
