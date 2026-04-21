<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnalyticsSalesReport extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'report_date', 'period', 'material_type', 'total_sales', 'transaction_count',
        'average_transaction', 'total_quantity', 'gst_collected', 'discount_given',
        'net_sales', 'unique_customers', 'repeat_customers', 'vip_transactions',
        'vip_sales', 'wholesale_transactions', 'wholesale_sales', 'top_products', 'top_customers',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_sales' => 'decimal:2',
        'average_transaction' => 'decimal:2',
        'total_quantity' => 'decimal:3',
        'gst_collected' => 'decimal:2',
        'discount_given' => 'decimal:2',
        'net_sales' => 'decimal:2',
        'vip_sales' => 'decimal:2',
        'wholesale_sales' => 'decimal:2',
        'top_products' => 'json',
        'top_customers' => 'json',
    ];

    // Scopes
    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByMaterial($query, $material)
    {
        return $query->where('material_type', $material);
    }

    public function scopeAfterDate($query, $date)
    {
        return $query->where('report_date', '>=', $date);
    }

    public function scopeBeforeDate($query, $date)
    {
        return $query->where('report_date', '<=', $date);
    }

    // Methods
    public function getGrowthRate($previousPeriodSales)
    {
        if ($previousPeriodSales == 0) {
            return 0;
        }

        return (($this->total_sales - $previousPeriodSales) / $previousPeriodSales) * 100;
    }

    public function getAverageOrderValue()
    {
        if ($this->transaction_count == 0) {
            return 0;
        }

        return $this->total_sales / $this->transaction_count;
    }

    public function getVipPercentage()
    {
        if ($this->transaction_count == 0) {
            return 0;
        }

        return ($this->vip_transactions / $this->transaction_count) * 100;
    }

    public function getWholesalePercentage()
    {
        if ($this->transaction_count == 0) {
            return 0;
        }

        return ($this->wholesale_transactions / $this->transaction_count) * 100;
    }

    public function getDiscountPercentage()
    {
        if ($this->total_sales == 0) {
            return 0;
        }

        return ($this->discount_given / $this->total_sales) * 100;
    }
}
