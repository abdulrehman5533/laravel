<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Branch;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $branchId = $request->user()->branch_id ?? Auth::user()->branch_id;

        $suppliers = Supplier::where('branch_id', $branchId)
            ->when($request->search, function ($q, $search) {
                return $q->search($search);
            })
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->supplier_type, function ($q, $type) {
                return $q->where('supplier_type', $type);
            })
            ->with('creditLimits', 'purchaseOrders')
            ->paginate(15);

        return view('accounts.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $branches = Branch::all();

        return view('accounts.suppliers.create', compact('branches'));
    }

    public function store(StoreSupplierRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['user_id'] = Auth::id();

        $supplier = $this->supplierService->createSupplier($data);

        return redirect()->route('accounts.suppliers.show', $supplier)
            ->with('success', 'Supplier created successfully');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders' => function ($q) {
                $q->latest()->limit(10);
            },
            'payments' => function ($q) {
                $q->latest()->limit(10);
            },
            'creditLimits',
            'analytics',
        ]);

        $outstandingBalance = $this->supplierService->getOutstandingBalance($supplier);
        $totalPurchases = $this->supplierService->getTotalPurchases($supplier);
        $averageLeadTime = $this->supplierService->getAverageLeadTime($supplier);

        return view('accounts.suppliers.show', compact(
            'supplier',
            'outstandingBalance',
            'totalPurchases',
            'averageLeadTime'
        ));
    }

    public function edit(Supplier $supplier)
    {
        $branches = Branch::all();

        return view('accounts.suppliers.edit', compact('supplier', 'branches'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        $this->supplierService->updateSupplier($supplier, $data);

        return redirect()->route('accounts.suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Cannot delete supplier with active purchase orders');
        }

        $supplier->delete();

        return redirect()->route('accounts.suppliers.index')
            ->with('success', 'Supplier deleted successfully');
    }

    public function ledger(Supplier $supplier, Request $request)
    {
        $fromDate = $request->from_date ?? now()->subMonths(3);
        $toDate = $request->to_date ?? now();

        $purchases = $supplier->purchaseOrders()
            ->whereBetween('po_date', [$fromDate, $toDate])
            ->with('items')
            ->paginate(20);

        $payments = $supplier->payments()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->paginate(20);

        $totalPurchases = $supplier->purchaseOrders()
            ->whereBetween('po_date', [$fromDate, $toDate])
            ->sum('total_amount');

        $totalPayments = $supplier->payments()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('amount_paid');

        return view('accounts.suppliers.ledger', compact(
            'supplier',
            'purchases',
            'payments',
            'totalPurchases',
            'totalPayments',
            'fromDate',
            'toDate'
        ));
    }

    public function priceHistory(Supplier $supplier)
    {
        $priceHistories = $supplier->priceHistories()
            ->orderByDesc('effective_from')
            ->paginate(20);

        return view('accounts.suppliers.price-history', compact('supplier', 'priceHistories'));
    }

    public function block(Supplier $supplier)
    {
        $this->supplierService->blockSupplier($supplier);

        return back()->with('success', 'Supplier blocked successfully');
    }

    public function unblock(Supplier $supplier)
    {
        $this->supplierService->unblockSupplier($supplier);

        return back()->with('success', 'Supplier unblocked successfully');
    }

    public function suspend(Supplier $supplier, Request $request)
    {
        $days = $request->input('days', 30);
        $this->supplierService->suspendSupplier($supplier, $days);

        return back()->with('success', "Supplier suspended for {$days} days");
    }

    public function export(Request $request)
    {
        $branchId = Auth::user()->branch_id ?? $request->input('branch_id');

        $suppliers = Supplier::where('branch_id', $branchId)
            ->get();

        $csv = "Name,Email,Phone,Type,Status,Rating,Outstanding Balance\n";

        foreach ($suppliers as $supplier) {
            $balance = $supplier->purchaseOrders()->sum('amount_due');
            $csv .= "\"{$supplier->name}\",{$supplier->email},{$supplier->phone_primary},";
            $csv .= "{$supplier->supplier_type},{$supplier->status},{$supplier->rating},{$balance}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="suppliers.csv"');
    }
}
