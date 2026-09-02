<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\EmailTemplate;
use App\Models\RecruitmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class CandidateApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;

    protected string $htmlBody;

    public function __construct(
        public Candidate $candidate,
        public Application $application,
        public RecruitmentJob $job,
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
            view: 'emails.candidate-application-received',
            with: [
                'subjectLine' => $this->subjectLine,
                'htmlBody' => $this->htmlBody,
            ],
        );
    }

    protected function resolveTemplate(): array
    {
        // 1. Cập nhật mẫu Acknowledgement Email chuyên nghiệp vào Fallback
        $fallbackSubject = '[{{app_name}}] - Xác nhận tiếp nhận hồ sơ ứng tuyển vị trí {{job_title}}';

        $fallbackBody = implode("\n", [
            '<p>Thân gửi <strong>{{candidate_name}}</strong>,</p>',
            '<p>Cảm ơn bạn đã quan tâm và dành thời gian gửi hồ sơ ứng tuyển vị trí <strong>{{job_title}}</strong> tại <strong>{{app_name}}</strong>.</p>',
            '<p>Chúng tôi xác nhận đã nhận được hồ sơ của bạn thành công. Dưới đây là thông tin ghi nhận từ hệ thống:</p>',
            '<div class="info-card">',
            '    <div class="info-item"><span>Mã hồ sơ</span><span class="info-value">#{{application_id}}</span></div>',
            '    <div class="info-item"><span>Vị trí</span><span class="info-value">{{job_title}}</span></div>',
            '    <div class="info-item"><span>Thời gian nộp</span><span class="info-value">{{applied_at}}</span></div>',
            '    <div class="info-item"><span>Email ứng tuyển</span><span class="info-value">{{candidate_email}}</span></div>',
            '</div>',
            '<p>Bộ phận tuyển dụng đang trong quá trình xem xét các hồ sơ phù hợp. Nếu hồ sơ của bạn đáp ứng các yêu cầu công việc, chúng tôi sẽ sớm liên hệ với bạn để trao đổi về các bước tiếp theo.</p>',
            '<p>Trân trọng,<br><strong>Đội ngũ Tuyển dụng - {{app_name}}</strong></p>',
        ]);

        $subject = $fallbackSubject;
        $body = $fallbackBody;

        // Kiểm tra database để lấy template động (nếu có)
        if (Schema::hasTable('email_templates')) {
            $template = EmailTemplate::query()
                ->where('type', 'auto_reply')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if ($template) {
                $subject = $template->subject ?: $fallbackSubject;
                $body = $template->body ?: $fallbackBody;
            }
        }

        // Thực hiện thay thế các placeholder bằng dữ liệu thực tế
        $replacements = [
            '{{candidate_name}}' => e($this->candidate->name),
            '{{candidate_email}}' => e((string) $this->candidate->email),
            '{{job_title}}' => e($this->job->title),
            '{{application_id}}' => (string) $this->application->id,
            '{{applied_at}}' => e($this->formatDisplayDate($this->application->applied_at)),
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
