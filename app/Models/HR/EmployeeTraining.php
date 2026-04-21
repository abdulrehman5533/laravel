<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTraining extends Model
{
    protected $table = 'hr_employee_training';

    protected $fillable = [
        'employee_id',
        'training_program_id',
        'status',
        'completion_date',
        'certificate_path',
    ];

    protected $casts = [
        'completion_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }
}
