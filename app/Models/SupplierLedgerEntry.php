<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierLedgerEntry extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'supplier_ledger_id', 'date', 'type', 'amount', 'gross_weight', 'fine_weight',
        'reference_type', 'reference_id', 'running_balance',
        'running_gross_weight', 'running_fine_weight', 'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'gross_weight' => 'decimal:4',
        'fine_weight' => 'decimal:4',
        'running_balance' => 'decimal:2',
        'running_gross_weight' => 'decimal:4',
        'running_fine_weight' => 'decimal:4',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(SupplierLedger::class, 'supplier_ledger_id');
    }
}
