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
        $fallbackSubject = 'Xác nhận đã nhận hồ sơ ứng tuyển';
        $fallbackBody = implode("\n", [
            '<p>Chào {{candidate_name}},</p>',
            '<p>Cảm ơn bạn đã quan tâm và ứng tuyển vào vị trí <strong>{{job_title}}</strong>.</p>',
            '<p>Chúng tôi đã nhận được hồ sơ của bạn với các thông tin sau:</p>',
            '<ul>',
            '<li>Mã hồ sơ ứng tuyển: #{{application_id}}</li>',
            '<li>Vị trí: {{job_title}}</li>',
            '<li>Thời gian nộp: {{applied_at}}</li>',
            '<li>Email ứng tuyển: {{candidate_email}}</li>',
            '</ul>',
            '<p>Bộ phận tuyển dụng sẽ xem xét hồ sơ và liên hệ với bạn trong thời gian sớm nhất nếu hồ sơ phù hợp.</p>',
            '<p>Chúng tôi trân trọng sự quan tâm của bạn đối với {{app_name}} và hy vọng sẽ có cơ hội hợp tác cùng bạn.</p>',
            '<p>Trân trọng,<br>{{app_name}}</p>',
        ]);

        $subject = $fallbackSubject;
        $body = $fallbackBody;

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
