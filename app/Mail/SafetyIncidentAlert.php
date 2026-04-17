<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SafetyIncidentAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $incidentDate,
        public readonly string  $departmentName,
        public readonly array   $indicators,   // ['Fatal Incident Injury' => 1, 'LTI' => 2, ...]
        public readonly string  $companyName,
        public readonly string  $appUrl,
        public readonly ?string $logoUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Safety Incident Recorded — ' . $this->departmentName . ' (' . $this->incidentDate . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.safety-incident-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
