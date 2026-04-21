<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'notification_history';

    protected $fillable = ['user_id', 'channel', 'event_type', 'status', 'error_message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
