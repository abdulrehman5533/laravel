<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallmentPlan extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'plan_name', 'frequency', 'number_of_installments',
        'late_fee_percentage', 'grace_period_days', 'late_fee_type',
        'fixed_late_fee_amount', 'skip_allowed', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'plan_id');
    }
}
