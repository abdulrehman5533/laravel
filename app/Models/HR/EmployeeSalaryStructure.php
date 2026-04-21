<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructure extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_salary_structures';

    protected $fillable = [
        'employee_id',
        'component_id',
        'amount',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }
}
