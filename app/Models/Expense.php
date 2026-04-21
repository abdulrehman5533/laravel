<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'branch_id', 'category_id', 'subcategory_id', 'date', 'vendor_name',
        'amount', 'payment_method', 'status', 'description', 'monthly_limit',
        'is_recurring', 'recurrence_type', 'recurrence_start_date', 'recurrence_end_date',
        'created_by', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'recurrence_start_date' => 'date',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseSubcategory::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ExpenseAttachment::class);
    }
}
