<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StorePaymentRequest;
use App\Models\PosPayment;
use App\Models\PosSale;
use App\Services\POS\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private SalesService $salesService) {}

    /**
     * List all payments
     */
    public function index(): View
    {
        $payments = PosPayment::with(['sale', 'customer'])
            ->latest()
            ->paginate(20);

        return view('pos.payments.index', compact('payments'));
    }

    /**
     * Show payment creation form
     */
    public function create(): View
    {
        $saleId = request('sale_id');
        $sale = PosSale::with('items')->findOrFail($saleId);

        return view('pos.payments.create', compact('sale'));
    }

    /**
     * Record payment for sale
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $sale = PosSale::findOrFail($request->input('pos_sale_id'));

        try {
            $payment = $this->salesService->recordPayment($sale, $request->validated());

            return redirect()->route('pos.sales.show', $sale)
                ->with('success', 'Payment recorded successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    /**
     * Show payment details
     */
    public function show(PosPayment $payment): View
    {
        $payment->load(['sale', 'customer']);

        return view('pos.payments.show', compact('payment'));
    }

    /**
     * Show payment edit form
     */
    public function edit(PosPayment $payment): View
    {
        $payment->load(['sale', 'customer']);

        return view('pos.payments.edit', compact('payment'));
    }

    /**
     * Update payment details
     */
    public function update(PosPayment $payment, StorePaymentRequest $request): RedirectResponse
    {
        try {
            $this->salesService->updatePayment($payment, $request->validated());

            return redirect()->route('pos.payments.show', $payment)
                ->with('success', 'Payment updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    /**
     * Delete a payment
     */
    public function destroy(PosPayment $payment): RedirectResponse
    {
        $sale = $payment->sale;
        $payment->delete();

        $totalPaid = $sale->payments()->sum('amount');

        if ($totalPaid <= 0) {
            $sale->update(['payment_status' => 'unpaid']);
        } else {
            $sale->update(['payment_status' => 'partial']);
        }

        return redirect()->route('pos.payments.index')
            ->with('success', 'Payment deleted successfully');
    }

    /**
     * Continue a pending payment (mark as completed or process further)
     */
    public function continue(PosPayment $payment): RedirectResponse
    {
        if ($payment->status === 'pending') {
            try {
                $this->salesService->updatePayment($payment, ['status' => 'completed']);

                return redirect()->route('pos.payments.show', $payment)
                    ->with('success', 'Payment completed successfully');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Failed to complete payment: '.$e->getMessage());
            }
        }

        return redirect()->route('pos.payments.show', $payment)
            ->with('error', 'This payment is already completed');
    }
}
