<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurityOverrideLog extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'purity_loggable_type',
        'purity_loggable_id',
        'old_purity',
        'new_purity',
        'reason',
        'user_id',
        'ip_address',
    ];

    public function purityLoggable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logOverride($model, $oldPurity, $newPurity, $reason)
    {
        return self::create([
            'purity_loggable_type' => get_class($model),
            'purity_loggable_id' => $model->id,
            'old_purity' => $oldPurity,
            'new_purity' => $newPurity,
            'reason' => $reason,
            'user_id' => auth()->id() ?? 1,
            'ip_address' => request()->ip(),
        ]);
    }
}
