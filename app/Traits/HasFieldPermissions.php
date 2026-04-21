<?php

namespace App\Traits;

trait HasFieldPermissions
{
    /**
     * Check if a field is readable by the current user.
     */
    public function isFieldReadable(string $field): bool
    {
        if (! auth()->check()) {
            return true;
        }

        return auth()->user()->getFieldPermission(static::class, $field) !== 'none';
    }

    /**
     * Check if a field is writable by the current user.
     */
    public function isFieldWritable(string $field): bool
    {
        if (! auth()->check()) {
            return true;
        }

        return auth()->user()->getFieldPermission(static::class, $field) === 'write';
    }

    /**
     * Filter array representation of the model based on field permissions.
     */
    public function toArrayWithPermissions(): array
    {
        $array = $this->toArray();
        foreach ($array as $key => $value) {
            if (! $this->isFieldReadable($key)) {
                $array[$key] = '***'; // Or unset($array[$key])
            }
        }

        return $array;
    }
}
