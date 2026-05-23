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
        // Skip tenant identification for local development
        if ($request->getHost() === 'localhost' || $request->getHost() === '127.0.0.1') {
            return $next($request);
        }

        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // Find tenant by domain only (subdomain column doesn't exist)
        $tenant = Tenant::where('domain', $host)->first();

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);
            config(['app.current_tenant_id' => $tenant->id]);
        }

        return $next($request);
    }
}
