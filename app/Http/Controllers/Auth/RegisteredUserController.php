<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\TenantPermissionSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $plans = Plan::all();

        return view('auth.register', compact('plans'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'shop_name' => 'required|string|max:255',
            'subdomain' => 'required|string|alpha_dash|max:255|unique:tenants,subdomain',
            'plan_id' => 'required|exists:plans,id',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create Tenant
            $tenant = Tenant::create([
                'name' => $request->shop_name,
                'subdomain' => $request->subdomain,
                'plan_id' => $request->plan_id,
                'is_active' => true,
            ]);

            // 2. Setup basic roles and permissions for this tenant using the seeder service
            $seeder = new TenantPermissionSeeder;
            $adminRole = $seeder->seed($tenant);

            // 3. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tenant_id' => $tenant->id,
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect(route('dashboard', absolute: false));
        });
    }
}
