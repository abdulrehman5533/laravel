<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appraisal extends Model
{
    protected $table = 'hr_appraisals';

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'appraisal_date',
        'kpi_scores',
        'total_score',
        'feedback',
        'status',
    ];

    protected $casts = [
        'appraisal_date' => 'date',
        'kpi_scores' => 'json',
        'total_score' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
