<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    protected $table = 'hr_training_programs';

    protected $fillable = [
        'title',
        'description',
        'trainer_name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class, 'training_program_id');
    }
}
