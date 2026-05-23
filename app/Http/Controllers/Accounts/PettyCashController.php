<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PettyCash;
use App\Models\PettyCashEntry;
use Illuminate\Http\Request;

class PettyCashController extends Controller
{
    public function index(Request $request)
    {
        $query = PettyCash::with('branch');
        if ($request->branch_id) $query->where('branch_id', $request->branch_id);
        $pettyCashes = $query->paginate(20);
        $branches    = Branch::where('is_active', true)->get();
        return view('accounts.petty-cash.index', compact('pettyCashes', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('accounts.petty-cash.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'       => 'required|exists:branches,id',
            'limit'           => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
        ]);
        $data['is_active'] = true;
        PettyCash::create($data);
        return redirect()->route('accounts.petty-cash.index')->with('success', 'Petty cash fund created.');
    }

    public function show(PettyCash $pettyCash)
    {
        $pettyCash->load('branch', 'entries');
        return view('accounts.petty-cash.show', compact('pettyCash'));
    }

    public function edit(PettyCash $pettyCash)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('accounts.petty-cash.edit', compact('pettyCash', 'branches'));
    }

    public function update(Request $request, PettyCash $pettyCash)
    {
        $data = $request->validate([
            'limit'     => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $pettyCash->update($data);
        return redirect()->route('accounts.petty-cash.show', $pettyCash)->with('success', 'Updated.');
    }

    public function destroy(PettyCash $pettyCash)
    {
        $pettyCash->delete();
        return redirect()->route('accounts.petty-cash.index')->with('success', 'Deleted.');
    }
}
