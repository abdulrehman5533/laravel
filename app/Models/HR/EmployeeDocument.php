<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $table = 'hr_employee_documents';

    protected $fillable = [
        'employee_id',
        'title',
        'document_type',
        'file_path',
        'expiry_date',
        'is_verified',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
