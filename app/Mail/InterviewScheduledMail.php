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

        $fallbackSubject = 'L?ch ph?ng v?n - {{candidate_name}} - {{job_title}}';
        $fallbackBody = implode("\n", [
            '<p>Xin chào,</p>',
            '<p>L?ch ph?ng v?n dã du?c s?p x?p cho h? so ?ng tuy?n c?a ?ng viên.</p>',
            '<p><strong>Thông tin l?ch ph?ng v?n</strong></p>',
            '<ul>',
            '<li>?ng viên: {{candidate_name}}</li>',
            '<li>V? trí: {{job_title}}</li>',
            '<li>Th?i gian: {{scheduled_at}}</li>',
            '<li>Hình th?c: {{interview_type}}</li>',
            '<li>Ð?a di?m / Link: {{interview_location}}</li>',
            '<li>Ngu?i ph?ng v?n: {{interviewer_name}}</li>',
            '</ul>',
            '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
            '<p>Vui lòng s?p x?p th?i gian tham gia ph?ng v?n theo l?ch dã du?c thi?t l?p.</p>',
            '<p>Trân tr?ng,<br>{{app_name}}</p>',
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
            '{{candidate_name}}' => e($candidate?->name ?? '?ng viên'),
            '{{candidate_email}}' => e((string) ($candidate?->email ?? '')),
            '{{job_title}}' => e($job?->title ?? 'V? trí ?ng tuy?n'),
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
