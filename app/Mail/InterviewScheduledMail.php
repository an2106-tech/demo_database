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

        // 1. Cập nhật mẫu Interview Invitation chuyên nghiệp
        $fallbackSubject = 'Thư mời phỏng vấn vị trí {{job_title}} - {{app_name}}';
        
        $fallbackBody = implode("\n", [
            '<p>Chào <strong>{{candidate_name}}</strong>,</p>',
            '<p>Chúc mừng bạn đã vượt qua vòng lọc hồ sơ! Sau khi xem xét các kỹ năng và kinh nghiệm của bạn, chúng tôi trân trọng mời bạn tham gia buổi phỏng vấn để trao đổi chi tiết hơn về sự phù hợp của bạn với đội ngũ <strong>{{app_name}}</strong>.</p>',
            '<p><strong>Thông tin chi tiết về buổi phỏng vấn:</strong></p>',
            '<ul style="line-height: 1.6;">',
            '<li><strong>Vị trí ứng tuyển:</strong> {{job_title}}</li>',
            '<li><strong>Thời gian:</strong> {{scheduled_at}}</li>',
            '<li><strong>Hình thức:</strong> {{interview_type}}</li>',
            '<li><strong>Địa điểm / Link họp:</strong> <a href="{{interview_location}}">{{interview_location}}</a></li>',
            '<li><strong>Người phỏng vấn:</strong> {{interviewer_name}}</li>',
            '</ul>',
            '<p><strong>Ghi chú từ bộ phận tuyển dụng:</strong> {{interview_notes}}</p>',
            '<p>Vui lòng phản hồi email này để xác nhận sự tham gia của bạn. Chúng tôi đã đính kèm lịch hẹn (iCal) vào email này để bạn có thể dễ dàng lưu vào lịch cá nhân.</p>',
            '<p>Mong sớm được gặp bạn!</p>',
            '<p>Trân trọng,<br><strong>Phòng Nhân sự - {{app_name}}</strong></p>',
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
            '{{interview_type}}' => e($this->interview->type === 'online' ? 'Phỏng vấn Online' : 'Phỏng vấn trực tiếp (Offline)'),
            '{{interview_location}}' => e($locationText),
            '{{interviewer_name}}' => e($interviewer?->name ?? 'Hội đồng tuyển dụng'),
            '{{interview_notes}}' => e($this->interview->notes ?: 'Không có ghi chú bổ sung'),
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
            ->format('H:i, \n\g\à\y d/m/Y');
    }
}