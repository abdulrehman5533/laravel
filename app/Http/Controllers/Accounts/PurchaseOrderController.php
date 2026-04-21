<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\CreditManagementService;
use App\Services\PurchaseService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    protected PurchaseService $purchaseService;

    protected CreditManagementService $creditService;

    protected WorkflowService $workflowService;

    public function __construct(PurchaseService $purchaseService, CreditManagementService $creditService, WorkflowService $workflowService)
    {
        $this->purchaseService = $purchaseService;
        $this->creditService = $creditService;
        $this->workflowService = $workflowService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $purchaseOrders = PurchaseOrder::where('branch_id', $branchId)
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->payment_status, function ($q, $status) {
                return $q->where('payment_status', $status);
            })
            ->when($request->supplier_id, function ($q, $id) {
                return $q->where('supplier_id', $id);
            })
            ->when($request->search, function ($q, $term) {
                return $q->where('po_number', 'like', "%{$term}%");
            })
            ->when($request->overdue, function ($q) {
                return $q->where('is_overdue', true);
            })
            ->with('supplier')
            ->orderByDesc('po_date')
            ->paginate(20);

        $suppliers = Supplier::where('branch_id', $branchId)->where('status', 'Active')->get();

        return view('accounts.purchases.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $branchId = Auth::user()->branch_id;
        $suppliers = Supplier::where('branch_id', $branchId)->where('status', 'Active')->get();
        $branches = Branch::where('id', $branchId)->get();

        return view('accounts.purchases.create', compact('suppliers', 'branches'));
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $branchId = Auth::user()->branch_id;
        $data = $request->validated();
        $data['branch_id'] = $branchId;
        $data['created_by'] = Auth::id();

        // Check credit availability
        if (! $this->creditService->checkCreditAvailability(
            $data['supplier_id'],
            $branchId,
            $data['total_amount'] ?? 0
        )) {
            return back()->with('error', 'Supplier credit limit exceeded');
        }

        $purchaseOrder = $this->purchaseService->createPurchaseOrder($data);

        // Start Workflow Approval process
        $this->workflowService->startWorkflow($purchaseOrder, 'Purchase');

        // Allocate credit
        $this->creditService->allocateCredit(
            $data['supplier_id'],
            $branchId,
            $purchaseOrder->total_amount
        );

        return redirect()->route('accounts.purchases.orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully and sent for approval');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items', 'payments', 'returns']);

        return view('accounts.purchases.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Draft') {
            return back()->with('error', 'Can only edit draft purchase orders');
        }

        $branchId = Auth::user()->branch_id;
        $suppliers = Supplier::where('branch_id', $branchId)->where('status', 'Active')->get();

        return view('accounts.purchases.edit', compact('purchaseOrder', 'suppliers'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'Draft') {
            return back()->with('error', 'Can only edit draft purchase orders');
        }

        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $this->purchaseService->updatePurchaseOrder($purchaseOrder, $data);

        return redirect()->route('accounts.purchases.orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully');
    }

    public function confirm(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->status = 'Confirmed';
        $purchaseOrder->save();

        return back()->with('success', 'Purchase order confirmed');
    }

    public function receive(PurchaseOrder $purchaseOrder, Request $request)
    {
        $request->validate([
            'received_quantities' => 'array',
            'received_quantities.*' => 'numeric',
        ]);

        try {
            $this->purchaseService->receivePurchaseOrder(
                $purchaseOrder,
                $request->input('received_quantities', [])
            );

            $this->purchaseService->addStockToInventory($purchaseOrder);
            $this->purchaseService->postPurchaseToGL($purchaseOrder);

            return back()->with('success', 'Purchase order received. Stock and GL entries updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to receive purchase order: '.$e->getMessage());
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['Draft', 'Confirmed', 'Received'])) {
            return back()->with('error', 'Cannot cancel this purchase order');
        }

        try {
            $purchaseOrder->status = 'Cancelled';
            $purchaseOrder->save();

            if ($purchaseOrder->status === 'Received') {
                $this->purchaseService->reverseStockFromCancelledPurchase($purchaseOrder);
            }

            $this->creditService->releaseCredit(
                $purchaseOrder->supplier_id,
                $purchaseOrder->branch_id,
                $purchaseOrder->total_amount
            );

            return back()->with('success', 'Purchase order cancelled successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel purchase order: '.$e->getMessage());
        }
    }

    public function overdue()
    {
        $branchId = Auth::user()->branch_id;
        $overdueOrders = $this->purchaseService->getOverduePurchaseOrders($branchId);

        return view('accounts.purchases.overdue', compact('overdueOrders'));
    }

    public function pendingPayments()
    {
        $branchId = Auth::user()->branch_id;
        $pendingPayments = $this->purchaseService->getPendingPayments($branchId);

        return view('accounts.purchases.pending-payments', compact('pendingPayments'));
    }

    public function recordPayment(PurchaseOrder $purchaseOrder, Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
        ]);

        try {
            $payment = $this->purchaseService->recordPayment($purchaseOrder, $validated);
            $this->purchaseService->postPaymentToGL($payment);

            return back()->with('success', 'Payment recorded and GL entries posted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    public function bulkPayment(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_ids' => 'required|array',
            'purchase_order_ids.*' => 'exists:purchase_orders,id',
            'total_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
        ]);

        try {
            $orders = PurchaseOrder::whereIn('id', $validated['purchase_order_ids'])->get();
            $amountPerOrder = $validated['total_amount'] / count($validated['purchase_order_ids']);

            foreach ($orders as $order) {
                $this->purchaseService->recordPayment($order, array_merge($validated, [
                    'amount' => $amountPerOrder,
                ]));
            }

            return back()->with('success', 'Bulk payment recorded successfully for '.count($orders).' orders');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record bulk payment: '.$e->getMessage());
        }
    }

    public function bulkStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_ids' => 'required|array',
            'purchase_order_ids.*' => 'exists:purchase_orders,id',
            'status' => 'required|in:Draft,Confirmed,Received,Cancelled',
        ]);

        try {
            PurchaseOrder::whereIn('id', $validated['purchase_order_ids'])
                ->update(['status' => $validated['status']]);

            return back()->with('success', 'Status updated successfully for '.count($validated['purchase_order_ids']).' orders');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: '.$e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $branchId = Auth::user()->branch_id;
        $format = $request->get('format', 'csv');

        $orders = PurchaseOrder::where('branch_id', $branchId)
            ->with('supplier')
            ->get();

        if ($format === 'pdf') {
            return $this->exportPdf($orders);
        }

        $csv = "PO Number,Supplier,Date,Amount,Status,Payment Status\n";

        foreach ($orders as $order) {
            $csv .= "\"{$order->po_number}\",\"{$order->supplier->name}\",\"{$order->po_date}\",";
            $csv .= "\"{$order->total_amount}\",\"{$order->status}\",\"{$order->payment_status}\"\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="purchase_orders_'.date('Y-m-d').'.csv"');
    }

    private function exportPdf($orders)
    {
        $html = '<h2>Purchase Orders Report</h2>';
        $html .= '<table border="1"><tr><th>PO Number</th><th>Supplier</th><th>Date</th><th>Amount</th><th>Status</th></tr>';

        foreach ($orders as $order) {
            $html .= "<tr><td>{$order->po_number}</td><td>{$order->supplier->name}</td>";
            $html .= "<td>{$order->po_date->format('d M Y')}</td><td>Rs.{$order->total_amount}</td>";
            $html .= "<td>{$order->status}</td></tr>";
        }

        $html .= '</table>';

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="purchase_orders_'.date('Y-m-d').'.html"');
    }
}
