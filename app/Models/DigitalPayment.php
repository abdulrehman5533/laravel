<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DigitalPayment extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'payment_gateway', 'transaction_id', 'transaction_date', 'amount', 'status',
        'reference_type', 'reference_id', 'customer_identifier', 'gateway_response',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'gateway_response' => 'json',
    ];

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}
