<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /** Redirect to provider */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /** Handle provider callback */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['social' => 'Social login failed.']);
        }

        if (! $socialUser || ! $socialUser->getEmail()) {
            return redirect()->route('login')->withErrors(['social' => 'Unable to retrieve email from provider.']);
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            ['name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User', 'password' => bcrypt(Str::random(16))]
        );

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
