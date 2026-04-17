<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsumableLowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array  $lowItems,     // array of ['name','category','use_unit','current_stock','reorder_level','deficit']
        public string $companyName,
        public string $appUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ ' . $this->companyName . ' — Low Stock Alert (' . count($this->lowItems) . ' item' . (count($this->lowItems) > 1 ? 's' : '') . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consumable-low-stock',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
