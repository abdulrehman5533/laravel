<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnalyticsInventoryReport extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'report_date', 'material_type', 'total_quantity', 'total_value', 'low_stock_quantity',
        'low_stock_products', 'out_of_stock_products', 'average_stock_value',
        'total_products', 'active_products', 'low_stock_items', 'inventory_turnover_ratio',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_quantity' => 'decimal:3',
        'total_value' => 'decimal:2',
        'low_stock_quantity' => 'decimal:3',
        'average_stock_value' => 'decimal:2',
        'inventory_turnover_ratio' => 'decimal:2',
        'low_stock_items' => 'json',
    ];

    // Scopes
    public function scopeByMaterial($query, $material)
    {
        return $query->where('material_type', $material);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('report_date', '>=', now()->subDays($days));
    }

    // Methods
    public function getStockHealthPercentage()
    {
        if ($this->total_products == 0) {
            return 0;
        }
        $goodStock = $this->total_products - $this->low_stock_products - $this->out_of_stock_products;

        return ($goodStock / $this->total_products) * 100;
    }

    public function getCriticalStockPercentage()
    {
        if ($this->total_products == 0) {
            return 0;
        }

        return (($this->low_stock_products + $this->out_of_stock_products) / $this->total_products) * 100;
    }

    public function getAverageUnitValue()
    {
        if ($this->total_quantity == 0) {
            return 0;
        }

        return $this->total_value / $this->total_quantity;
    }

    public function getStockHealthStatus()
    {
        $health = $this->getStockHealthPercentage();
        if ($health >= 80) {
            return 'Excellent';
        }
        if ($health >= 60) {
            return 'Good';
        }
        if ($health >= 40) {
            return 'Fair';
        }

        return 'Poor';
    }
}
