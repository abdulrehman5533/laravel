<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryProduct;
use App\Models\InventoryTransfer;
use App\Services\Inventory\BranchTransferService;
use Illuminate\Http\Request;

class InventoryTransferController extends Controller
{
    public function __construct(private BranchTransferService $transferService) {}

    public function index()
    {
        $transfers = InventoryTransfer::with(['fromBranch', 'toBranch', 'requestedBy'])
            ->latest()
            ->paginate(15);

        return view('inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $products = InventoryProduct::active()->get();

        return view('inventory.transfers.create', compact('branches', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $transfer = $this->transferService->requestTransfer(
            $validated['from_branch_id'],
            $validated['to_branch_id'],
            $validated['items'],
            $validated['notes']
        );

        return redirect()->route('inventory.transfers.show', $transfer)
            ->with('success', 'Transfer request created successfully.');
    }

    public function show(InventoryTransfer $transfer)
    {
        $transfer->load(['fromBranch', 'toBranch', 'items.product', 'requestedBy', 'approvedBy', 'dispatchedBy', 'receivedBy']);

        return view('inventory.transfers.show', compact('transfer'));
    }

    public function approve(InventoryTransfer $transfer)
    {
        try {
            $this->transferService->approveTransfer($transfer->id);

            return back()->with('success', 'Transfer approved.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function dispatch(InventoryTransfer $transfer)
    {
        try {
            $this->transferService->dispatchTransfer($transfer->id);

            return back()->with('success', 'Transfer dispatched and stock deducted from source.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receive(InventoryTransfer $transfer)
    {
        try {
            $this->transferService->receiveTransfer($transfer->id);

            return back()->with('success', 'Transfer received and stock added to destination.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
