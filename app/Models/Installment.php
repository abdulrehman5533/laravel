<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Installment extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'customer_id', 'sale_id', 'plan_id', 'total_amount', 'down_payment_amount',
        'installment_amount', 'total_installments', 'paid_installments',
        'skipped_installments', 'outstanding_amount', 'total_late_fees',
        'start_date', 'end_date', 'status', 'internal_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'plan_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(InstallmentSchedule::class);
    }

    // Alias for views that expect 'schedule' property
    public function getScheduleAttribute()
    {
        return $this->schedules()->get();
    }

    // Provide a payments-like collection derived from schedules (schedules store amount_paid)
    public function payments()
    {
        // Return schedules that have an amount_paid as a basis for payments
        return $this->hasMany(InstallmentSchedule::class)->whereNotNull('amount_paid');
    }

    // Backwards-compatible attribute accessors used by CRM views
    public function getNumberOfInstallmentsAttribute()
    {
        return $this->total_installments ?? ($this->plan?->number_of_installments ?? null);
    }

    public function getFrequencyAttribute()
    {
        return $this->plan?->frequency ?? null;
    }

    public function getOverdueSchedules()
    {
        return $this->schedules()
            ->where('status', 'overdue')
            ->orWhere('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get();
    }
}
