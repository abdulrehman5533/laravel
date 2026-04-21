<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'customer_id', 'subtotal', 'tax_amount',
        'discount', 'total_amount', 'payment_method', 'payment_status', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public static function generateInvoiceNumber($type = 'tax')
    {
        $year = date('Y');
        $month = date('m');
        if ($type === 'tax') {
            $prefix = "INV-$year$month-";
        } else {
            $prefix = "KACHA-$year$month-";
        }
        $lastInvoice = self::where('invoice_number', 'like', "$prefix%")
            ->orderBy('id', 'desc')
            ->first();
        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -4)) + 1 : 1;

        return $prefix.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
