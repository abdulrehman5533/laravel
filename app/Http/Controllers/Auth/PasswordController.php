<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        // CRITICAL: Refresh the authentication session to sync the new password hash
        auth()->login($user);
        
        // Regenerate the session ID for security
        $request->session()->regenerate();
        
        // Update the current session ID in the user record
        $user->current_session_id = $request->session()->getId();
        $user->save();

        return back()->with('status', 'password-updated');
    }
}
