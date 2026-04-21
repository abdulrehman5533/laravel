<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChequeManagement extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $table = 'cheque_management';

    protected $fillable = [
        'bank_account_id', 'cheque_number', 'cheque_type', 'amount', 'cheque_date',
        'payee_name', 'payer_name', 'status', 'clearing_date', 'reference_type',
        'reference_id', 'remarks',
    ];

    protected $casts = [
        'cheque_date' => 'date',
        'clearing_date' => 'date',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function isBounced(): bool
    {
        return $this->status === 'bounced';
    }

    public function isCleared(): bool
    {
        return $this->status === 'cleared';
    }
}
