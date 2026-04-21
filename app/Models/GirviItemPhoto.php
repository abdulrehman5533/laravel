<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GirviItemPhoto extends Model
{
    protected $fillable = [
        'girvi_item_id', 'photo_path', 'photo_type', 'notes'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(GirviItem::class, 'girvi_item_id');
    }
}
