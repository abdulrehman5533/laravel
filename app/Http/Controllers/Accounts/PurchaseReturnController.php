<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
        $this->middleware('auth');
        $this->middleware('can:manage-accounts')->only(['approve', 'processRefund']);
    }

    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $returns = PurchaseReturn::where('branch_id', $branchId)
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->supplier_id, function ($q, $id) {
                return $q->where('supplier_id', $id);
            })
            ->with('supplier', 'purchaseOrder')
            ->orderByDesc('return_date')
            ->paginate(20);

        return view('accounts.purchases.returns.index', compact('returns'));
    }

    public function create()
    {
        $branchId = auth()->user()->branch_id;
        $purchaseOrders = PurchaseOrder::where('branch_id', $branchId)
            ->where('status', 'Received')
            ->with('supplier')
            ->get();

        return view('accounts.purchases.returns.create', compact('purchaseOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'return_reason' => 'required|in:Quality_Defect,Quantity_Variance,Purity_Issue,Damaged,Not_As_Ordered,Excess_Stock,Pricing_Error,Other',
            'return_reason_details' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'gst_percentage' => 'required|numeric|between:0,28',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        $returnRecord = PurchaseReturn::create([
            ...$validated,
            'branch_id' => Auth::user()->branch_id,
            'supplier_id' => $po->supplier_id,
            'return_number' => 'RET-'.date('Ymd').'-'.rand(1000, 9999),
            'return_date' => now(),
            'created_by' => Auth::id(),
        ]);

        $returnRecord->calculateGST();

        return redirect()->route('accounts.purchases.returns.show', $returnRecord)
            ->with('success', 'Purchase return initiated');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('supplier', 'purchaseOrder');

        return view('accounts.purchases.returns.show', compact('purchaseReturn'));
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('supplier', 'purchaseOrder');

        return view('accounts.purchases.returns.edit', compact('purchaseReturn'));
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $validated = $request->validate([
            'return_reason_details' => 'nullable|string',
        ]);

        $purchaseReturn->update($validated);

        return redirect()->route('accounts.purchases.returns.show', $purchaseReturn)
            ->with('success', 'Purchase return updated');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->delete();

        return redirect()->route('accounts.purchases.returns.index')
            ->with('success', 'Purchase return deleted');
    }

    public function approve(PurchaseReturn $purchaseReturn)
    {
        try {
            $purchaseReturn->approve(Auth::id());
            $this->purchaseService->postPurchaseReturnToGL($purchaseReturn);

            return back()->with('success', 'Return approved and GL entries posted');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve return: '.$e->getMessage());
        }
    }

    public function processRefund(PurchaseReturn $purchaseReturn, Request $request)
    {
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0',
        ]);

        try {
            $purchaseReturn->processRefund($validated['refund_amount']);

            return back()->with('success', 'Refund processed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process refund: '.$e->getMessage());
        }
    }

    public function export()
    {
        $branchId = Auth::user()->branch_id;
        $returns = PurchaseReturn::where('branch_id', $branchId)->get();

        $csv = "Return Number,Supplier,Amount,Status\n";
        foreach ($returns as $return) {
            $csv .= "{$return->return_number},{$return->supplier->name},{$return->return_amount},{$return->status}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="purchase_returns.csv"');
    }
}
