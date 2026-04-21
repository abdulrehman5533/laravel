<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    protected $fillable = [
        'recipient',
        'channel',
        'subject',
        'content',
        'status',
        'error_message',
    ];
}
