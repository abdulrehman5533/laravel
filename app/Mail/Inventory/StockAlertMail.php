<?php

namespace App\Mail\Inventory;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alert;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\StockAlert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $severity = strtoupper($this->alert->severity);

        return new Envelope(
            subject: "[{$severity}] Stock Alert: {$this->alert->product->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory.stock-alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
