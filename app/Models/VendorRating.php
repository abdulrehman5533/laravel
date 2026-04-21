<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorRating extends Model
{
    protected $fillable = [
        'supplier_id',
        'rating',
        'criteria_scores',
        'comments',
        'rating_date',
    ];

    protected $casts = [
        'criteria_scores' => 'json',
        'rating' => 'decimal:2',
        'rating_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
