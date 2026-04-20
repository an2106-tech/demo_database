<?php

namespace App\Mail;

use App\Models\RecruitmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\URL;

class JobApprovalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $approveUrl;
    public string $rejectUrl;

    public function __construct(
        public RecruitmentJob $job,
        public string $directorName,
        public string $hrName
    ) {
        $this->approveUrl = URL::temporarySignedRoute(
            'jobs.direct_approve', 
            now()->addDays(7), 
            ['job' => $this->job->id]
        );
        $this->rejectUrl = URL::temporarySignedRoute(
            'jobs.direct_reject', 
            now()->addDays(7), 
            ['job' => $this->job->id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] - Yêu cầu phê duyệt tin tuyển dụng: ' . $this->job->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job-approval-notification',
            with: [
                'jobTitle' => $this->job->title,
                'branchName' => $this->job->branch?->name ?? 'N/A',
                'departmentName' => $this->job->department?->name ?? 'N/A',
                'directorName' => $this->directorName,
                'hrName' => $this->hrName,
            ],
        );
    }
}
