<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallmentSchedule extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'installment_id', 'sequence_number', 'due_date', 'amount', 'late_fee',
        'late_fee_applied', 'status', 'paid_date', 'amount_paid',
        'payment_method', 'notes', 'reminder_sent_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'late_fee_applied' => 'boolean',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date < now()->toDateString();
    }
}
