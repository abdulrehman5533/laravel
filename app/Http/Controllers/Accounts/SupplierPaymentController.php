<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\SupplierPayment;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierPaymentController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
        $this->middleware('auth');
        // Only users who can manage accounts may approve or reverse payments
        $this->middleware('can:manage-accounts')->only(['approve', 'reverse']);
    }

    public function index(Request $request)
    {
        $branchId = Auth::user()->branch_id;

        $payments = SupplierPayment::where('branch_id', $branchId)
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->payment_method, function ($q, $method) {
                return $q->where('payment_method', $method);
            })
            ->with('supplier', 'purchaseOrder')
            ->orderByDesc('payment_date')
            ->paginate(20);

        return view('accounts.purchases.payments.index', compact('payments'));
    }

    public function create()
    {
        $branchId = auth()->user()->branch_id;
        $purchaseOrders = PurchaseOrder::where('branch_id', $branchId)
            ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->with('supplier')
            ->get();

        return view('accounts.purchases.payments.create', compact('purchaseOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Bank_Transfer,Cheque,Cash,Credit_Card,Online_Payment,NEFT,RTGS,UPI',
            'cheque_number' => 'nullable|string|max:20',
            'cheque_date' => 'nullable|date',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        $payment = SupplierPayment::create([
            ...$validated,
            'branch_id' => Auth::user()->branch_id,
            'supplier_id' => $po->supplier_id,
            'payment_reference' => 'PAY-'.date('Ymd').'-'.rand(1000, 9999),
            'status' => 'Pending',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('accounts.purchases.payments.show', $payment)
            ->with('success', 'Payment recorded successfully');
    }

    public function show(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load('supplier', 'purchaseOrder');

        return view('accounts.purchases.payments.show', compact('supplierPayment'));
    }

    public function approve(SupplierPayment $payment)
    {
        $payment->approve(Auth::id());

        return back()->with('success', 'Payment approved');
    }

    public function reverse(SupplierPayment $payment)
    {
        $payment->reversePayment();

        return back()->with('success', 'Payment reversed');
    }

    public function export()
    {
        $branchId = Auth::user()->branch_id;
        $payments = SupplierPayment::where('branch_id', $branchId)->get();

        $csv = "Reference,Supplier,Date,Amount,Method,Status\n";
        foreach ($payments as $payment) {
            $csv .= "{$payment->payment_reference},{$payment->supplier->name},{$payment->payment_date},";
            $csv .= "{$payment->amount_paid},{$payment->payment_method},{$payment->status}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="supplier_payments.csv"');
    }
}
