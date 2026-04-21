<?php

namespace App\Mail;

use App\Models\PosSale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;

    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(PosSale $sale, $pdfContent = null)
    {
        $this->sale = $sale;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice #'.$this->sale->invoice_no.' from '.config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.sales.invoice',
            with: [
                'customerName' => $this->sale->customer->name ?? 'Valued Customer',
                'invoiceNo' => $this->sale->invoice_no,
                'totalAmount' => $this->sale->total,
                'currency' => $this->sale->currency,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfContent) {
            return [
                Attachment::fromData(fn () => $this->pdfContent, 'Invoice-'.$this->sale->invoice_no.'.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
