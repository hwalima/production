<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetByAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $userName,
        public readonly string  $userEmail,
        public readonly string  $plainPassword,
        public readonly string  $resetBy,
        public readonly string  $companyName,
        public readonly string  $appUrl,
        public readonly ?string $logoUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Password Has Been Reset — ' . $this->companyName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-by-admin',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
