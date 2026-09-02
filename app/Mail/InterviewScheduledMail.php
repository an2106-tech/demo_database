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
            '{{round_number}}' => e((string) ((int) ($this->interview->round_number ?: 1))),
            '{{round_name}}' => e($this->interview->round_name ?: 'Vòng '.((int) ($this->interview->round_number ?: 1))),
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
                : '<p>Cảm ơn bạn đã ứng tuyển vào vị trí <strong>{{job_title}}</strong>. Chúng tôi trân trọng mời bạn tham dự buổi phỏng vấn theo thông tin dưới đây.</p>',
            '<div class="info-card">',
            '    <div class="info-item"><span>Vòng phỏng vấn</span><span class="info-value">{{round_name}}</span></div>',
            '    <div class="info-item"><span>Vị trí ứng tuyển</span><span class="info-value">{{job_title}}</span></div>',
            '    <div class="info-item"><span>Thời gian</span><span class="info-value">{{scheduled_at}}</span></div>',
            '    <div class="info-item"><span>Thời lượng</span><span class="info-value">{{duration_minutes}} phút</span></div>',
            '    <div class="info-item"><span>Hình thức</span><span class="info-value">{{interview_type}}</span></div>',
            '    <div class="info-item"><span>Địa điểm / Link</span><span class="info-value"><a href="{{interview_location}}">{{interview_location}}</a></span></div>',
            '    <div class="info-item"><span>Người phụ trách</span><span class="info-value">{{interviewer_name}}</span></div>',
            '</div>',
            '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
            '<p style="color: #737373; font-size: 13px;">File lịch hẹn (.ics) đã được đính kèm để bạn thuận tiện lưu hoặc cập nhật lịch cá nhân.</p>',
            '<p>Trân trọng,<br><strong>Phòng Tuyển dụng - {{app_name}}</strong></p>',
        ]);
    }

    protected function evaluatorBody(): string
    {
        return implode("\n", [
            '<p>Chào anh/chị,</p>',
            $this->isUpdate
                ? '<p>Lịch phỏng vấn ứng viên <strong>{{candidate_name}}</strong> đã được cập nhật.</p>'
                : '<p>Anh/chị được phân công tham gia phỏng vấn ứng viên <strong>{{candidate_name}}</strong> cho vị trí <strong>{{job_title}}</strong>.</p>',
            '<div class="info-card">',
            '    <div class="info-item"><span>Ứng viên</span><span class="info-value">{{candidate_name}}</span></div>',
            '    <div class="info-item"><span>Vòng phỏng vấn</span><span class="info-value">{{round_name}}</span></div>',
            '    <div class="info-item"><span>Vai trò của anh/chị</span><span class="info-value">{{recipient_role}}</span></div>',
            '    <div class="info-item"><span>Vị trí tuyển dụng</span><span class="info-value">{{job_title}}</span></div>',
            '    <div class="info-item"><span>Thời gian</span><span class="info-value">{{scheduled_at}}</span></div>',
            '    <div class="info-item"><span>Thời lượng</span><span class="info-value">{{duration_minutes}} phút</span></div>',
            '    <div class="info-item"><span>Hình thức</span><span class="info-value">{{interview_type}}</span></div>',
            '    <div class="info-item"><span>Địa điểm / Link</span><span class="info-value"><a href="{{interview_location}}">{{interview_location}}</a></span></div>',
            '</div>',
            '<p><strong>Ghi chú:</strong> {{interview_notes}}</p>',
            '<p>Sau buổi phỏng vấn, vui lòng mở hồ sơ trên hệ thống và gửi phiếu đánh giá theo mẫu đã phân công.</p>',
            '<p>Trân trọng,<br><strong>Phòng Tuyển dụng - {{app_name}}</strong></p>',
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
