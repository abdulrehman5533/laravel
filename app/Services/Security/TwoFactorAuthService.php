<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\TwoFactorAuthentication;
use App\Models\TwoFactorVerificationLog;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate 2FA secret for user
     */
    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();
        
        $twoFa = TwoFactorAuthentication::firstOrCreate(
            ['user_id' => $user->id],
            [
                'secret_key' => $secret,
                'method' => 'totp',
                'is_enabled' => false,
            ]
        );

        return $secret;
    }

    /**
     * Get QR code for 2FA setup
     */
    public function getQrCode(User $user, string $secret): string
    {
        $companyName = config('app.name', 'MAGIA LUPOS');
        
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secret
        );
    }

    /**
     * Verify 2FA code
     */
    public function verifyCode(User $user, string $code): bool
    {
        $twoFa = TwoFactorAuthentication::where('user_id', $user->id)->first();

        if (!$twoFa) {
            return false;
        }

        // Check if it's a backup code
        if (Str::length($code) > 6) {
            return $twoFa->useBackupCode($code);
        }

        // Verify TOTP code
        $isValid = $this->google2fa->verifyKey($twoFa->secret_key, $code);

        if ($isValid) {
            $this->logVerification($user, 'totp', 'success');
        } else {
            $this->logVerification($user, 'totp', 'failed');
        }

        return $isValid;
    }

    /**
     * Enable 2FA for user
     */
    public function enable(User $user, string $code): bool
    {
        $twoFa = TwoFactorAuthentication::where('user_id', $user->id)->first();

        if (!$twoFa) {
            return false;
        }

        // Verify the code first
        if (!$this->google2fa->verifyKey($twoFa->secret_key, $code)) {
            return false;
        }

        // Generate backup codes
        $twoFa->generateBackupCodes();
        $twoFa->is_enabled = true;
        $twoFa->verified_at = now();
        $twoFa->save();

        return true;
    }

    /**
     * Disable 2FA for user
     */
    public function disable(User $user): bool
    {
        $twoFa = TwoFactorAuthentication::where('user_id', $user->id)->first();

        if (!$twoFa) {
            return false;
        }

        $twoFa->is_enabled = false;
        $twoFa->verified_at = null;
        $twoFa->backup_codes = null;
        $twoFa->save();

        return true;
    }

    /**
     * Check if 2FA is enabled for user
     */
    public function isEnabled(User $user): bool
    {
        $twoFa = TwoFactorAuthentication::where('user_id', $user->id)->first();

        return $twoFa && $twoFa->isEnabled();
    }

    /**
     * Get 2FA settings for user
     */
    public function getSettings(User $user): ?TwoFactorAuthentication
    {
        return TwoFactorAuthentication::where('user_id', $user->id)->first();
    }

    /**
     * Log verification attempt
     */
    public function logVerification(User $user, string $method, string $status): void
    {
        TwoFactorVerificationLog::create([
            'user_id' => $user->id,
            'method' => $method,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get verification logs for user
     */
    public function getVerificationLogs(User $user, int $limit = 50)
    {
        return TwoFactorVerificationLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed attempts in last hour
     */
    public function getFailedAttemptsLastHour(User $user): int
    {
        return TwoFactorVerificationLog::where('user_id', $user->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes(User $user): array
    {
        $twoFa = TwoFactorAuthentication::where('user_id', $user->id)->first();

        if (!$twoFa) {
            return [];
        }

        return $twoFa->generateBackupCodes();
    }

    /**
     * Force 2FA for user (Admin action)
     */
    public function forceEnable(User $user): string
    {
        $secret = $this->generateSecret($user);
        
        $twoFa = TwoFactorAuthentication::where('user_id', $user->id)->first();
        $twoFa->is_enabled = true;
        $twoFa->verified_at = now();
        $twoFa->generateBackupCodes();
        $twoFa->save();

        return $secret;
    }

    /**
     * Check if user has too many failed attempts
     */
    public function isBruteForceAttempt(User $user, int $maxAttempts = 5): bool
    {
        return $this->getFailedAttemptsLastHour($user) >= $maxAttempts;
    }
}
