<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GirviReminder extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'girvi_id', 'channel', 'reminder_type', 'recipient_contact',
        'message_content', 'status', 'gateway_response', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function girvi(): BelongsTo
    {
        return $this->belongsTo(Girvi::class);
    }
}
