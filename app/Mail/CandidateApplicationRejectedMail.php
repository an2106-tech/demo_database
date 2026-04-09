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
            view: 'emails.candidate-application-received',
            with: [
                'subjectLine' => $this->subjectLine,
                'htmlBody' => $this->htmlBody,
            ],
        );
    }

    protected function resolveTemplate(): array
    {
        $fallbackSubject = 'Cập nhật kết quả hồ sơ ứng tuyển';
        $fallbackBody = implode("\n", [
            '<p>Chào {{candidate_name}},</p>',
            '<p>Cảm ơn bạn đã quan tâm và ứng tuyển vào vị trí <strong>{{job_title}}</strong>.</p>',
            '<p>Sau quá trình xem xét kỹ lưỡng, rất tiếc hiện tại chúng tôi chưa thể tiếp tục hồ sơ của bạn cho vị trí này.</p>',
            '<p>Thông tin hồ sơ:</p>',
            '<ul>',
            '<li>Mã hồ sơ ứng tuyển: #{{application_id}}</li>',
            '<li>Vị trí: {{job_title}}</li>',
            '<li>Thời gian cập nhật: {{updated_at}}</li>',
            '</ul>',
            '<p><strong>Lý do:</strong> {{rejected_reason}}</p>',
            '<p>Chúng tôi đánh giá cao sự quan tâm của bạn và sẽ lưu lại hồ sơ cho những cơ hội phù hợp hơn trong tương lai.</p>',
            '<p>Hy vọng sẽ có cơ hội được đồng hành cùng bạn trong thời gian tới.</p>',
            '<p>Trân trọng,<br>{{app_name}}</p>',
        ]);

        $subject = $fallbackSubject;
        $body = $fallbackBody;

        if (Schema::hasTable('email_templates')) {
            $template = EmailTemplate::query()
                ->where('type', 'rejection')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if ($template) {
                $subject = $template->subject ?: $fallbackSubject;
                $body = $template->body ?: $fallbackBody;
            }
        }

        $replacements = [
            '{{candidate_name}}' => e($this->candidate->name),
            '{{candidate_email}}' => e((string) $this->candidate->email),
            '{{job_title}}' => e($this->job->title),
            '{{application_id}}' => (string) $this->application->id,
            '{{updated_at}}' => e(optional($this->application->updated_at)->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')),
            '{{rejected_reason}}' => e($this->application->rejected_reason ?: 'Chưa có ghi chú cụ thể.'),
            '{{app_name}}' => e((string) config('app.name')),
        ];

        return [
            strtr($subject, $replacements),
            strtr($body, $replacements),
        ];
    }
}
