<?php

namespace App\Services\Inventory;

use App\Models\RfidTag;
use App\Models\InventoryProduct;
use App\Models\RfidReadLog;

class RfidService
{
    public function createRfidTag(InventoryProduct $product, string $rfidTagId, string $tagType = 'uhf'): RfidTag
    {
        return RfidTag::create([
            'inventory_product_id' => $product->id,
            'rfid_tag_id' => $rfidTagId,
            'tag_type' => $tagType,
            'is_active' => true,
        ]);
    }

    public function logRfidRead(string $rfidTagId, string $readerId, ?int $signalStrength = null): RfidReadLog
    {
        $tag = RfidTag::where('rfid_tag_id', $rfidTagId)->first();
        
        $log = RfidReadLog::create([
            'rfid_tag_id' => $rfidTagId,
            'inventory_product_id' => $tag?->inventory_product_id,
            'reader_id' => $readerId,
            'read_timestamp' => now(),
            'signal_strength' => $signalStrength,
        ]);

        if ($tag) {
            $tag->update(['last_read_at' => now()]);
        }

        return $log;
    }

    public function updateTagLocation(RfidTag $tag, string $location): bool
    {
        $tag->update([
            'location' => $location,
            'last_read_at' => now(),
        ]);
        return true;
    }

    public function activateTag(RfidTag $tag): bool
    {
        $tag->update(['is_active' => true]);
        return true;
    }

    public function deactivateTag(RfidTag $tag): bool
    {
        $tag->update(['is_active' => false]);
        return true;
    }

    public function getTagReadHistory(string $rfidTagId, int $limit = 50)
    {
        return RfidReadLog::where('rfid_tag_id', $rfidTagId)
            ->orderBy('read_timestamp', 'desc')
            ->limit($limit)
            ->get();
    }
}
