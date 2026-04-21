<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementRule extends Model
{
    protected $fillable = ['name', 'type', 'conditions', 'is_active'];

    protected $casts = [
        'conditions' => 'json',
        'is_active' => 'boolean',
    ];
}
