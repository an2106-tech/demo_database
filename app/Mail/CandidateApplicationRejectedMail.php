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

class CandidateApplicationRejectedMail extends Mailable
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
            view: 'emails.candidate-application-rejected', // Bạn có thể dùng chung view hiển thị {!! $htmlBody !!}
            with: [
                'subjectLine' => $this->subjectLine,
                'htmlBody' => $this->htmlBody,
            ],
        );
    }

    protected function resolveTemplate(): array
    {
        // 1. Cập nhật mẫu Rejection Email chuyên nghiệp, giữ uy tín thương hiệu
        $fallbackSubject = '[{{app_name}}] - Thông báo kết quả ứng tuyển vị trí {{job_title}}';
        
        $fallbackBody = implode("\n", [
            '<p>Chào <strong>{{candidate_name}}</strong>,</p>',
            '<p>Cảm ơn bạn đã dành thời gian quan tâm đến cơ hội nghề nghiệp và tham gia quá trình tuyển dụng cho vị trí <strong>{{job_title}}</strong> tại <strong>{{app_name}}</strong>.</p>',
            '<p>Sau khi cân nhắc kỹ lưỡng các tiêu chí chuyên môn và định hướng hiện tại, chúng tôi rất tiếc phải thông báo rằng chưa thể đồng hành cùng bạn trong thời điểm này.</p>',
            '<p>Đây là một quyết định khó khăn vì chúng tôi đã nhận được rất nhiều hồ sơ chất lượng. Tuy nhiên, chúng tôi đánh giá cao những kỹ năng và kinh nghiệm mà bạn đã chia sẻ. Hồ sơ của bạn sẽ được lưu giữ trong danh sách tiềm năng của chúng tôi và chúng tôi sẽ chủ động liên hệ nếu có vị trí phù hợp hơn trong tương lai.</p>',
            '<p>Một lần nữa, cảm ơn bạn và chúc bạn sớm tìm được cơ hội nghề nghiệp như ý.</p>',
            '<p>Trân trọng,<br><strong>Phòng Nhân sự - {{app_name}}</strong></p>',
        ]);

        $subject = $fallbackSubject;
        $body = $fallbackBody;

        // Kiểm tra database để lấy template rejection (nếu có)
        if (Schema::hasTable('email_templates')) {
            $template = EmailTemplate::query()
                ->where('type', 'rejection') // Đổi từ auto_reply sang rejection
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if ($template) {
                $subject = $template->subject ?: $fallbackSubject;
                $body = $template->body ?: $fallbackBody;
            }
        }

        $updatedAt = $this->application->updated_at;

        // Thay thế placeholder (khớp mẫu trong EmailTemplateSeeder type=rejection)
        $replacements = [
            '{{candidate_name}}' => e($this->candidate->name),
            '{{candidate_email}}' => e((string) ($this->candidate->email ?? '')),
            '{{job_title}}' => e($this->job->title),
            '{{app_name}}' => e((string) config('app.name')),
            '{{application_id}}' => (string) $this->application->id,
            '{{updated_at}}' => e($this->formatDisplayDateTime($updatedAt)),
            // Keep older database templates safe without exposing internal HR notes.
            '{{rejected_reason}}' => 'Hồ sơ hiện chưa phù hợp với nhu cầu tuyển dụng tại thời điểm này.',
        ];

        return [
            strtr($subject, $replacements),
            strtr($body, $replacements),
        ];
    }

    protected function formatDisplayDate($date): string
    {
        if (! $date) {
            return now()->setTimezone(config('app.interview_timezone', 'Asia/Saigon'))->format('d/m/Y');
        }

        return $date->copy()
            ->setTimezone(config('app.interview_timezone', 'Asia/Saigon'))
            ->format('d/m/Y');
    }

    protected function formatDisplayDateTime($date): string
    {
        $tz = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');

        if (! $date) {
            return now()->setTimezone($tz)->format('d/m/Y H:i');
        }

        return $date->copy()
            ->setTimezone($tz)
            ->format('d/m/Y H:i');
    }
}
