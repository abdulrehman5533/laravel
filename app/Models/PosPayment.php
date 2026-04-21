<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosPayment extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $table = 'pos_payments';

    protected $fillable = [
        'pos_sale_id', 'pos_customer_id', 'payment_method', 'amount', 'metal_type', 'metal_weight', 'metal_rate', 'is_bhav_cut', 'currency', 'exchange_rate', 'bank_name', 'cheque_number', 'transaction_id', 'reference', 'notes', 'status', 'recorded_by', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
        'metal_weight' => 'decimal:3',
        'metal_rate' => 'decimal:2',
        'is_bhav_cut' => 'boolean',
        'exchange_rate' => 'decimal:6',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'pos_customer_id');
    }

    public function recordedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
