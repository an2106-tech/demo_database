<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferApprovedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Offer $offer,
        public Application $application,
        public RecruitmentJob $job,
        public User $recipient,
        public string $recipientRole, // 'hr', 'pm', 'director'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thư mời nhận việc đã được gửi - ' . ($this->application->candidate?->name ?? 'Ứng viên') . ' - ' . ($this->job->title ?? 'Vị trí'),
        );
    }

    public function content(): Content
    {
        // Chỉ hiển thị lương cho HR và director
        $showSalary = in_array($this->recipientRole, ['hr', 'director'], true);
        $expiresAt = $this->offer->expires_at;
        $responseDeadline = $expiresAt?->format('d/m/Y H:i') ?? 'Chưa xác định';
        $responseWindow = 'Chưa xác định';

        if ($expiresAt) {
            $now = now($expiresAt->timezone);

            if ($expiresAt->isFuture()) {
                $remainingHours = max(1, (int) ceil(($expiresAt->getTimestamp() - $now->getTimestamp()) / 3600));
                $responseWindow = $remainingHours >= 24
                    ? ceil($remainingHours / 24).' ngày'
                    : $remainingHours.' giờ';
            } else {
                $responseWindow = 'Đã quá hạn';
            }
        }

        return new Content(
            view: 'emails.offer-approved-notification',
            with: [
                'offer' => $this->offer,
                'application' => $this->application,
                'job' => $this->job,
                'recipientName' => $this->recipient->name,
                'recipientRole' => $this->recipientRole,
                'candidateName' => $this->application->candidate?->name ?? 'Ứng viên',
                'candidateEmail' => $this->application->candidate?->email ?? '-',
                'jobTitle' => $this->job->title ?? 'Vị trí',
                'salaryOffered' => $showSalary ? (number_format((float) $this->offer->salary_offered, 0, ',', '.') . ' VND') : null,
                'startDate' => $this->offer->start_date?->format('d/m/Y') ?? 'Chưa xác định',
                'probationMonths' => $this->offer->probation_months,
                'showSalary' => $showSalary,
                'responseDeadline' => $responseDeadline,
                'responseWindow' => $responseWindow,
            ],
        );
    }
}
