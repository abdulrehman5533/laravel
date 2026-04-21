<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategoryWastage extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'category_name',
        'metal_type',
        'max_allowed_wastage_percent',
        'max_allowed_wastage_fixed',
        'requires_admin_approval',
    ];

    protected $casts = [
        'max_allowed_wastage_percent' => 'decimal:2',
        'max_allowed_wastage_fixed' => 'decimal:3',
        'requires_admin_approval' => 'boolean',
    ];

    public function calculateWastageLimit(float $issuedWeight): float
    {
        $limit = ($issuedWeight * ($this->max_allowed_wastage_percent / 100));

        if ($this->max_allowed_wastage_fixed && $limit > $this->max_allowed_wastage_fixed) {
            return (float) $this->max_allowed_wastage_fixed;
        }

        return (float) $limit;
    }
}
