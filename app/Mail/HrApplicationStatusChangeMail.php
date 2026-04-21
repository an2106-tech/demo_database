<?php

namespace App\Mail;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HrApplicationStatusChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;

    protected string $htmlBody;

    public function __construct(
        public Candidate $candidate,
        public Application $application,
        public RecruitmentJob $job,
        public StatusApplicationEnum $oldStatus,
        public StatusApplicationEnum $newStatus,
    ) {
        $this->subjectLine = sprintf(
            '[%s] Hồ sơ ứng tuyển cập nhật trạng thái — %s',
            config('app.name'),
            $this->job->title,
        );

        $this->htmlBody = $this->buildHtmlBody();
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
            view: 'emails.hr-application-status-change',
            with: [
                'subjectLine' => $this->subjectLine,
                'htmlBody' => $this->htmlBody,
            ],
        );
    }

    protected function buildHtmlBody(): string
    {
        $branchName = $this->job->relationLoaded('branch')
            ? $this->job->branch?->name
            : $this->job->branch()->value('name');

        $adminUrl = ApplicationResource::getUrl('edit', ['record' => $this->application]);

        $statusChangeText = sprintf(
            'Trạng thái hồ sơ đã thay đổi từ <strong>%s</strong> sang <strong>%s</strong>',
            $this->oldStatus->getLabel(),
            $this->newStatus->getLabel()
        );

        return implode("\n", [
            '<p>Xin chào,</p>',
            '<p>Hồ sơ ứng tuyển sau đã được cập nhật trạng thái:</p>',
            '<ul>',
            '<li><strong>Vị trí:</strong> '.e($this->job->title).'</li>',
            '<li><strong>Chi nhánh:</strong> '.e($branchName ?: '—').'</li>',
            '<li><strong>Ứng viên:</strong> '.e($this->candidate->name).'</li>',
            '<li><strong>Email ứng viên:</strong> '.e((string) $this->candidate->email).'</li>',
            '<li><strong>Mã hồ sơ:</strong> #'.$this->application->id.'</li>',
            '<li><strong>Cập nhật trạng thái:</strong> '.$statusChangeText.'</li>',
            '</ul>',
            '<p><a href="'.$adminUrl.'">Xem chi tiết hồ sơ</a></p>',
            '<p>Trân trọng,<br><strong>Hệ thống tuyển dụng '.e((string) config('app.name')).'</strong></p>',
        ]);
    }
}