<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JewelleryProduct extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'description', 'category', 'material',
        'weight', 'purity', 'making_charge', 'stone_weight',
        'stone_cost', 'cost_price', 'selling_price', 'gold_rate',
        'stock', 'images', 'hallmark', 'certificate_no', 'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
        'stone_weight' => 'decimal:3',
        'purity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'gold_rate' => 'decimal:2',
        'making_charge' => 'decimal:2',
        'stone_cost' => 'decimal:2',
        'is_active' => 'boolean',
        'images' => 'array',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public static function generateSku($category, $material)
    {
        $categoryCode = strtoupper(substr($category, 0, 3));
        $materialCode = strtoupper(substr($material, 0, 1));
        $count = self::where('category', $category)->where('material', $material)->count() + 1;

        return $categoryCode.$materialCode.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
