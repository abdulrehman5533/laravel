<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ApiWebhook extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'url',
        'event_type',
        'secret',
        'is_active',
        'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
