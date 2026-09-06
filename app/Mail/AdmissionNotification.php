<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class AdmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $candidate,
        public array $academicContext
    ) {
        $logoPath = public_path('logo/logo.png');

        if (file_exists($logoPath)) {
            $this->withSymfonyMessage(function (Email $message) use ($logoPath) {
                $message->embedFromPath(
                    $logoPath,
                    'heritage-logo'
                );
            });
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Heritage Polytechnic — Notification of Admission',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admission-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
