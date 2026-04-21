<?php

namespace App\Mail;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HrNewApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;

    protected string $htmlBody;

    public function __construct(
        public Candidate $candidate,
        public Application $application,
        public RecruitmentJob $job,
    ) {
        $this->subjectLine = sprintf(
            '[%s] Hồ sơ ứng tuyển mới — %s',
            config('app.name'),
            $this->job->title,
        );

        $branchName = $this->job->relationLoaded('branch')
            ? $this->job->branch?->name
            : $this->job->branch()->value('name');

        $adminUrl = ApplicationResource::getUrl('edit', ['record' => $this->application]);

        $this->htmlBody = implode("\n", [
            '<p>Xin chào,</p>',
            '<p>Có một hồ sơ ứng tuyển mới qua website với thông tin sau:</p>',
            '<ul>',
            '<li><strong>Vị trí:</strong> '.e($this->job->title).'</li>',
            '<li><strong>Chi nhánh:</strong> '.e($branchName ?: '—').'</li>',
            '<li><strong>Ứng viên:</strong> '.e($this->candidate->name).'</li>',
            '<li><strong>Email ứng viên:</strong> '.e((string) $this->candidate->email).'</li>',
            '<li><strong>Mã hồ sơ:</strong> #'.$this->application->id.'</li>',
            '</ul>',
            '<p><a href="'.$adminUrl.'">Mở hồ sơ trong hệ thống</a></p>',
            '<p>Trân trọng,<br><strong>Hệ thống tuyển dụng '.e((string) config('app.name')).'</strong></p>',
        ]);
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
            view: 'emails.candidate-application-received',
            with: [
                'subjectLine' => $this->subjectLine,
                'htmlBody' => $this->htmlBody,
            ],
        );
    }
}
