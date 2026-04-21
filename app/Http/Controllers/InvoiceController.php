<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function generateInvoice($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);

        $pdf = Pdf::loadView('invoices.template', compact('sale'));

        return $pdf->stream("invoice-{$sale->invoice_number}.pdf");
    }

    public function printInvoice($id)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($id);

        return view('invoices.print', compact('sale'));
    }
}
