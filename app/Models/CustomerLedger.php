<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedger extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'customer_id', 'branch_id', 'credit_limit', 'grace_period_days',
        'late_fee_percentage', 'opening_balance', 'current_balance',
        'total_sales', 'transaction_count', 'last_transaction_date', 'status',
    ];

    protected $casts = [
        'last_transaction_date' => 'date',
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_sales' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class);
    }

    public function updateBalance(): void
    {
        $debits = $this->entries()->where('type', 'debit')->sum('amount');
        $credits = $this->entries()->where('type', 'credit')->sum('amount');

        $this->current_balance = $this->opening_balance + $debits - $credits;
        $this->total_sales = $this->entries()->where('reference_type', 'sale')->sum('amount');
        $this->transaction_count = $this->entries()->count();
        $this->last_transaction_date = $this->entries()->latest('date')->first()?->date;
        $this->save();
    }
}
