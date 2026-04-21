<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log a generic action.
     */
    public function log(string $action, string $module, $entity = null, ?array $oldValues = null, ?array $newValues = null, ?string $description = null)
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity ? $entity->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Get logs for a specific module or entity.
     */
    public function getLogs(?string $module = null, $entity = null)
    {
        $query = AuditLog::with('user')->latest();

        if ($module) {
            $query->where('module', $module);
        }

        if ($entity) {
            $query->where('entity_type', get_class($entity))
                ->where('entity_id', $entity->id);
        }

        return $query->get();
    }
}
