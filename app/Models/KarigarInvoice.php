<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KarigarInvoice extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'karigar_id', 'invoice_date', 'total_weight',
        'total_labor', 'total_wastage_amount', 'tax_amount', 'grand_total',
        'paid_amount', 'due_amount', 'status', 'payment_status', 'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_weight' => 'decimal:3',
        'total_labor' => 'decimal:2',
        'total_wastage_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $lastInvoice = static::latest('id')->first();
                $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;
                $invoice->invoice_number = 'KINV-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function karigar()
    {
        return $this->belongsTo(Supplier::class, 'karigar_id');
    }

    public function items()
    {
        return $this->hasMany(KarigarInvoiceItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
