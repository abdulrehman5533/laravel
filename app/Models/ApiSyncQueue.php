<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSyncQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'device_id',
        'user_id',
        'payload',
        'status',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
