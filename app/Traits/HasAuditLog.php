<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    public static function bootHasAuditLog()
    {
        static::created(function (Model $model) {
            $model->logAudit('created');
        });

        static::updated(function (Model $model) {
            $model->logAudit('updated');
        });

        static::deleted(function (Model $model) {
            $model->logAudit('deleted');
        });
    }

    public function logAudit(string $action, ?string $description = null)
    {
        $oldValues = $action === 'updated' ? array_intersect_key($this->getOriginal(), $this->getDirty()) : null;
        $newValues = $action === 'updated' ? $this->getDirty() : ($action === 'created' ? $this->toArray() : null);

        // Remove sensitive or large fields
        $exclude = ['password', 'remember_token', 'image_path', 'meta', 'audit_log'];
        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($exclude));
        }
        if ($newValues) {
            $newValues = array_diff_key($newValues, array_flip($exclude));
        }

        return AuditLog::create([
            'user_id' => Auth::id(), // Can be null now
            'action' => $action,
            'module' => $this->getModuleName(),
            'entity_type' => get_class($this),
            'entity_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description ?? "Model {$action}",
        ]);
    }

    protected function getModuleName(): string
    {
        $class = class_basename($this);
        if (str_contains($class, 'Pos') || str_contains($class, 'Sale')) {
            return 'Sales';
        }
        if (str_contains($class, 'Inventory') || str_contains($class, 'Product')) {
            return 'Inventory';
        }
        if (str_contains($class, 'Account') || str_contains($class, 'Ledger') || str_contains($class, 'Journal')) {
            return 'Accounting';
        }
        if (str_contains($class, 'Customer')) {
            return 'CRM';
        }
        if (str_contains($class, 'Supplier') || str_contains($class, 'Purchase')) {
            return 'Purchases';
        }

        if ($theme = str_contains($class, 'Employee') || str_contains($class, 'Payroll') || str_contains($class, 'Attendance') || str_contains($class, 'Leave') || str_contains($class, 'Shift')) {
            return 'HR';
        }

        return 'General';
    }
}
