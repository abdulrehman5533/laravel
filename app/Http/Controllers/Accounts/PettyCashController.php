<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PettyCash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PettyCashController extends Controller
{
    public function index(Request $request): View
    {
        $query = PettyCash::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $pettyCashes = $query->with('branch', 'employee')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        return view('accounts.pettycash.index', compact('pettyCashes', 'branches'));
    }

    public function create(): View
    {
        $branches = Branch::where('is_active', true)->get();

        return view('accounts.pettycash.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'nullable|exists:users,id',
            'limit' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        PettyCash::create($validated);

        return redirect()->route('accounts.pettycash.index')->with('success', 'Petty cash created');
    }

    public function show(PettyCash $pettyCash): View
    {
        $pettyCash->load('entries', 'branch', 'employee');

        return view('accounts.pettycash.show', compact('pettyCash'));
    }

    public function edit(PettyCash $pettyCash): View
    {
        $branches = Branch::where('is_active', true)->get();

        return view('accounts.pettycash.edit', compact('pettyCash', 'branches'));
    }

    public function update(Request $request, PettyCash $pettyCash): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'nullable|exists:users,id',
            'limit' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $pettyCash->update($validated);

        return redirect()->route('accounts.pettycash.show', $pettyCash)->with('success', 'Petty cash updated');
    }

    public function destroy(PettyCash $pettyCash): RedirectResponse
    {
        $pettyCash->delete();

        return redirect()->route('accounts.pettycash.index')->with('success', 'Petty cash deleted');
    }
}
