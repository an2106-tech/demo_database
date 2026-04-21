<?php

namespace App\Mail;

use App\Filament\Resources\OfferResource;
use App\Models\Application;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Offer $offer,
        public Application $application,
        public RecruitmentJob $job,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yeu cau duyet offer',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-approval-request',
            with: [
                'offer' => $this->offer,
                'application' => $this->application,
                'job' => $this->job,
                'recipientName' => $this->recipient->name,
                'approvalUrl' => $this->buildApprovalUrl(),
                'candidateName' => $this->application->candidate?->name ?? 'Ung vien',
                'jobTitle' => $this->job->title ?? 'Vi tri',
                'salaryOffered' => number_format((float) $this->offer->salary_offered, 0, ',', '.') . ' VND',
            ],
        );
    }

    protected function buildApprovalUrl(): string
    {
        return OfferResource::getUrl('edit', ['record' => $this->offer]);
    }
}
