<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // Find tenant by subdomain or domain
        // Remove 'deleted_at' check if 'tenants' table does not use soft deletes
        $tenantQuery = Tenant::where('subdomain', $subdomain)
            ->orWhere('domain', $host);

        // Only add 'whereNull("deleted_at")' if the column exists
        if (Schema::hasColumn('tenants', 'deleted_at')) {
            $tenantQuery->whereNull('deleted_at');
        }

        $tenant = $tenantQuery->first();

        // If a tenant is identified and we are at root, redirect to login for that tenant
        if ($tenant && $request->is('/')) {
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);

            return redirect()->route('login');
        }

        // Skip tenant identification for root landing page (when no tenant found), central admin and registration routes
        if ($request->is('/') || $request->is('central/*') || $request->is('register*')) {
            return $next($request);
        }

        if (! $tenant && ($host === '127.0.0.1' || $host === 'localhost')) {
            // Default tenant for local dev if not specified
            $tenant = Tenant::where('subdomain', 'admin')->first();
        }

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);

            // Also set it in config for easy access if needed
            config(['app.current_tenant_id' => $tenant->id]);
        } else {
            // If it's the main landing page, it might not have a tenant
            // But for /admin or /dashboard, it MUST have a tenant.
            if ($request->is('admin/*') || $request->is('dashboard/*') || $request->is('api/*')) {
                abort(404, 'Tenant not found.');
            }
        }

        return $next($request);
    }
}
