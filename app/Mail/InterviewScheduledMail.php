<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Interview;
use App\Services\InterviewCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class InterviewScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;

    protected string $htmlBody;

    public function __construct(
        public Interview $interview,
        public string $recipientLabel = 'recipient',
    ) {
        [$this->subjectLine, $this->htmlBody] = $this->resolveTemplate();
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
            view: 'emails.interview-scheduled',
            with: [
                'subjectLine' => $this->subjectLine,
                'htmlBody' => $this->htmlBody,
            ],
        );
    }

    public function attachments(): array
    {
        $service = app(InterviewCalendarService::class);

        return [
            Attachment::fromData(
                fn (): string => $service->buildContent($this->interview),
                $service->fileNameFor($this->interview),
            )->withMime('text/calendar'),
        ];
    }

    protected function resolveTemplate(): array
    {
        $application = $this->interview->application;
        $job = $application?->job;
        $candidate = $application?->candidate;
        $interviewer = $this->interview->interviewer;
        $locationText = app(InterviewCalendarService::class)->resolveLocation($this->interview);

        $fallbackSubject = 'Lịch phỏng vấn - {{candidate_name}} - {{job_title}}';
        $fallbackBody = implode("\n", [
            '<p>Xin chào,</p>',
            '<p>Lịch phỏng vấn đã được sắp xếp cho hồ sơ ứng tuyển của ứng viên.</p>',
            '<p><strong>Thông tin lịch phỏng vấn</strong></p>',
            '<ul>',
            '<li>Ứng viên: {{candidate_name}}</li>',
            '<li>Vị trí: {{job_title}}</li>',
            '<li>Thời gian: {{scheduled_at}}</li>',
            '<li>Hình thức: {{interview_type}}</li>',
            '<li>Địa điểm / Link: {{interview_location}}</li>',
            '<li>Người phỏng vấn: {{interviewer_name}}</li>',
            '</ul>',
            '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
            '<p>Vui lòng sắp xếp thời gian tham gia phỏng vấn theo lịch đã được thiết lập.</p>',
            '<p>Trân trọng,<br>{{app_name}}</p>',
        ]);

        $subject = $fallbackSubject;
        $body = $fallbackBody;

        if (Schema::hasTable('email_templates')) {
            $template = EmailTemplate::query()
                ->where('type', 'interview_invite')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if ($template) {
                $subject = $template->subject ?: $fallbackSubject;
                $body = $template->body ?: $fallbackBody;
            }
        }

        $replacements = [
            '{{candidate_name}}' => e($candidate?->name ?? 'Ứng viên'),
            '{{candidate_email}}' => e((string) ($candidate?->email ?? '')),
            '{{job_title}}' => e($job?->title ?? 'Vị trí ứng tuyển'),
            '{{scheduled_at}}' => e($this->formatDisplayDate($this->interview->scheduled_at)),
            '{{interview_type}}' => e($this->interview->type === 'online' ? 'Online' : 'Offline'),
            '{{interview_location}}' => e($locationText),
            '{{interviewer_name}}' => e($interviewer?->name ?? 'N/A'),
            '{{interview_notes}}' => e($this->interview->notes ?: 'Không có'),
            '{{recipient_label}}' => e($this->recipientLabel),
            '{{app_name}}' => e((string) config('app.name')),
        ];

        return [
            strtr($subject, $replacements),
            strtr($body, $replacements),
        ];
    }

    protected function formatDisplayDate($date): string
    {
        if (! $date) {
            return now()->setTimezone(config('app.interview_timezone', 'Asia/Saigon'))->format('d/m/Y H:i');
        }

        return $date->copy()
            ->setTimezone(config('app.interview_timezone', 'Asia/Saigon'))
            ->format('d/m/Y H:i');
    }
}
