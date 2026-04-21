<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesCommissionRate extends Model
{
    use HasFactory;

    protected $table = 'hr_sales_commission_rates';

    protected $fillable = [
        'employee_id',
        'product_category',
        'commission_percent',
        'target_amount',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
