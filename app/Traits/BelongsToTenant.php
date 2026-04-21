<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            if (! $model->tenant_id && app()->has('current_tenant_id')) {
                $model->tenant_id = app('current_tenant_id');
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->has('current_tenant_id')) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', app('current_tenant_id'));
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
