<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PosSale extends Model
{
    use BelongsToTenant;
    use HasAuditLog, HasFactory, SoftDeletes;

    protected $table = 'pos_sales';

    protected $fillable = [
        'invoice_no',
        'invoice_type',
        'branch_id',
        'pos_customer_id',
        'created_by',
        'updated_by',
        'sale_time',
        'due_date',
        'subtotal',
        'making_charges',
        'wastage_amount',
        'discount',
        'discount_type',
        'discount_value',
        'tax_amount',
        'total',
        'outstanding_balance',
        'currency',
        'exchange_rate',
        'status',
        'payment_status',
        'payment_method',
        'metal_payment_gold',
        'metal_payment_silver',
        'metal_rate_gold',
        'metal_rate_silver',
        'notes',
        'is_wholesale',
        'is_memo',
        'memo_expiry_date',
        'consignment_status',
        'loyalty_points_earned',
        'loyalty_points_used',
        'payment_summary',
        'meta',
        'stock_moved',
        'previous_balance_cash',
        'previous_balance_gold',
        'previous_balance_silver',
        'emailed_at',
        'printed_at',
        'email_status',
        'audit_log',
        'installment_plan',
    ];

    protected $casts = [
        'sale_time' => 'datetime',
        'due_date' => 'date',
        'is_wholesale' => 'boolean',
        'stock_moved' => 'boolean',
        'payment_summary' => 'array',
        'meta' => 'array',
        'installment_plan' => 'array',
        'audit_log' => 'array',
        'subtotal' => 'decimal:2',
        'making_charges' => 'decimal:2',
        'wastage_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_used' => 'integer',
        'exchange_rate' => 'decimal:6',
        'metal_payment_gold' => 'decimal:3',
        'metal_payment_silver' => 'decimal:3',
        'metal_rate_gold' => 'decimal:2',
        'metal_rate_silver' => 'decimal:2',
        'previous_balance_cash' => 'decimal:2',
        'previous_balance_gold' => 'decimal:3',
        'previous_balance_silver' => 'decimal:3',
        'emailed_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'pos_customer_id');
    }

    public function items()
    {
        return $this->hasMany(PosSaleItem::class, 'pos_sale_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'pos_sale_id');
    }

    public function holds()
    {
        return $this->hasMany(PosHold::class, 'pos_sale_id');
    }

    public function invoices()
    {
        return $this->hasMany(PosInvoice::class, 'pos_sale_id');
    }

    public function recalculateTotals(): void
    {
        $items = $this->items;

        // Sum of all items' line totals (which already includes item-level making, wastage, tax, and item-level discount)
        $totalItemsLineTotal = $items->sum('line_total');

        $subtotal = $items->sum(function ($item) {
            return ($item->quantity * (float) $item->unit_price) + ((float) $item->weight * (float) $item->gold_rate) + (float) $item->stone_price;
        });

        $making = $items->sum('making_charge_amount');
        $wastage = $items->sum('wastage_amount');
        $tax = $items->sum('tax_amount');
        $itemDiscounts = $items->sum('discount_amount');

        // Apply sale-level discount
        $saleDiscount = 0;
        if ($this->discount_type === 'percentage' && $this->discount_value > 0) {
            $saleDiscount = ($totalItemsLineTotal * $this->discount_value) / 100;
        } elseif ($this->discount_type === 'fixed' && $this->discount_value > 0) {
            $saleDiscount = $this->discount_value;
        }

        $total = (float) $totalItemsLineTotal - (float) $saleDiscount;

        // Calculate outstanding balance - accounting for multiple currencies via exchange_rate
        $paidAmount = $this->payments()
            ->where('status', 'completed')
            ->get()
            ->sum(function ($payment) {
                return (float) $payment->amount * (float) ($payment->exchange_rate ?? 1.0);
            });

        $outstandingBalance = $total - $paidAmount;

        $this->update([
            'subtotal' => $subtotal,
            'making_charges' => $making,
            'wastage_amount' => $wastage,
            'tax_amount' => $tax,
            'discount' => $saleDiscount,
            'total' => $total,
            'outstanding_balance' => $outstandingBalance,
        ]);
    }

    /**
     * Get total gold weight for this sale
     */
    public function getTotalGoldWeightAttribute()
    {
        return $this->items()->sum('total_weight');
    }

    /**
     * Get total stone carat for this sale
     */
    public function getTotalStoneCaratAttribute()
    {
        return $this->items()->sum('stone_carat');
    }

    /**
     * Check if sale is fully paid
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->outstanding_balance <= 0;
    }

    /**
     * Get payment status
     */
    public function getPaymentStatusAttribute()
    {
        if ($this->outstanding_balance <= 0) {
            return 'paid';
        } elseif ($this->payments()->exists()) {
            return 'partial';
        } else {
            return 'unpaid';
        }
    }

    /**
     * Add audit log entry
     */
    public function addAuditLog(string $action, ?string $details = null, ?int $userId = null): void
    {
        $log = $this->audit_log ?? [];
        $log[] = [
            'timestamp' => now()->toISOString(),
            'action' => $action,
            'details' => $details,
            'user_id' => $userId ?? Auth::id(),
        ];

        $this->update(['audit_log' => $log]);
    }

    /**
     * Get branch relationship
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    /**
     * Get created by user
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get updated by user
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function returns()
    {
        return $this->hasMany(\App\Models\PosReturnRepair::class, 'pos_sale_id');
    }

    public function hold(?string $heldBy = null, ?string $note = null): PosHold
    {
        $snapshot = $this->toArray();
        $hold = $this->holds()->create([
            'held_by' => $heldBy,
            'sale_snapshot' => json_encode($snapshot),
            'note' => $note,
            'hold_reference' => 'HOLD-'.time().'-'.substr(sha1((string) $this->id), 0, 6),
        ]);

        $this->update(['status' => 'held']);

        return $hold;
    }
}
