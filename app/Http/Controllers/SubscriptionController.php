<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $tenant = app('current_tenant');
        $plans = Plan::all();

        return view('subscription.index', compact('tenant', 'plans'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = app('current_tenant');
        $tenant->update([
            'plan_id' => $request->plan_id,
        ]);

        return redirect()->back()->with('success', 'Subscription upgraded successfully!');
    }
}
