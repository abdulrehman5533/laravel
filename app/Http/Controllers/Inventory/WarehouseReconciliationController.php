<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\WarehouseReconciliation;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

class WarehouseReconciliationController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function index()
    {
        $reconciliations = WarehouseReconciliation::with(['warehouse', 'conductedBy'])
            ->latest()
            ->paginate(15);
        
        return view('inventory.reconciliation.index', compact('reconciliations'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        return view('inventory.reconciliation.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        $reconciliation = $this->warehouseService->startReconciliation(
            $request->warehouse_id,
            $request->notes
        );

        $this->warehouseService->addItemsToReconciliation($reconciliation);

        return redirect()->route('inventory.reconciliation.show', $reconciliation->id)
            ->with('success', 'Reconciliation session started.');
    }

    public function show(WarehouseReconciliation $reconciliation)
    {
        $reconciliation->load(['items.product', 'warehouse', 'conductedBy']);
        return view('inventory.reconciliation.show', compact('reconciliation'));
    }

    public function updateItem(Request $request, WarehouseReconciliation $reconciliation, $itemId)
    {
        $request->validate([
            'physical_quantity' => 'required|numeric|min:0',
            'adjustment_action' => 'required|in:none,update_stock,write_off',
            'notes' => 'nullable|string',
        ]);

        $item = $reconciliation->items()->findOrFail($itemId);
        
        $discrepancy = $request->physical_quantity - $item->system_quantity;
        
        $item->update([
            'physical_quantity' => $request->physical_quantity,
            'discrepancy' => $discrepancy,
            'adjustment_action' => $request->adjustment_action,
            'notes' => $request->notes,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function finalize(WarehouseReconciliation $reconciliation)
    {
        if ($reconciliation->status !== 'pending') {
            return back()->with('error', 'Only pending reconciliations can be finalized.');
        }

        try {
            $this->warehouseService->finalizeReconciliation($reconciliation);
            return redirect()->route('inventory.reconciliation.show', $reconciliation->id)
                ->with('success', 'Reconciliation finalized and stock updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error finalizing reconciliation: ' . $e->getMessage());
        }
    }
}
