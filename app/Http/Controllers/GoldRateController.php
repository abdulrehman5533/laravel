<?php

namespace App\Http\Controllers;

use App\Models\GoldRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoldRateController extends Controller
{
    public function index(): View
    {
        $rates = GoldRate::latest('date')->paginate(15);
        $todayRate = GoldRate::whereDate('date', today())->first();

        return view('gold-rate.index', compact('rates', 'todayRate'));
    }

    public function create(): View
    {
        $lastRate = GoldRate::latest('date')->first();

        return view('gold-rate.create', compact('lastRate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'rate_24k' => 'required|numeric|min:0',
            'rate_22k' => 'nullable|numeric|min:0',
            'rate_21k' => 'nullable|numeric|min:0',
            'rate_20k' => 'nullable|numeric|min:0',
            'rate_18k' => 'nullable|numeric|min:0',
            'rate_14k' => 'nullable|numeric|min:0',
            'silver_rate' => 'required|numeric|min:0',
            'buy_rate_24k' => 'nullable|numeric|min:0',
            'buy_rate_22k' => 'nullable|numeric|min:0',
            'buy_silver_rate' => 'nullable|numeric|min:0',
            'comment' => 'nullable|string|max:255',
            'auto_calculate' => 'nullable|boolean',
        ]);

        $rate = new GoldRate($validated);
        $rate->created_by = Auth::id();
        $rate->status = 'draft';

        if ($request->has('auto_calculate')) {
            $rate->calculateRatesFromBase($validated['rate_24k']);
        }

        $rate->save();

        return redirect()->route('gold-rates.index')
            ->with('success', 'Gold rate created as draft. Please approve it to make it active.');
    }

    public function edit(GoldRate $goldRate): View
    {
        if ($goldRate->is_locked) {
            abort(403, 'This rate is locked and cannot be edited.');
        }

        return view('gold-rate.edit', compact('goldRate'));
    }

    public function update(Request $request, GoldRate $goldRate): RedirectResponse
    {
        if ($goldRate->is_locked) {
            return redirect()->back()->with('error', 'This rate is locked and cannot be updated.');
        }

        $validated = $request->validate([
            'rate_24k' => 'required|numeric|min:0',
            'rate_22k' => 'required|numeric|min:0',
            'rate_21k' => 'nullable|numeric|min:0',
            'rate_18k' => 'required|numeric|min:0',
            'silver_rate' => 'required|numeric|min:0',
            'comment' => 'nullable|string|max:255',
        ]);

        $goldRate->update($validated);
        $goldRate->updated_by = Auth::id();
        $goldRate->save();

        return redirect()->route('gold-rates.index')->with('success', 'Gold rate updated successfully.');
    }

    public function approve(GoldRate $goldRate): RedirectResponse
    {
        $goldRate->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'published_at' => now(),
            'is_locked' => true,
        ]);

        return redirect()->back()->with('success', 'Gold rate approved and locked for production.');
    }

    public function lock(GoldRate $goldRate): RedirectResponse
    {
        $goldRate->update(['is_locked' => true]);

        return redirect()->back()->with('success', 'Gold rate locked.');
    }

    public function unlock(GoldRate $goldRate): RedirectResponse
    {
        // Only super-admins should be able to unlock
        if (! Auth::user()->hasRole('Super Admin')) {
            abort(403, 'Only Super Admins can unlock rates.');
        }

        $goldRate->update(['is_locked' => false]);

        return redirect()->back()->with('success', 'Gold rate unlocked for editing.');
    }
}
