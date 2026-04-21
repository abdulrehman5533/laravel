<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnalyticsFinancialReport extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'report_date', 'period', 'total_revenue', 'cost_of_goods', 'gross_profit',
        'operating_expenses', 'net_profit', 'profit_margin_percentage', 'gst_payable',
        'gst_receivable', 'total_receivables', 'total_payables', 'cash_in_hand',
        'bank_balance', 'breakdown',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_revenue' => 'decimal:2',
        'cost_of_goods' => 'decimal:2',
        'gross_profit' => 'decimal:2',
        'operating_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'profit_margin_percentage' => 'decimal:2',
        'gst_payable' => 'decimal:2',
        'gst_receivable' => 'decimal:2',
        'total_receivables' => 'decimal:2',
        'total_payables' => 'decimal:2',
        'cash_in_hand' => 'decimal:2',
        'bank_balance' => 'decimal:2',
        'breakdown' => 'json',
    ];

    // Scopes
    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    // Methods
    public function getGrossMarginPercentage()
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return ($this->gross_profit / $this->total_revenue) * 100;
    }

    public function getNetMarginPercentage()
    {
        if ($this->total_revenue == 0) {
            return 0;
        }

        return ($this->net_profit / $this->total_revenue) * 100;
    }

    public function getTotalAssets()
    {
        return $this->cash_in_hand + $this->bank_balance + $this->total_receivables;
    }

    public function getTotalLiabilities()
    {
        return $this->total_payables + $this->gst_payable;
    }

    public function getNetCash()
    {
        return ($this->cash_in_hand + $this->bank_balance) - $this->total_payables;
    }

    public function getROI()
    {
        $assets = $this->getTotalAssets();
        if ($assets == 0) {
            return 0;
        }

        return ($this->net_profit / $assets) * 100;
    }
}
