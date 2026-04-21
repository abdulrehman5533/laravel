<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\KarigarInvoice;
use App\Models\ServiceJobItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KarigarInvoiceController extends Controller
{
    public function index()
    {
        $invoices = KarigarInvoice::with('karigar')->latest()->paginate(15);

        return view('service.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $karigars = Supplier::where('supplier_type', 'Karigar')->active()->get();
        $selectedKarigar = $request->karigar_id ? Supplier::find($request->karigar_id) : null;

        $pendingItems = [];
        if ($selectedKarigar) {
            $pendingItems = ServiceJobItem::whereHas('serviceJob', function ($q) use ($selectedKarigar) {
                $q->where('karigar_id', $selectedKarigar->id);
            })->where('status', 'received')
                ->where('is_approved', true)
                ->whereDoesntHave('invoiceItem')
                ->get();
        }

        return view('service.invoices.create', compact('karigars', 'selectedKarigar', 'pendingItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karigar_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:service_job_items,id',
        ]);

        return DB::transaction(function () use ($request) {
            $karigar = Supplier::find($request->karigar_id);
            $items = ServiceJobItem::whereIn('id', $request->item_ids)->get();

            $totalWeight = $items->sum('weight_received');
            $totalLabor = $items->sum('labor_charge');
            $totalWastage = $items->sum('wastage_actual');

            $invoice = KarigarInvoice::create([
                'karigar_id' => $karigar->id,
                'invoice_date' => $request->invoice_date,
                'total_weight' => $totalWeight,
                'total_labor' => $totalLabor,
                'total_wastage_amount' => 0, // Need rate for wastage if billing in cash
                'grand_total' => $totalLabor,
                'due_amount' => $totalLabor,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                $invoice->items()->create([
                    'service_job_item_id' => $item->id,
                    'description' => $item->ornament_name,
                    'weight' => $item->weight_received,
                    'labor_rate' => 0, // Could be derived
                    'labor_amount' => $item->labor_charge,
                    'wastage_weight' => $item->wastage_actual,
                    'total_amount' => $item->labor_charge,
                ]);
            }

            // Accounting Integration: Post to Supplier Ledger
            $this->postToLedger($invoice);

            return redirect()->route('service.invoices.show', $invoice)
                ->with('success', 'Karigar Invoice generated and posted to ledger successfully');
        });
    }

    public function show(KarigarInvoice $invoice)
    {
        $invoice->load('karigar', 'items.serviceJobItem.serviceJob');

        return view('service.invoices.show', compact('invoice'));
    }

    private function postToLedger(KarigarInvoice $invoice)
    {
        // Get or create SupplierLedger for this karigar (supplier)
        $ledger = $invoice->karigar->getLedgerForBranch(auth()->user()->branch_id ?? 1);

        $newBalance = $ledger->current_balance + $invoice->grand_total;

        $ledger->entries()->create([
            'date' => $invoice->invoice_date,
            'type' => 'credit',
            'amount' => $invoice->grand_total,
            'reference_type' => 'KarigarInvoice',
            'reference_id' => $invoice->id,
            'running_balance' => $newBalance,
            'running_gross_weight' => $ledger->current_gross_weight,
            'running_fine_weight' => $ledger->current_fine_weight,
            'description' => 'Karigar Invoice #'.$invoice->invoice_number,
        ]);

        $ledger->update([
            'current_balance' => $newBalance,
            'transaction_count' => $ledger->transaction_count + 1,
            'last_transaction_date' => $invoice->invoice_date,
        ]);
    }
}
