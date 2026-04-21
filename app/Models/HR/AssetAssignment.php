<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    protected $table = 'hr_asset_assignments';

    protected $fillable = [
        'asset_id',
        'employee_id',
        'assigned_at',
        'returned_at',
        'condition_on_assign',
        'condition_on_return',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'returned_at' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
