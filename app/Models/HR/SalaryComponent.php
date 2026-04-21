<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $table = 'hr_salary_components';

    protected $fillable = [
        'name',
        'type',
        'calculation_type',
        'default_value',
        'is_taxable',
        'is_mandatory',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_mandatory' => 'boolean',
    ];
}
