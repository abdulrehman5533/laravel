<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldPermission extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = ['role_id', 'model_name', 'field_name', 'permission'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
