<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;

class CentralDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('is_active', true)->count(),
            'total_plans' => Plan::count(),
            'total_users' => User::count(),
            'estimated_mrr' => Tenant::where('is_active', true)
                ->join('plans', 'tenants.plan_id', '=', 'plans.id')
                ->sum('plans.price'),
            'plan_distribution' => Plan::withCount('tenants')->get(),
            'recent_tenants' => Tenant::with('plan')->latest()->take(5)->get(),
        ];

        return view('central.dashboard', $stats);
    }

    public function tenants()
    {
        $tenants = Tenant::with('plan')->paginate(10);

        return view('central.tenants.index', compact('tenants'));
    }

    public function plans()
    {
        $plans = Plan::all();

        return view('central.plans.index', compact('plans'));
    }
}
