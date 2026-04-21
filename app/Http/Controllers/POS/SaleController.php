<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreSaleRequest;
use App\Http\Requests\POS\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Services\Accounting\SalesAccountingService;
use App\Services\POS\InvoiceService;
use App\Services\POS\PaymentService;
use App\Services\POS\POSService;
use App\Services\POS\PricingService;
use App\Services\POS\ReturnService;
use App\Services\POS\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private POSService $posService,
        private PricingService $pricingService,
        private SalesService $salesService,
        private PaymentService $paymentService,
        private InvoiceService $invoiceService,
        private ReturnService $returnService,
        private SalesAccountingService $accountingService
    ) {}

    /**
     * Display all sales
     */
    public function index(): View
    {
        $sales = PosSale::with(['customer', 'items'])
            ->latest('sale_time')
            ->paginate(15);

        return view('pos.sales.index', compact('sales'));
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $customers = Customer::latest()->limit(5)->get();
        $recentSales = PosSale::with('customer')->latest()->limit(5)->get();
        $branches = \App\Models\Branch::all();

        return view('pos.sales.create', compact('customers', 'recentSales', 'branches'));
    }

    /**
     * Store a new sale (draft)
     */
    public function store(StoreSaleRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $sale = $this->salesService->createSale($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'sale' => $sale,
                    'message' => 'Sale initialized successfully',
                ]);
            }

            return redirect()->route('pos.sales.show', $sale)
                ->with('success', 'Sale started. Add items to proceed.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create sale: '.$e->getMessage());
        }
    }

    /**
     * Display sale details with items
     */
    public function show(PosSale $sale): View
    {
        $sale->load(['customer', 'items', 'payments', 'holds']);

        return view('pos.sales.show', compact('sale'));
    }

    /**
     * Edit sale (only draft/held allowed)
     */
    public function edit(PosSale $sale): View
    {
        if (! in_array($sale->status, ['open', 'held'])) {
            abort(403, 'Cannot edit completed or cancelled sales');
        }

        $customers = Customer::all();
        $sale->load('items');

        return view('pos.sales.edit', compact('sale', 'customers'));
    }

    /**
     * Update sale
     */
    public function update(UpdateSaleRequest $request, PosSale $sale): RedirectResponse
    {
        $sale->update($request->validated());
        $this->posService->calculateSaleTotals($sale);

        return redirect()->route('pos.sales.show', $sale)
            ->with('success', 'Sale updated successfully');
    }

    /**
     * Add item via AJAX
     */
    public function addItem(PosSale $sale, array $itemData): JsonResponse
    {
        try {
            $item = $this->posService->addItem($sale, $itemData);
            $this->posService->calculateSaleTotals($sale);

            return response()->json(['success' => true, 'item' => $item], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Store item from form (synchronous)
     */
    public function storeItem(\Illuminate\Http\Request $request, PosSale $sale): RedirectResponse
    {
        $data = $request->validate([
            'sku' => 'nullable|string|max:191',
            'product_id' => 'nullable|exists:inventory_products,id',
            'description' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'required|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'gross_weight' => 'nullable|numeric|min:0',
            'stone_weight' => 'nullable|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'gold_purity' => 'nullable|string',
            'gold_rate' => 'nullable|numeric|min:0',
            'making_charge' => 'nullable|numeric|min:0',
            'making_charge_type' => 'nullable|string|in:fixed,per_gram,per_piece',
            'wastage_percent' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'stone_type' => 'nullable|string',
            'stone_carat' => 'nullable|numeric|min:0',
            'stone_count' => 'nullable|integer|min:0',
            'stone_price' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->posService->addItem($sale, $data);
            $this->posService->calculateSaleTotals($sale);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('pos.sales.show', $sale)->with('success', 'Item added');
    }

    /**
     * Hold current sale (pause for later)
     */
    public function hold(PosSale $sale): RedirectResponse
    {
        if ($sale->status !== 'open') {
            return redirect()->back()->with('error', 'Cannot hold this sale');
        }

        $this->posService->holdSale($sale, request('note'));

        return redirect()->route('pos.index')
            ->with('success', 'Sale held. You can resume it later.');
    }

    /**
     * Resume a held sale
     */
    public function resume(PosSale $sale): RedirectResponse
    {
        if ($sale->status !== 'held') {
            return redirect()->back()->with('error', 'Only held sales can be resumed');
        }

        $sale->update(['status' => 'open']);

        return redirect()->route('pos.sales.show', $sale)
            ->with('success', 'Sale resumed');
    }

    /**
     * Complete sale and prepare for payment
     */
    public function complete(\Illuminate\Http\Request $request, PosSale $sale): RedirectResponse
    {
        try {
            if ($sale->status === 'held') {
                $this->salesService->resumeHeldSale($sale);
            }

            // Update invoice type if provided
            if ($request->has('invoice_type')) {
                $sale->update(['invoice_type' => $request->invoice_type]);
            }

            $this->salesService->completeSale($sale);

            $this->accountingService->postSaleToAccounting($sale);

            return redirect()->route('pos.payments.create', ['sale_id' => $sale->id])
                ->with('success', 'Sale completed as '.ucfirst($sale->invoice_type).' Invoice. Now collect payment.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to complete sale: '.$e->getMessage());
        }
    }

    /**
     * Cancel sale
     */
    public function cancel(PosSale $sale): RedirectResponse
    {
        try {
            $this->salesService->cancelSale($sale, request('reason'));

            return redirect()->route('pos.index')
                ->with('success', 'Sale cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel sale: '.$e->getMessage());
        }
    }

    /**
     * Destroy sale (soft delete)
     */
    public function destroy(PosSale $sale): RedirectResponse
    {
        if ($sale->stock_moved) {
            $this->posService->restockSale($sale);
        }

        $sale->delete();

        return redirect()->route('pos.sales.index')
            ->with('success', 'Sale deleted and items restocked');
    }

    /**
     * Add item by barcode (AJAX)
     */
    public function addItemByBarcode(\Illuminate\Http\Request $request, PosSale $sale): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string',
            'quantity' => 'nullable|numeric|min:0.001',
        ]);

        try {
            $product = \App\Models\InventoryProduct::where('sku', $request->barcode)
                ->orWhere('barcode', $request->barcode)
                ->first();

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found with barcode: '.$request->barcode,
                ], 404);
            }

            $itemData = [
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => $request->quantity ?? 1,
                'unit_price' => $product->selling_price ?? $product->price,
                'sku' => $product->sku,
                'tax_percent' => $product->tax_rate ?? 0,
            ];

            $item = $this->posService->addItem($sale, $itemData);
            $this->posService->calculateSaleTotals($sale);

            $sale->load('items');

            return response()->json([
                'success' => true,
                'item' => $item,
                'cart' => $sale->items,
                'totals' => [
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax_amount,
                    'discount' => $sale->discount_amount,
                    'total' => $sale->total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add item by product ID (AJAX)
     */
    public function addItemByProduct(\Illuminate\Http\Request $request, PosSale $sale): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:inventory_products,id',
            'quantity' => 'nullable|numeric|min:0.001',
        ]);

        try {
            $product = \App\Models\InventoryProduct::findOrFail($request->product_id);

            $itemData = [
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => $request->quantity ?? 1,
                'unit_price' => $product->selling_price ?? $product->price,
                'sku' => $product->sku,
                'tax_percent' => $product->tax_rate ?? 0,
            ];

            $item = $this->posService->addItem($sale, $itemData);
            $this->posService->calculateSaleTotals($sale);

            $sale->load('items');

            return response()->json([
                'success' => true,
                'item' => $item,
                'cart' => $sale->items,
                'totals' => [
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax_amount,
                    'discount' => $sale->discount_amount,
                    'total' => $sale->total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove item from cart (AJAX)
     */
    public function removeItem(PosSale $sale, PosSaleItem $item): JsonResponse
    {
        if ($item->pos_sale_id !== $sale->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item does not belong to this sale',
            ], 403);
        }

        try {
            $item->delete();
            $this->posService->calculateSaleTotals($sale);

            $sale->load('items');

            return response()->json([
                'success' => true,
                'cart' => $sale->items,
                'totals' => [
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax_amount,
                    'discount' => $sale->discount_amount,
                    'total' => $sale->total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update item quantity (AJAX)
     */
    public function updateItemQuantity(\Illuminate\Http\Request $request, PosSale $sale, PosSaleItem $item): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.001',
        ]);

        if ($item->pos_sale_id !== $sale->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item does not belong to this sale',
            ], 403);
        }

        try {
            if ($item->product_id) {
                $product = \App\Models\InventoryProduct::find($item->product_id);
                if ($product && $product->current_stock < $request->quantity) {
                    $available = (float) $product->current_stock;
                    throw new \Exception("Stock Alert: '{$product->name}' is out of stock. Only {$available} available in inventory.");
                }
            }

            $item->update(['quantity' => $request->quantity]);
            $this->posService->calculateSaleTotals($sale);

            $sale->load('items');

            return response()->json([
                'success' => true,
                'item' => $item,
                'cart' => $sale->items,
                'totals' => [
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax_amount,
                    'discount' => $sale->discount_amount,
                    'total' => $sale->total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get cart items (AJAX)
     */
    public function getCart(PosSale $sale): JsonResponse
    {
        $sale->load('items');

        return response()->json([
            'success' => true,
            'cart' => $sale->items,
            'totals' => [
                'subtotal' => $sale->subtotal,
                'tax' => $sale->tax_amount,
                'discount' => $sale->discount_amount,
                'total' => $sale->total,
            ],
        ]);
    }

    /**
     * Apply discount to sale (AJAX)
     */
    public function applyDiscount(\Illuminate\Http\Request $request, PosSale $sale): JsonResponse
    {
        $request->validate([
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
        ]);

        try {
            $this->salesService->applyDiscount($sale, $request->discount_type, $request->discount_value);

            return response()->json([
                'success' => true,
                'totals' => [
                    'subtotal' => $sale->subtotal,
                    'tax' => $sale->tax_amount,
                    'discount' => $sale->discount,
                    'total' => $sale->total,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * View sale returns
     */
    public function returns(PosSale $sale): View
    {
        $returns = $this->returnService->getReturnHistory($sale);

        return view('pos.sales.returns', compact('sale', 'returns'));
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoicePdf(PosSale $sale)
    {
        try {
            return $this->invoiceService->generateAndDownloadPdf($sale);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate PDF: '.$e->getMessage());
        }
    }

    /**
     * View sale payment history
     */
    public function paymentHistory(PosSale $sale): View
    {
        $payments = $this->paymentService->getPaymentHistory($sale);
        $summary = $this->paymentService->getPaymentSummary($sale);

        return view('pos.sales.payments', compact('sale', 'payments', 'summary'));
    }

    /**
     * Print A4 format invoice
     */
    public function printA4Invoice(PosSale $sale): View
    {
        $data = $this->invoiceService->generateInvoiceData($sale);

        return view('pos.invoices.a4', $data);
    }

    /**
     * Share invoice via email
     */
    public function sendEmail(\Illuminate\Http\Request $request, PosSale $sale): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $pdfContent = $this->invoiceService->generatePdfInvoice($sale);

            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\SaleInvoiceMail($sale, $pdfContent));

            $sale->update([
                'emailed_at' => now(),
                'email_status' => 'sent',
            ]);

            return back()->with('success', 'Invoice has been sent to '.$request->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: '.$e->getMessage());
        }
    }

    /**
     * Generate secure link
     */
    public function generateLink(PosSale $sale): RedirectResponse
    {
        $token = \Illuminate\Support\Facades\Crypt::encryptString($sale->id);
        $url = route('public.invoice', ['token' => $token]);

        return back()->with('share_link', $url)->with('success', 'Sharing link generated successfully');
    }
}
