<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosReturnRepair extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'pos_returns_repairs';

    protected $fillable = [
        'return_no', 'branch_id', 'customer_id', 'pos_sale_id', 'pos_sale_item_id',
        'quantity_returned', 'type', 'return_date', 'reason', 'notes',
        'refund_amount', 'refund_method', 'status', 'approved_by',
        'processed_by', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'refund_amount' => 'decimal:2',
        'quantity_returned' => 'decimal:3',
        'return_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($return) {
            if (empty($return->return_no)) {
                $lastReturn = static::latest('id')->first();
                $nextNumber = $lastReturn ? ($lastReturn->id + 1) : 1;
                $return->return_no = 'RTN-'.str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
            }
            if (empty($return->return_date)) {
                $return->return_date = now();
            }
            if (empty($return->branch_id) && auth()->check()) {
                $return->branch_id = auth()->user()->branch_id;
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function item()
    {
        return $this->belongsTo(PosSaleItem::class, 'pos_sale_item_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function processedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'processed_by');
    }
}
