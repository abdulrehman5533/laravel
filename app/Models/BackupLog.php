<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $fillable = ['filename', 'disk', 'size', 'status', 'type', 'error_message'];
}
