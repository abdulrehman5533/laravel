<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Asset;
use App\Models\HR\AssetAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::with('assignments.employee')->get();

        return view('hr.assets.index', compact('assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'asset_tag' => 'required|string|unique:hr_assets,asset_tag',
            'category' => 'required|string',
            'serial_number' => 'nullable|string',
            'value' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        Asset::create($validated);

        return redirect()->back()->with('success', 'Asset created successfully.');
    }

    public function assign(Request $request, Asset $asset)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'assigned_at' => 'required|date',
            'condition_on_assign' => 'nullable|string',
        ]);

        if ($asset->status !== 'available') {
            return redirect()->back()->with('error', 'Asset is not available for assignment.');
        }

        DB::transaction(function () use ($request, $asset) {
            AssetAssignment::create([
                'asset_id' => $asset->id,
                'employee_id' => $request->employee_id,
                'assigned_at' => $request->assigned_at,
                'condition_on_assign' => $request->condition_on_assign,
                'assigned_by' => auth()->id(),
            ]);

            $asset->update(['status' => 'assigned']);
        });

        return redirect()->back()->with('success', 'Asset assigned successfully.');
    }

    public function return(Request $request, AssetAssignment $assignment)
    {
        $request->validate([
            'returned_at' => 'required|date',
            'condition_on_return' => 'nullable|string',
            'status' => 'required|in:available,under_repair,retired',
        ]);

        DB::transaction(function () use ($request, $assignment) {
            $assignment->update([
                'returned_at' => $request->returned_at,
                'condition_on_return' => $request->condition_on_return,
            ]);

            $assignment->asset->update(['status' => $request->status]);
        });

        return redirect()->back()->with('success', 'Asset returned successfully.');
    }
}
