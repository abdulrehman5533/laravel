<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Services\POS\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PublicInvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Show invoice publicly using a secure token
     */
    public function show(Request $request)
    {
        $token = $request->get('token');

        if (! $token) {
            abort(404);
        }

        try {
            $saleId = Crypt::decryptString($token);
            $sale = PosSale::with(['items', 'customer', 'branch', 'createdBy'])->findOrFail($saleId);

            $data = $this->invoiceService->generateInvoiceData($sale);

            return view('pos.invoices.a4', $data);
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
