<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GirviAuction extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'girvi_id', 'auction_date', 'reserve_price',
        'final_bid_amount', 'winning_bidder_name', 'winning_bidder_contact',
        'auction_charges', 'status', 'auctioneer_notes',
        'principal_recovered', 'interest_recovered', 'surplus_amount', 'surplus_status',
    ];

    protected $casts = [
        'auction_date' => 'date',
        'reserve_price' => 'decimal:2',
        'final_bid_amount' => 'decimal:2',
        'auction_charges' => 'decimal:2',
        'principal_recovered' => 'decimal:2',
        'interest_recovered' => 'decimal:2',
        'surplus_amount' => 'decimal:2',
    ];

    public function girvi(): BelongsTo
    {
        return $this->belongsTo(Girvi::class);
    }
}
