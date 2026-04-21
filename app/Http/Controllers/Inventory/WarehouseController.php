<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function index()
    {
        $warehouses = Warehouse::with(['bins', 'branch'])->get();

        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        return view('inventory.warehouses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'capacity_volume' => 'nullable|numeric|min:0',
            'rfid_enabled' => 'boolean',
            'iot_sensor_id' => 'nullable|string|max:255',
        ]);

        $warehouse = Warehouse::create($validated);

        return redirect()->route('inventory.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $iotStatus = $this->warehouseService->monitorIotSensors($warehouse);

        return view('inventory.warehouses.show', compact('warehouse', 'iotStatus'));
    }

    public function storeBin(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'bin_code' => 'required|string|max:20',
            'bin_type' => 'required|string|in:shelf,vault,drawer,pallet',
        ]);

        $warehouse->bins()->create($validated);

        return redirect()->route('inventory.warehouses.show', $warehouse)
            ->with('success', 'Storage bin added successfully.');
    }

    public function trackLogistics(Request $request)
    {
        $tracking = $this->warehouseService->trackShipment($request->tracking_number);

        return response()->json($tracking);
    }
}
