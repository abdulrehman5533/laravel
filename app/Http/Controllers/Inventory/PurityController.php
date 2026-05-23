<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PurityLevel;
use Illuminate\Http\Request;

class PurityController extends Controller
{
    public function index()
    {
        $purities = PurityLevel::withCount('products')->orderBy('karat', 'desc')->get();
        return view('inventory.settings.purities', compact('purities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:50|unique:purity_levels,name',
            'karat'       => 'required|numeric|min:0|max:24',
            'percentage'  => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        $data['is_active'] = true;
        PurityLevel::create($data);
        return redirect()->back()->with('success', 'Purity level created.');
    }

    public function update(Request $request, PurityLevel $purity)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:50|unique:purity_levels,name,'.$purity->id,
            'karat'       => 'required|numeric|min:0|max:24',
            'percentage'  => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
        $purity->update($data);
        return redirect()->back()->with('success', 'Purity updated.');
    }

    public function destroy(PurityLevel $purity)
    {
        if ($purity->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete — purity has products.');
        }
        $purity->delete();
        return redirect()->back()->with('success', 'Purity deleted.');
    }
}
