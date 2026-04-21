<?php

namespace App\Observers;

use App\Models\GoldRate;

class GoldRateObserver
{
    /**
     * Handle the GoldRate "created" event.
     */
    public function created(GoldRate $goldRate): void
    {
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'module' => 'Rate Management',
            'entity_type' => GoldRate::class,
            'entity_id' => $goldRate->id,
            'new_values' => $goldRate->getAttributes(),
            'description' => 'New gold rates established for '.$goldRate->date->format('Y-m-d'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the GoldRate "updated" event.
     */
    public function updated(GoldRate $goldRate): void
    {
        $changes = $goldRate->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $oldValues = array_intersect_key($goldRate->getOriginal(), $changes);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'module' => 'Rate Management',
            'entity_type' => GoldRate::class,
            'entity_id' => $goldRate->id,
            'old_values' => $oldValues,
            'new_values' => $changes,
            'description' => 'Gold rates updated for '.$goldRate->date->format('Y-m-d'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the GoldRate "deleted" event.
     */
    public function deleted(GoldRate $goldRate): void
    {
        //
    }

    /**
     * Handle the GoldRate "restored" event.
     */
    public function restored(GoldRate $goldRate): void
    {
        //
    }

    /**
     * Handle the GoldRate "force deleted" event.
     */
    public function forceDeleted(GoldRate $goldRate): void
    {
        //
    }
}
