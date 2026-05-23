<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfidTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inventory_product_id',
        'rfid_tag_id',
        'tag_type',
        'is_active',
        'last_read_at',
        'location',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_read_at' => 'datetime',
    ];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    /**
     * Activate RFID tag
     */
    public function activate(): bool
    {
        $this->is_active = true;
        $this->save();

        return true;
    }

    /**
     * Deactivate RFID tag
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        $this->save();

        return true;
    }

    /**
     * Update last read time
     */
    public function updateLastReadTime(): void
    {
        $this->last_read_at = now();
        $this->save();
    }

    /**
     * Update location
     */
    public function updateLocation(string $location): void
    {
        $this->location = $location;
        $this->updateLastReadTime();
    }

    /**
     * Check if tag is active and valid
     */
    public function isValid(): bool
    {
        return $this->is_active && !empty($this->rfid_tag_id);
    }

    /**
     * Get time since last read
     */
    public function getTimeSinceLastRead(): ?string
    {
        if (!$this->last_read_at) {
            return null;
        }

        return $this->last_read_at->diffForHumans();
    }
}
