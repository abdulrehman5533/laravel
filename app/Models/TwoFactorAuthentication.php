<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuthentication extends Model
{
    protected $fillable = [
        'user_id',
        'secret_key',
        'backup_codes',
        'is_enabled',
        'method',
        'phone_number',
        'verified_at',
    ];

    protected $casts = [
        'backup_codes' => 'array',
        'is_enabled' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_key',
        'backup_codes',
    ];

    /**
     * Get the user that owns the 2FA settings
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if 2FA is enabled for this user
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled && $this->verified_at !== null;
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        
        $this->backup_codes = $codes;
        $this->save();
        
        return $codes;
    }

    /**
     * Use a backup code
     */
    public function useBackupCode(string $code): bool
    {
        if (!$this->backup_codes) {
            return false;
        }

        $key = array_search($code, $this->backup_codes);
        
        if ($key === false) {
            return false;
        }

        unset($this->backup_codes[$key]);
        $this->backup_codes = array_values($this->backup_codes);
        $this->save();

        return true;
    }

    /**
     * Check if backup codes are available
     */
    public function hasBackupCodes(): bool
    {
        return !empty($this->backup_codes) && count($this->backup_codes) > 0;
    }

    /**
     * Get remaining backup codes count
     */
    public function getRemainingBackupCodesCount(): int
    {
        return count($this->backup_codes ?? []);
    }
}
