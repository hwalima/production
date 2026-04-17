<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class OverdueActionItemsDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Collection $items,       // ActionItem models with ->department loaded
        public readonly string     $companyName,
        public readonly string     $appUrl,
        public readonly ?string    $logoUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->items->count();
        return new Envelope(
            subject: "⚠️ {$count} Overdue Action " . ($count === 1 ? 'Item' : 'Items') . " — {$this->companyName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.overdue-action-items',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
