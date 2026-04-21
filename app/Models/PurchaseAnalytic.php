<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseAnalytic extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $table = 'purchase_analytics';

    protected $fillable = [
        'branch_id', 'supplier_id', 'analysis_year', 'analysis_month',
        'period_start', 'period_end', 'total_orders', 'total_purchase_amount',
        'average_order_value', 'total_quantity_ordered', 'on_time_delivery_percentage',
        'on_time_orders', 'late_orders', 'average_delivery_days', 'total_orders_received',
        'quality_acceptance_rate', 'defect_rate', 'rejected_items', 'return_count',
        'total_return_amount', 'amount_paid', 'average_payment_days',
        'payment_on_time_percentage', 'overdue_amount', 'overdue_invoices',
        'lowest_price_paid', 'highest_price_paid', 'average_price', 'gst_total',
        'supplier_performance_score', 'reliability_score', 'quality_score',
        'cost_effectiveness_score', 'month_on_month_growth', 'year_on_year_growth',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_purchase_amount' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'total_quantity_ordered' => 'decimal:4',
        'on_time_delivery_percentage' => 'decimal:2',
        'average_delivery_days' => 'decimal:2',
        'quality_acceptance_rate' => 'decimal:2',
        'defect_rate' => 'decimal:2',
        'total_return_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'average_payment_days' => 'decimal:2',
        'payment_on_time_percentage' => 'decimal:2',
        'overdue_amount' => 'decimal:2',
        'lowest_price_paid' => 'decimal:4',
        'highest_price_paid' => 'decimal:4',
        'average_price' => 'decimal:4',
        'gst_total' => 'decimal:2',
        'supplier_performance_score' => 'decimal:2',
        'reliability_score' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'cost_effectiveness_score' => 'decimal:2',
        'month_on_month_growth' => 'decimal:2',
        'year_on_year_growth' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Scopes
    public function scopeByPeriod($query, $year, $month)
    {
        return $query->where('analysis_year', $year)
            ->where('analysis_month', $month);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('analysis_year', $year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('analysis_month', $month);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeHighPerformers($query)
    {
        return $query->where('supplier_performance_score', '>=', 4);
    }

    public function scopeLowPerformers($query)
    {
        return $query->where('supplier_performance_score', '<', 2);
    }

    // Methods
    public function calculatePerformanceScore()
    {
        $reliabilityScore = ($this->on_time_delivery_percentage + $this->payment_on_time_percentage) / 2;
        $qualityScore = $this->quality_acceptance_rate;
        $costScore = 100 - (($this->highest_price_paid - $this->lowest_price_paid) / $this->highest_price_paid * 100);

        $this->reliability_score = $reliabilityScore / 20; // Convert to 0-5 scale
        $this->quality_score = $qualityScore / 20; // Convert to 0-5 scale
        $this->cost_effectiveness_score = $costScore / 20; // Convert to 0-5 scale

        $this->supplier_performance_score =
            ($this->reliability_score + $this->quality_score + $this->cost_effectiveness_score) / 3;

        $this->save();
    }

    public function getPeriodLabel()
    {
        return \Carbon\Carbon::createFromDate($this->analysis_year, $this->analysis_month, 1)
            ->format('F Y');
    }

    public function getPerformanceStatus()
    {
        if ($this->supplier_performance_score >= 4) {
            return 'Excellent';
        } elseif ($this->supplier_performance_score >= 3) {
            return 'Good';
        } elseif ($this->supplier_performance_score >= 2) {
            return 'Average';
        } else {
            return 'Poor';
        }
    }
}
