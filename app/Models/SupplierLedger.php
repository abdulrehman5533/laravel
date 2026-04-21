<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierLedger extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'branch_id', 'opening_balance', 'current_balance',
        'opening_gross_weight', 'opening_fine_weight', 'current_gross_weight', 'current_fine_weight',
        'total_purchases', 'transaction_count', 'last_transaction_date',
        'performance_rating', 'status',
    ];

    protected $casts = [
        'last_transaction_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(SupplierLedgerEntry::class);
    }

    public function debitCreditNotes(): HasMany
    {
        return $this->hasMany(DebitCreditNote::class);
    }

    public function getOutstandingAmount(): float
    {
        return max(0, $this->current_balance);
    }
}
