<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'trigger_event',
        'subject',
        'body',
        'channels',
        'language',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'channels' => 'json',
    ];
}
