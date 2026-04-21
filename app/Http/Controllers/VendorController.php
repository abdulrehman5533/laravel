<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%')
                    ->orWhere('vendor_code', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('material') && $request->material) {
            $query->where('material_speciality', $request->material);
        }

        $vendors = $query->latest()->paginate(20);

        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:vendors,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'material_speciality' => 'nullable|string',
        ]);

        $validated['vendor_code'] = Vendor::generateVendorCode();
        $validated['is_active'] = true;

        Vendor::create($validated);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor added successfully!');
    }

    public function show(Vendor $vendor)
    {
        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:vendors,email,'.$vendor->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'material_speciality' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully!');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully!');
    }
}
