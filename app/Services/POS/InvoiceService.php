<?php

namespace App\Services\POS;

use App\Mail\SaleInvoiceMail;
use App\Models\PosInvoice;
use App\Models\PosSale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate comprehensive invoice with shop details
     */
    public function generateInvoiceData(PosSale $sale): array
    {
        $title = 'TAX INVOICE';
        if ($sale->invoice_type === 'estimate') {
            $title = 'ESTIMATE / PRO-FORMA';
        } elseif ($sale->invoice_type === 'Non-Tax') {
            $title = $sale->is_wholesale ? 'WHOLESALE INVOICE' : 'COMMERCIAL INVOICE';
        } elseif ($sale->is_wholesale) {
            $title = 'WHOLESALE TAX INVOICE';
        }

        return [
            'invoice_title' => $title,
            'shop_name' => config('app.name', 'Jewelry Store'),
            'shop_ntn' => config('pos.shop_ntn', 'NTN: 1234567-8'),
            'shop_gst' => config('pos.shop_gst', 'GST: 123456-78-9'),
            'shop_address' => config('pos.shop_address', 'Store Address'),
            'shop_phone' => config('pos.shop_phone', '+92-300-000-0000'),
            'shop_email' => config('pos.shop_email', 'info@jewelry.com'),
            'sale' => $sale->relationLoaded('items') ? $sale : $sale->load('customer', 'items', 'payments'),
            'total_gold_weight' => $sale->items->sum('weight'),
            'total_stone_carat' => $sale->items->sum('stone_carat'),
            'payment_status' => $sale->payment_status,
            'outstanding_balance' => $sale->outstanding_balance,
        ];
    }

    /**
     * Generate thermal receipt format (80mm)
     */
    public function generateThermalReceipt(PosSale $sale): string
    {
        $data = $this->generateInvoiceData($sale);
        $title = $data['invoice_title'];

        // Abbreviate for thermal receipt (80mm)
        $title = str_replace('INVOICE', 'INV', $title);
        $title = str_replace('ESTIMATE / PRO-FORMA', 'ESTIMATE', $title);
        $title = str_replace('WHOLESALE TAX INV', 'WHOLESALE TAX', $title);

        $width = 42;
        $line = str_repeat('=', $width)."\n";
        $dash = str_repeat('-', $width)."\n";

        $receipt = $line;
        $receipt .= str_pad($title, $width, ' ', STR_PAD_BOTH)."\n";
        $receipt .= $line."\n";

        $receipt .= 'Inv: '.$sale->invoice_no."\n";
        $receipt .= 'Date: '.$sale->sale_time->format('d-m-Y H:i')."\n";
        $receipt .= 'Cust: '.($sale->customer->name ?? 'Walk-in')."\n";
        if ($sale->customer && $sale->customer->phone) {
            $receipt .= 'Phone: '.$sale->customer->phone."\n";
        }
        $receipt .= "\n";

        $receipt .= "ITEMS:\n";
        $receipt .= $dash;
        foreach ($sale->items as $item) {
            $receipt .= $item->description."\n";

            $gross = $item->gross_weight > 0 ? $item->gross_weight : $item->weight;
            $stoneWt = $item->stone_weight ?? 0;
            $net = $item->net_weight > 0 ? $item->net_weight : ($gross - $stoneWt);

            $receipt .= sprintf("Gr:%.3fg | St:%.3fg | Net:%.3fg\n", $gross, $stoneWt, $net);
            $receipt .= sprintf("Pur: %s | Rate: %.2f\n", $item->gold_purity ?? '—', $item->gold_rate > 0 ? $item->gold_rate : $item->unit_price);

            if ($item->making_charge_amount > 0) {
                $receipt .= sprintf("Making: %.2f\n", $item->making_charge_amount);
            }
            if ($item->stone_price > 0) {
                $receipt .= sprintf("Stone Val: %.2f\n", $item->stone_price);
            }

            $qtyStr = sprintf('Qty: %s', number_format($item->quantity, 2));
            $totalStr = sprintf('Total: %.2f', $item->line_total);
            $receipt .= $qtyStr.str_repeat(' ', max(1, $width - strlen($qtyStr) - strlen($totalStr))).$totalStr."\n";
            $receipt .= $dash;
        }

        $receipt .= "\n";

        $totals = [
            'Subtotal' => $sale->subtotal,
            'Making Total' => $sale->making_charges,
            'Wastage Val' => $sale->wastage_amount,
            'Tax' => $sale->tax_amount,
            'Discount' => -$sale->discount,
        ];

        foreach ($totals as $label => $value) {
            if ($value != 0) {
                $valStr = number_format($value, 2);
                $receipt .= $label.str_repeat(' ', max(1, $width - strlen($label) - strlen($valStr))).$valStr."\n";
            }
        }

        $receipt .= $line;
        $grandTotalLabel = 'GRAND TOTAL ('.$sale->currency.')';
        $grandTotalVal = number_format($sale->total, 2);
        $receipt .= $grandTotalLabel.str_repeat(' ', max(1, $width - strlen($grandTotalLabel) - strlen($grandTotalVal))).$grandTotalVal."\n";
        $receipt .= $line."\n";

        $totalPaid = $sale->payments->where('status', 'completed')->sum('amount');
        if ($totalPaid > 0) {
            $paidLabel = 'Paid Amount';
            $paidVal = number_format($totalPaid, 2);
            $receipt .= $paidLabel.str_repeat(' ', max(1, $width - strlen($paidLabel) - strlen($paidVal))).$paidVal."\n";

            $balLabel = 'Balance Due';
            $balVal = number_format($sale->outstanding_balance, 2);
            $receipt .= $balLabel.str_repeat(' ', max(1, $width - strlen($balLabel) - strlen($balVal))).$balVal."\n";
            $receipt .= $dash."\n";
        }

        $totalGross = $sale->items->sum(function ($i) {
            return $i->gross_weight > 0 ? $i->gross_weight : $i->weight;
        });
        $totalNet = $sale->items->sum(function ($i) {
            return $i->net_weight > 0 ? $i->net_weight : ($i->gross_weight - $i->stone_weight);
        });

        $receipt .= sprintf("Total Gross Wt: %.3fg\n", $totalGross);
        $receipt .= sprintf("Total Net Wt:   %.3fg\n", $totalNet);
        $receipt .= "\n";

        $receipt .= str_pad('Thank you for your purchase!', $width, ' ', STR_PAD_BOTH)."\n";
        $receipt .= str_pad('Please visit again', $width, ' ', STR_PAD_BOTH)."\n";
        $receipt .= $line;

        return $receipt;
    }

    /**
     * Generate A4 invoice format (HTML)
     */
    public function generateA4Invoice(PosSale $sale): string
    {
        $data = $this->generateInvoiceData($sale);

        return view('pos.invoices.a4', $data)->render();
    }

    /**
     * Generate PDF invoice using dompdf
     */
    public function generatePdfInvoice(PosSale $sale): string
    {
        $data = $this->generateInvoiceData($sale);
        $html = view('pos.invoices.pdf', $data)->render();

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 96);

        return $pdf->output();
    }

    /**
     * Generate WhatsApp-friendly invoice (text-based)
     */
    public function generateWhatsAppInvoice(PosSale $sale): string
    {
        $data = $this->generateInvoiceData($sale);
        $title = ucwords(strtolower($data['invoice_title']));

        $message = '📄 *'.$title.' #'.$sale->invoice_no."*\n\n";
        $message .= 'Customer: '.($sale->customer->name ?? 'Guest')."\n";
        $message .= 'Date: '.$sale->sale_time->format('d-m-Y H:i')."\n\n";

        $message .= "*Items:*\n";
        foreach ($sale->items as $item) {
            $message .= '• '.$item->description.' x'.$item->quantity.' - '.number_format($item->line_total, 2)."\n";
        }

        $message .= "\n*Summary:*\n";
        $message .= 'Subtotal: '.number_format($sale->subtotal, 2)."\n";
        if ($sale->making_charges > 0) {
            $message .= 'Making: '.number_format($sale->making_charges, 2)."\n";
        }
        if ($sale->wastage_amount > 0) {
            $message .= 'Wastage: '.number_format($sale->wastage_amount, 2)."\n";
        }
        $message .= 'Tax: '.number_format($sale->tax_amount, 2)."\n";
        $message .= '*Total: '.number_format($sale->total, 2).' '.$sale->currency."*\n\n";

        $message .= 'Thank you! 🙏';

        return $message;
    }

    /**
     * Save invoice to database
     */
    public function saveInvoice(PosSale $sale, string $format, string $content, ?string $filePath = null): PosInvoice
    {
        return $sale->invoices()->create([
            'format' => $format,
            'content' => $content,
            'file_path' => $filePath,
            'meta' => ['generated_at' => now()],
        ]);
    }

    /**
     * Generate and download PDF invoice
     */
    public function generateAndDownloadPdf(PosSale $sale): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->generateInvoiceData($sale);
        $html = view('pos.invoices.pdf', $data)->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 96);

        return $pdf->download('Invoice-'.$sale->invoice_no.'.pdf');
    }

    /**
     * Generate PDF and save to storage
     */
    public function generateAndSavePdf(PosSale $sale): string
    {
        $data = $this->generateInvoiceData($sale);
        $html = view('pos.invoices.pdf', $data)->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 96);

        $filename = 'invoices/'.date('Y/m').'/Invoice-'.$sale->invoice_no.'.pdf';
        Storage::put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Auto-generate essential invoice formats on sale completion
     * Heavy tasks like PDF generation and Emailing are optional to prevent timeouts
     */
    public function generateAllFormats(PosSale $sale, bool $includeHeavyTasks = false): void
    {
        // Generate thermal receipt
        $thermal = $this->generateThermalReceipt($sale);
        $this->saveInvoice($sale, 'thermal', $thermal);

        // Generate A4 HTML
        $a4 = $this->generateA4Invoice($sale);
        $this->saveInvoice($sale, 'a4', $a4);

        // Generate WhatsApp message
        $whatsapp = $this->generateWhatsAppInvoice($sale);
        $this->saveInvoice($sale, 'whatsapp', $whatsapp);

        if ($includeHeavyTasks) {
            // Generate and save PDF
            $pdfContent = null;
            try {
                $pdfPath = $this->generateAndSavePdf($sale);
                $invoice = $sale->invoices()->where('format', 'pdf')->first();
                if ($invoice) {
                    $invoice->update(['file_path' => $pdfPath]);
                } else {
                    $invoice = $this->saveInvoice($sale, 'pdf', '', $pdfPath);
                }

                // Get PDF content for email
                $pdfContent = Storage::get($pdfPath);
            } catch (\Exception $e) {
                logger()->error('PDF generation failed: '.$e->getMessage());
            }

            // Auto Email Invoice
            if ($sale->customer && $sale->customer->email) {
                $this->emailInvoice($sale, $pdfContent);
            }
        }
    }

    /**
     * Email invoice to customer
     */
    public function emailInvoice(PosSale $sale, $pdfContent = null): bool
    {
        if (! $sale->customer || ! $sale->customer->email) {
            return false;
        }

        try {
            Mail::to($sale->customer->email)->send(new SaleInvoiceMail($sale, $pdfContent));

            $sale->update([
                'emailed_at' => now(),
                'email_status' => 'sent',
            ]);

            $invoice = $sale->invoices()->where('format', 'pdf')->first();
            if ($invoice) {
                $invoice->update(['emailed_at' => now()]);
            }

            $sale->addAuditLog('invoice_emailed', 'Invoice sent to '.$sale->customer->email);

            return true;
        } catch (\Exception $e) {
            logger()->error('Invoice email failed: '.$e->getMessage());
            $sale->update(['email_status' => 'failed']);

            return false;
        }
    }

    /**
     * Send WhatsApp invoice (integrate with WhatsApp API)
     */
    public function sendViaWhatsApp(PosSale $sale, string $phoneNumber): bool
    {
        // TODO: Integrate with WhatsApp Business API
        // Example: use Twilio or similar service
        $message = $this->generateWhatsAppInvoice($sale);

        // Send via API
        // return $this->whatsappClient->send($phoneNumber, $message);

        return true;
    }

    /**
     * Print thermal receipt
     */
    public function printThermalReceipt(PosSale $sale): void
    {
        $receipt = $this->generateThermalReceipt($sale);
        echo $receipt;
    }

    /**
     * Regenerate invoice for reprinting
     */
    public function reprintInvoice(PosSale $sale, string $format = 'thermal'): void
    {
        $sale->addAuditLog('invoice_reprinted', "Invoice reprinted in format: {$format}");

        if ($format === 'thermal') {
            $this->printThermalReceipt($sale);
        } elseif ($format === 'a4') {
            echo view('pos.invoices.pdf', ['sale' => $sale])->render();
        } elseif ($format === 'whatsapp') {
            echo $this->generateWhatsAppInvoice($sale);
        }
    }

    /**
     * Get all invoices for a sale
     */
    public function getInvoices(PosSale $sale)
    {
        return $sale->invoices()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Export invoice to CSV
     */
    public function exportToCSV(PosSale $sale): string
    {
        $csv = "Invoice Export\n";
        $csv .= "Invoice No,Date,Customer,Total,Payment Status\n";
        $csv .= "{$sale->invoice_no},{$sale->sale_time->format('Y-m-d')},";
        $customerName = $sale->customer ? $sale->customer->name : 'Walk-in';
        $csv .= "{$customerName},{$sale->total},{$sale->payment_status}\n\n";

        $csv .= "Item Description,Quantity,Unit Price,Amount\n";
        foreach ($sale->items as $item) {
            $csv .= "{$item->description},{$item->quantity},{$item->unit_price},{$item->line_total}\n";
        }

        return $csv;
    }
}
