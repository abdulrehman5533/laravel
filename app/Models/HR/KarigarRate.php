<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KarigarRate extends Model
{
    use HasFactory;

    protected $table = 'hr_karigar_rates';

    protected $fillable = [
        'employee_id',
        'item_category',
        'rate_per_gram',
        'rate_per_piece',
        'wastage_limit_percent',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
