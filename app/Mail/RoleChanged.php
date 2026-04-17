<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RoleChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $userName,
        public readonly string  $userEmail,
        public readonly string  $oldRole,
        public readonly string  $newRole,
        public readonly string  $companyName,
        public readonly string  $appUrl,
        public readonly ?string $logoUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Access Level Has Changed — ' . $this->companyName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.role-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
