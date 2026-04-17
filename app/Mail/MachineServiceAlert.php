<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MachineServiceAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array   $overdueList,
        public string  $companyName,
        public string  $appUrl,
        public ?string $logoUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ ' . $this->companyName . ' — Machine Service Alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.machine-service-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
