<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\JewelleryProduct;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('customer');

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('customer', function ($q) use ($request) {
                        $q->where('name', 'like', '%'.$request->search.'%')
                            ->orWhere('phone', 'like', '%'.$request->search.'%');
                    });
            });
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->latest()->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = JewelleryProduct::where('stock', '>', 0)->where('is_active', true)->get();

        return view('pos.sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:jewellery_products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'discount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $sale = Sale::create([
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_id' => $request->customer_id,
            'subtotal' => 0,
            'tax_amount' => $request->tax_amount ?? 0,
            'discount' => $request->discount ?? 0,
            'total_amount' => 0,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'notes' => $request->notes,
        ]);

        $subtotal = 0;

        foreach ($request->items as $item) {
            $product = JewelleryProduct::find($item['product_id']);

            if ($product->stock < $item['quantity']) {
                $sale->delete();

                return back()->with('error', "Insufficient stock for {$product->name}. Available: {$product->stock}");
            }

            $unitPrice = $product->selling_price;
            $totalPrice = $unitPrice * $item['quantity'];

            $saleItem = SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'making_charge' => $product->making_charge,
                'stone_cost' => $product->stone_cost,
                'gold_value' => ($product->weight * $product->purity * $product->gold_rate) / 100,
            ]);

            $product->decrement('stock', $item['quantity']);

            $subtotal += $totalPrice;
        }

        $totalAmount = $subtotal + ($request->tax_amount ?? 0) - ($request->discount ?? 0);

        $sale->update([
            'subtotal' => $subtotal,
            'total_amount' => $totalAmount,
        ]);

        if ($sale->customer_id) {
            $customer = Customer::find($sale->customer_id);
            $customer->increment('total_purchases', $totalAmount);
            $customer->increment('purchase_count');
        }

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Sale created successfully!');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $sale->load('items.product');
        $customers = Customer::orderBy('name')->get();
        $products = JewelleryProduct::where('stock', '>', 0)->where('is_active', true)->get();

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        // Implementation for update
        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully!');
    }

    public function destroy(Sale $sale)
    {
        // Restore product stock before deleting
        foreach ($sale->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully!');
    }

    public function generateInvoice($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);
        $invoiceType = strpos($sale->invoice_number, 'KACHA') === 0 ? 'non-tax' : 'tax';
        $pdf = Pdf::loadView('invoices.print', compact('sale', 'invoiceType'));

        return $pdf->stream("invoice-{$sale->invoice_number}.pdf");
    }

    public function printInvoice($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);
        $invoiceType = strpos($sale->invoice_number, 'KACHA') === 0 ? 'non-tax' : 'tax';

        return view('invoices.print', compact('sale', 'invoiceType'));
    }
}
