<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $userName,
        public readonly string  $userEmail,
        public readonly string  $plainPassword,
        public readonly string  $companyName,
        public readonly string  $appUrl,
        public readonly ?string $logoUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . $this->companyName . ' — Your Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-new-user',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
