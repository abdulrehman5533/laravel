<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreReturnRequest;
use App\Models\PosReturnRepair;
use App\Models\PosSale;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReturnController extends Controller
{
    /**
     * List returns and repairs
     */
    public function index(): View
    {
        $returns = PosReturnRepair::with(['sale', 'item'])
            ->latest()
            ->paginate(20);

        return view('pos.returns.index', compact('returns'));
    }

    /**
     * Create return/repair entry
     */
    public function create(): View
    {
        $sales = PosSale::with('customer')
            ->where('status', 'completed')
            ->latest()
            ->limit(100)
            ->get();

        return view('pos.returns.create', compact('sales'));
    }

    /**
     * Get items for a specific sale via AJAX
     */
    public function getSaleItems(PosSale $sale)
    {
        $items = $sale->items()->with('product')->get();

        return response()->json([
            'items' => $items,
            'customer' => $sale->customer,
            'total' => $sale->total,
            'currency' => $sale->currency ?? 'Rs',
        ]);
    }

    /**
     * Store return/repair
     */
    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $sale = PosSale::findOrFail($request->pos_sale_id);

        $data = $request->validated();
        $data['customer_id'] = $sale->pos_customer_id;
        $data['branch_id'] = $sale->branch_id;
        $data['processed_by'] = auth()->id();
        $data['status'] = 'initiated';

        $return = PosReturnRepair::create($data);

        // If it's a direct return, we might want to process it immediately
        // depending on business rules. For now, we'll just redirect to show.

        return redirect()->route('pos.returns.show', $return)
            ->with('success', 'Return/repair entry created successfully');
    }

    /**
     * Show return details
     */
    public function show(PosReturnRepair $return): View
    {
        $return->load(['sale', 'item']);

        return view('pos.returns.show', compact('return'));
    }

    /**
     * Edit return
     */
    public function edit(PosReturnRepair $return): View
    {
        return view('pos.returns.edit', compact('return'));
    }

    /**
     * Update return
     */
    public function update(StoreReturnRequest $request, PosReturnRepair $return): RedirectResponse
    {
        $return->update($request->validated());

        return redirect()->route('pos.returns.show', $return)
            ->with('success', 'Return updated');
    }

    /**
     * Process refund
     */
    public function processRefund(PosReturnRepair $return): RedirectResponse
    {
        if ($return->status === 'initiated') {
            // Restock the returned item if it is inventory tracked and not already restocked
            $item = $return->item;
            $meta = $return->meta ?? [];

            if ($item && $item->product_id && empty($meta['restocked'])) {
                $product = \App\Models\InventoryProduct::find($item->product_id);
                if ($product) {
                    // Use quantity_returned if available, otherwise fallback to item quantity
                    $qty = $return->quantity_returned > 0 ? $return->quantity_returned : $item->quantity;
                    $product->updateStock($qty, 'add', 'return:'.$return->id);
                }

                $meta['restocked'] = true;
            }

            // Create refund payment record if refund_amount provided and not already refunded
            if (! empty($return->refund_amount) && empty($meta['refund_payment_id'])) {
                $sale = $return->sale;

                if ($sale) {
                    $payment = $sale->payments()->create([
                        'pos_customer_id' => $sale->pos_customer_id,
                        'payment_method' => 'refund',
                        'amount' => -1 * (float) $return->refund_amount,
                        'currency' => $sale->currency ?? 'USD',
                        'exchange_rate' => $sale->exchange_rate ?? 1,
                        'reference' => 'refund:'.$return->id,
                        'status' => 'completed',
                    ]);

                    $meta['refund_payment_id'] = $payment->id;

                    // Update sale payment summary
                    $paymentSummary = $sale->payment_summary ?? [];
                    $paymentSummary['last_refund'] = $return->refund_amount;
                    $paymentSummary['total_paid'] = $sale->payments()->sum('amount');

                    $sale->update(['payment_summary' => $paymentSummary]);
                }
            }

            $return->update(['status' => 'processed', 'meta' => $meta]);
        }

        return redirect()->back()
            ->with('success', 'Refund processed');
    }

    /**
     * Destroy return
     */
    public function destroy(PosReturnRepair $return): RedirectResponse
    {
        $return->delete();

        return redirect()->route('pos.returns.index')
            ->with('success', 'Return deleted');
    }
}
