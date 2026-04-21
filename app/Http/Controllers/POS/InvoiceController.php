<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Mail\SaleInvoiceMail;
use App\Models\PosInvoice;
use App\Models\PosSale;
use App\Services\POS\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Share invoice via email
     */
    public function sendEmail(Request $request, PosSale $sale): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $pdfContent = $this->invoiceService->generatePdfInvoice($sale);

            Mail::to($request->email)->send(new SaleInvoiceMail($sale, $pdfContent));

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
        $token = Crypt::encryptString($sale->id);
        $url = route('public.invoice', ['token' => $token]);

        return back()->with('share_link', $url)->with('success', 'Sharing link generated successfully');
    }

    /**
     * List invoices
     */
    public function index(): View
    {
        $invoices = PosInvoice::with('sale')
            ->latest()
            ->paginate(20);

        return view('pos.invoices.index', compact('invoices'));
    }

    /**
     * Generate invoice for sale
     */
    public function store(Request $request): RedirectResponse
    {
        $sale = PosSale::findOrFail($request->input('pos_sale_id'));
        $format = $request->input('format', 'thermal');

        $content = match ($format) {
            'a4' => $this->invoiceService->generateA4Invoice($sale),
            'whatsapp' => $this->invoiceService->generateWhatsAppInvoice($sale),
            default => $this->invoiceService->generateThermalReceipt($sale),
        };

        $this->invoiceService->saveInvoice($sale, $format, $content);

        return redirect()->route('pos.invoices.show', ['invoice' => $sale->invoices()->latest()->first()])
            ->with('success', 'Invoice generated');
    }

    /**
     * Show invoice
     */
    public function show(PosInvoice $invoice): View
    {
        $sale = $invoice->sale;
        $data = $this->invoiceService->generateInvoiceData($sale);

        return view('pos.invoices.a4', $data);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(PosSale $sale): StreamedResponse
    {
        $pdfContent = $this->invoiceService->generatePdfInvoice($sale);

        return response()->streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $sale->invoice_no.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Print thermal receipt
     */
    public function printThermal(PosSale $sale)
    {
        $receipt = $this->invoiceService->generateThermalReceipt($sale);

        return response($receipt, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'inline; filename="'.$sale->invoice_no.'.txt"',
        ]);
    }
}
