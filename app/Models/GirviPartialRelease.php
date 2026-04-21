<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GirviPartialRelease extends Model
{
    protected $fillable = [
        'girvi_id', 'release_date', 'amount_paid', 'principal_component',
        'interest_component', 'items_released', 'released_by', 'notes'
    ];

    protected $casts = [
        'release_date' => 'date',
        'items_released' => 'array',
        'amount_paid' => 'decimal:2',
        'principal_component' => 'decimal:2',
        'interest_component' => 'decimal:2',
    ];

    public function girvi(): BelongsTo
    {
        return $this->belongsTo(Girvi::class);
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
