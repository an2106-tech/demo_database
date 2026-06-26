<?php

namespace App\Mail;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\RecruitmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateApplicationStatusChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;

    public function __construct(
        public Application $application,
        public RecruitmentJob $job,
        public StatusApplicationEnum $oldStatus,
        public StatusApplicationEnum $newStatus,
    ) {
        $this->subjectLine = sprintf(
            '[%s] Hồ sơ ứng tuyển vị trí %s đã cập nhật trạng thái',
            config('app.name'),
            $this->job->title,
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.candidate-application-status-change',
            with: [
                'subjectLine' => $this->subjectLine,
                'application' => $this->application,
                'job' => $this->job,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ],
        );
    }
}
