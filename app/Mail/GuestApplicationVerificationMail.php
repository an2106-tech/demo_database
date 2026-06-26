<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Candidate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class GuestApplicationVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    public function __construct(
        public Candidate $candidate,
        public Application $application,
    ) {
        $this->verificationUrl = URL::temporarySignedRoute(
            'candidates.applications.verify_email',
            now()->addDays(7),
            ['application' => $application->id],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.config('app.name').'] Xác thực email ứng tuyển',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-application-verification',
            with: [
                'candidate' => $this->candidate,
                'application' => $this->application,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }
}
