<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class PerformanceKPI extends Model
{
    protected $table = 'hr_performance_kpis';

    protected $fillable = [
        'name',
        'description',
        'weightage',
        'target_type',
    ];
}
