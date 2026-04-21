<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;

class CompanyContextMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // If user hasn't selected a company yet, set default to their branch's company
            if (!session()->has('current_company_id')) {
                $companyId = $user->branch?->parent_id ?? $user->branch_id;
                session(['current_company_id' => $companyId]);
            }

            // Global scope or singleton to store current company context
            $currentCompanyId = session('current_company_id');
            
            // Verify if user has access to this company
            // (Enterprise level: check user's allowed branches/companies)
            // For now, we'll just assume they have access if it's their own or parent
            
            app()->instance('current_company_id', $currentCompanyId);
        }

        return $next($request);
    }
}
