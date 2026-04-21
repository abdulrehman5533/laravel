<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictPastDateEntry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip for Super Admin or Admin
        if ($user && ($user->role === 'super-admin' || $user->role === 'admin')) {
            return $next($request);
        }

        // Check for date fields in request (e.g., po_date, sale_date, girvi_date)
        $dateFields = ['date', 'po_date', 'sale_date', 'girvi_date', 'invoice_date', 'entry_date'];

        foreach ($dateFields as $field) {
            if ($request->has($field)) {
                $requestedDate = \Carbon\Carbon::parse($request->input($field));
                if (! $requestedDate->isToday()) {
                    return back()->with('error', 'Operators are restricted to current date entries only.');
                }
            }
        }

        // Restrict Edit/Delete for past records
        if (in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $route = $request->route();
            // This would need specific logic per model, but as a generic check:
            // if ($route->parameter('id')) { ... }
            // For now, let's assume the controller handles the "is_audited" or "is_past_date" check
        }

        return $next($request);
    }
}
