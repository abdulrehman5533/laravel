<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosPricingTier extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $table = 'pos_pricing_tiers';

    protected $fillable = [
        'branch_id', 'name', 'discount_percent', 'conditions',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:3',
        'conditions' => 'array',
    ];

    public function appliesTo(array $context): bool
    {
        $conditions = $this->conditions ?? [];
        // Example: basic matching for min_qty
        if (isset($conditions['min_qty']) && isset($context['quantity'])) {
            return $context['quantity'] >= $conditions['min_qty'];
        }

        if (isset($conditions['customer_type']) && isset($context['customer_type'])) {
            return $conditions['customer_type'] === $context['customer_type'];
        }

        return true;
    }
}
