<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Branch;
use App\Services\AssetService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function index()
    {
        $assets = Asset::with('branch')->get();

        return view('assets.index', compact('assets'));
    }

    public function create()
    {
        $branches = Branch::all();

        return view('assets.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|unique:assets',
            'name' => 'required',
            'branch_id' => 'required|exists:branches,id',
            'category' => 'required',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['current_value'] = $request->purchase_cost;

        $asset = Asset::create($data);

        // Predictive logic
        $asset->next_maintenance_date = $this->assetService->predictNextMaintenance($asset);
        $asset->save();

        return redirect()->route('assets.index')->with('success', 'Asset recorded with predictive maintenance schedule.');
    }

    public function show(Asset $asset)
    {
        return view('assets.show', compact('asset'));
    }

    public function calculateDepreciation(Asset $asset)
    {
        $asset->current_value = $this->assetService->calculateDepreciation($asset);
        $asset->save();

        return redirect()->back()->with('success', 'Enterprise depreciation calculated successfully.');
    }

    public function scheduleMaintenance(Asset $asset)
    {
        $asset->next_maintenance_date = $this->assetService->predictNextMaintenance($asset);
        $asset->save();

        return redirect()->back()->with('success', 'Maintenance scheduled via AI predictor.');
    }

    public function dispose(Request $request, Asset $asset)
    {
        $request->validate([
            'disposal_date' => 'required|date',
            'disposal_amount' => 'required|numeric|min:0',
            'disposal_reason' => 'required|string',
        ]);

        $asset->update([
            'status' => 'disposed',
            'disposal_date' => $request->disposal_date,
            'disposal_amount' => $request->disposal_amount,
            'disposal_reason' => $request->disposal_reason,
            'current_value' => 0,
        ]);

        return redirect()->route('assets.index')->with('success', 'Asset disposed successfully.');
    }
}
