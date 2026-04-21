<?php

namespace App\Http\Requests\Auth;

use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();

        if ($user) {
            if ($user->isLocked()) {
                SecurityAuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'login_failed',
                    'category' => 'auth',
                    'subject' => $user->email,
                    'description' => 'Login attempt on locked account',
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'created_at' => now(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account is locked. Please contact administrator.',
                ]);
            }

            if ($user->isSuspended()) {
                SecurityAuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'login_failed',
                    'category' => 'auth',
                    'subject' => $user->email,
                    'description' => 'Login attempt on suspended account',
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'created_at' => now(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account has been suspended. Please contact administrator.',
                ]);
            }

            if (! $user->is_active) {
                SecurityAuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'login_failed',
                    'category' => 'auth',
                    'subject' => $user->email,
                    'description' => 'Login attempt on inactive account',
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'created_at' => now(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account is currently inactive. Please contact administrator.',
                ]);
            }
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            if ($user) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= 5) {
                    $user->update([
                        'locked_until' => now()->addMinutes(30),
                        'user_status' => 'locked',
                        'is_active' => false,
                    ]);

                    SecurityAuditLog::create([
                        'user_id' => $user->id,
                        'action' => 'account_locked',
                        'category' => 'auth',
                        'description' => 'Account automatically locked due to multiple failed login attempts',
                        'ip_address' => $this->ip(),
                        'user_agent' => $this->userAgent(),
                        'created_at' => now(),
                    ]);
                }

                SecurityAuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'login_failed',
                    'category' => 'auth',
                    'subject' => $user->email,
                    'description' => 'Login attempt failed for existing user',
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'created_at' => now(),
                ]);
            } else {
                SecurityAuditLog::create([
                    'user_id' => null,
                    'action' => 'login_failed',
                    'category' => 'auth',
                    'subject' => $this->email,
                    'description' => 'Login attempt failed for non-existent email',
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'created_at' => now(),
                ]);
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user) {
            // Session cleanup is now handled in AuthenticatedSessionController::store()

            $user->update([
                'failed_login_attempts' => 0,
                'last_login_at' => now(),
                'locked_until' => null,
            ]);

            SecurityAuditLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'category' => 'auth',
                'subject' => $user->email,
                'description' => 'User logged in successfully',
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'created_at' => now(),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
