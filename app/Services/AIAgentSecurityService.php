<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Exception;

class AIAgentSecurityService
{
    /**
     * Validate message input
     */
    public static function validateMessage($message)
    {
        $validator = Validator::make(['message' => $message], [
            'message' => 'required|string|min:1|max:1000'
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()
            ];
        }

        // Check for malicious content
        if (self::containsMaliciousContent($message)) {
            return [
                'valid' => false,
                'errors' => ['message' => 'Invalid message content']
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate email
     */
    public static function validateEmail($email)
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        return !$validator->fails();
    }

    /**
     * Validate phone
     */
    public static function validatePhone($phone)
    {
        $validator = Validator::make(['phone' => $phone], [
            'phone' => 'required|regex:/^\+?[0-9]{10,}$/'
        ]);

        return !$validator->fails();
    }

    /**
     * Validate language code
     */
    public static function validateLanguage($language)
    {
        $validLanguages = ['ur', 'en', 'hi', 'pa'];
        return in_array($language, $validLanguages);
    }

    /**
     * Check rate limit
     */
    public static function checkRateLimit($userId, $limit = 60, $decayMinutes = 1)
    {
        $key = "ai-agent-{$userId}";

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $seconds = RateLimiter::availableIn($key);
            return [
                'allowed' => false,
                'retry_after' => $seconds,
                'message' => "Too many requests. Please try again in {$seconds} seconds."
            ];
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return ['allowed' => true];
    }

    /**
     * Sanitize input
     */
    public static function sanitizeInput($input)
    {
        // Remove HTML tags
        $input = strip_tags($input);

        // Remove special characters that could be harmful
        $input = preg_replace('/[<>\"\'%;()&+]/', '', $input);

        // Trim whitespace
        $input = trim($input);

        return $input;
    }

    /**
     * Check for malicious content
     */
    private static function containsMaliciousContent($message)
    {
        // Check for SQL injection patterns
        $sqlPatterns = [
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b)/i',
            '/(\bOR\b|\bAND\b)\s*[\'"]?\s*[=<>]/i'
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                Log::warning('Potential SQL injection detected: ' . $message);
                return true;
            }
        }

        // Check for script injection
        if (preg_match('/<script|javascript:|onerror|onload/i', $message)) {
            Log::warning('Potential script injection detected: ' . $message);
            return true;
        }

        return false;
    }

    /**
     * Encrypt sensitive data
     */
    public static function encryptData($data)
    {
        return encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public static function decryptData($encryptedData)
    {
        try {
            return decrypt($encryptedData);
        } catch (Exception $e) {
            Log::error('Decryption error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate API token
     */
    public static function generateAPIToken($userId)
    {
        return hash('sha256', $userId . time() . env('APP_KEY'));
    }

    /**
     * Validate API token
     */
    public static function validateAPIToken($token)
    {
        // Implement your token validation logic
        return !empty($token) && strlen($token) === 64;
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = [])
    {
        Log::warning('Security Event: ' . $event, array_merge([
            'timestamp' => now(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ], $details));
    }

    /**
     * Check IP whitelist
     */
    public static function isIPWhitelisted($ip)
    {
        $whitelist = explode(',', env('AI_AGENT_IP_WHITELIST', ''));
        
        if (empty($whitelist[0])) {
            return true; // No whitelist configured
        }

        return in_array($ip, $whitelist);
    }

    /**
     * Validate request signature
     */
    public static function validateRequestSignature($payload, $signature)
    {
        $expectedSignature = hash_hmac(
            'sha256',
            $payload,
            env('AI_AGENT_SECRET_KEY', '')
        );

        return hash_equals($expectedSignature, $signature);
    }
}
