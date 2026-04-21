<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'api_key_id',
        'endpoint',
        'method',
        'status_code',
        'response_time_ms',
        'ip_address',
    ];

    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }
}
