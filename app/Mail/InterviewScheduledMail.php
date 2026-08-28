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
        public bool $isUpdate = false,
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

        $isCandidate = $this->recipientLabel === 'candidate';
        $fallbackSubject = $isCandidate
            ? ($this->isUpdate
                ? 'Cập nhật lịch phỏng vấn vị trí {{job_title}} - {{app_name}}'
                : 'Thư mời phỏng vấn vị trí {{job_title}} - {{app_name}}')
            : ($this->isUpdate
                ? 'Cập nhật phân công phỏng vấn: {{candidate_name}} - {{job_title}}'
                : 'Phân công phỏng vấn: {{candidate_name}} - {{job_title}}');

        $fallbackBody = $isCandidate
            ? $this->candidateBody()
            : $this->evaluatorBody();

        $subject = $fallbackSubject;
        $body = $fallbackBody;
        $usesStoredTemplate = false;

        if ($isCandidate && Schema::hasTable('email_templates')) {
            $template = EmailTemplate::query()
                ->where('type', 'interview_invite')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if ($template) {
                $subject = $template->subject ?: $fallbackSubject;
                $body = $template->body ?: $fallbackBody;
                $usesStoredTemplate = true;
            }
        }

        $replacements = [
            '{{candidate_name}}' => e($candidate?->name ?? 'Ứng viên'),
            '{{candidate_email}}' => e((string) ($candidate?->email ?? '')),
            '{{job_title}}' => e($job?->title ?? 'Vị trí ứng tuyển'),
            '{{scheduled_at}}' => e($this->formatDisplayDate($this->interview->scheduled_at)),
            '{{duration_minutes}}' => e((string) ((int) ($this->interview->duration_minutes ?: 60))),
            '{{interview_type}}' => e($this->interview->type === 'online' ? 'Phỏng vấn Online' : 'Phỏng vấn trực tiếp (Offline)'),
            '{{interview_location}}' => e($locationText),
            '{{interviewer_name}}' => e($interviewer?->name ?? 'Hội đồng tuyển dụng'),
            '{{interview_notes}}' => e($this->interview->notes ?: 'Không có ghi chú bổ sung'),
            '{{recipient_label}}' => e($this->recipientLabel),
            '{{recipient_role}}' => e(in_array($this->recipientLabel, ['lead', 'interviewer'], true) ? 'Người phụ trách vòng phỏng vấn' : 'Thành viên đánh giá'),
            '{{app_name}}' => e((string) config('app.name')),
        ];

        $resolvedSubject = strtr($subject, $replacements);
        $resolvedBody = strtr($body, $replacements);

        if ($this->isUpdate && $usesStoredTemplate) {
            $resolvedSubject = 'Cập nhật - '.$resolvedSubject;
            $resolvedBody = '<p>Chúng tôi xin gửi thông tin <strong>cập nhật lịch phỏng vấn</strong>. Vui lòng theo dõi lịch mới bên dưới.</p>'
                .$resolvedBody;
        }

        return [$resolvedSubject, $resolvedBody];
    }

    protected function candidateBody(): string
    {
        return implode("\n", [
            '<p>Chào <strong>{{candidate_name}}</strong>,</p>',
            $this->isUpdate
                ? '<p>Chúng tôi xin gửi đến bạn thông tin <strong>cập nhật lịch phỏng vấn</strong> từ bộ phận tuyển dụng.</p>'
                : '<p>Chúc mừng bạn đã vượt qua vòng lọc hồ sơ. Chúng tôi trân trọng mời bạn tham gia buổi phỏng vấn cho vị trí <strong>{{job_title}}</strong> tại <strong>{{app_name}}</strong>.</p>',
            '<p><strong>Thông tin buổi phỏng vấn:</strong></p>',
            '<ul style="line-height: 1.6;">',
            '<li><strong>Thời gian:</strong> {{scheduled_at}}</li>',
            '<li><strong>Thời lượng:</strong> {{duration_minutes}} phút</li>',
            '<li><strong>Hình thức:</strong> {{interview_type}}</li>',
            '<li><strong>Địa điểm / Link họp:</strong> <a href="{{interview_location}}">{{interview_location}}</a></li>',
            '<li><strong>Người phụ trách vòng phỏng vấn:</strong> {{interviewer_name}}</li>',
            '</ul>',
            '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
            $this->isUpdate
                ? '<p>Vui lòng cập nhật lại lịch cá nhân theo file iCal đính kèm.</p>'
                : '<p>Vui lòng phản hồi email để xác nhận tham gia. File iCal đã được đính kèm để bạn lưu lịch.</p>',
            '<p>Trân trọng,<br><strong>Phòng Nhân sự - {{app_name}}</strong></p>',
        ]);
    }

    protected function evaluatorBody(): string
    {
        return implode("\n", [
            '<p>Chào anh/chị,</p>',
            $this->isUpdate
                ? '<p>Lịch phỏng vấn ứng viên <strong>{{candidate_name}}</strong> đã được cập nhật.</p>'
                : '<p>Anh/chị được phân công tham gia phỏng vấn ứng viên <strong>{{candidate_name}}</strong> cho vị trí <strong>{{job_title}}</strong>.</p>',
            '<p><strong>Vai trò:</strong> {{recipient_role}}</p>',
            '<ul style="line-height: 1.6;">',
            '<li><strong>Thời gian:</strong> {{scheduled_at}}</li>',
            '<li><strong>Thời lượng:</strong> {{duration_minutes}} phút</li>',
            '<li><strong>Hình thức:</strong> {{interview_type}}</li>',
            '<li><strong>Địa điểm / Link họp:</strong> <a href="{{interview_location}}">{{interview_location}}</a></li>',
            '</ul>',
            '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
            '<p>Sau buổi phỏng vấn, vui lòng mở hồ sơ trên hệ thống và gửi phiếu đánh giá theo mẫu đã phân công.</p>',
            '<p>Trân trọng,<br><strong>Phòng Nhân sự - {{app_name}}</strong></p>',
        ]);
    }

    protected function formatDisplayDate($date): string
    {
        if (! $date) {
            return now()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i');
        }

        return $date->copy()
            ->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->format('H:i, \n\g\à\y d/m/Y');
    }
}
