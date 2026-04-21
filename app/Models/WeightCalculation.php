<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeightCalculation extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $table = 'weight_calculations';

    protected $fillable = [
        'user_id',
        'branch_id',
        'input_weight',
        'input_unit',
        'karat_value',
        'rate_per_gram',
        'wastage_type', // percentage or fixed
        'wastage_value',
        'making_charge_type', // per_gram, per_piece, percentage
        'making_charge_value',
        'stone_weight',
        'stone_unit',
        'stone_price_per_carat',
        'tax_percentage',
        'discount_percentage',
        'custom_charges',
        'custom_charges_description',
        'ratti_type', // sunari or pakki
        'calculation_details', // JSON
        'final_price',
        'is_saved_as_product',
        'product_id',
        'notes',
    ];

    protected $casts = [
        'calculation_details' => 'json',
        'is_saved_as_product' => 'boolean',
        'rate_per_gram' => 'decimal:2',
        'wastage_value' => 'decimal:4',
        'making_charge_value' => 'decimal:2',
        'stone_price_per_carat' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'custom_charges' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class);
    }
}
