<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'status' => 'boolean',
        ]);

        // Check for duplicate branch by name, code, or all details
        $duplicate = Branch::where('name', $validated['name'])
            ->orWhere('code', $validated['code'])
            ->orWhere(function($q) use ($validated) {
                $q->where('address', $validated['address'] ?? null)
                  ->where('city', $validated['city'] ?? null)
                  ->where('state', $validated['state'] ?? null)
                  ->where('country', $validated['country'] ?? null)
                  ->where('phone', $validated['phone'] ?? null)
                  ->where('email', $validated['email'] ?? null);
            })
            ->first();
        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A branch with the same name, code, or details already exists. Please use unique values.');
        }

        Branch::create($validated);
        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'status' => 'boolean',
        ]);
        $branch->update($validated);
        return redirect()->route('branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully.');
    }
}
