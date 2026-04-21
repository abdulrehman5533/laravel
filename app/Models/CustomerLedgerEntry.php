<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedgerEntry extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'customer_ledger_id', 'date', 'type', 'amount',
        'reference_type', 'reference_id', 'running_balance', 'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(CustomerLedger::class, 'customer_ledger_id');
    }
}
