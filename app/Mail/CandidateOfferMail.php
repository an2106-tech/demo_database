<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\EmailTemplate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CandidateOfferMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $subjectLine;

    protected string $htmlBody;

    public function __construct(
        public Candidate $candidate,
        public Application $application,
        public RecruitmentJob $job,
        public Offer $offer,
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

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $path = $this->offer->pdf_path;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $path)
                ->as('Thu-moi-nhan-viec.pdf')
                ->withMime('application/pdf'),
        ];
    }

    protected function resolveTemplate(): array
    {
        $fallbackSubject = 'Thư mời nhận việc - {{job_title}}';
        $fallbackBody = implode("\n", [
            '<p>Chào {{candidate_name}},</p>',
            '<p>Chúc mừng bạn đã vượt qua các vòng đánh giá cho vị trí <strong>{{job_title}}</strong>.</p>',
            '<p>Chúng tôi trân trọng gửi đến bạn thư mời nhận việc với các thông tin chính sau:</p>',
            '<ul>',
            '<li>Mức lương đề nghị: {{salary_offered}}</li>',
            '<li>Ngày bắt đầu dự kiến: {{start_date}}</li>',
            '<li>Thời gian thử việc: {{probation_months}}</li>',
            '</ul>',
            '<div>{{offer_content}}</div>',
            '<p>Nếu bạn đồng ý với đề nghị này, vui lòng phản hồi lại bộ phận tuyển dụng để chúng tôi hỗ trợ các bước tiếp theo.</p>',
            '<p>Trân trọng,<br>{{app_name}}</p>',
        ]);

        $subject = $fallbackSubject;
        $body = $fallbackBody;

        if (Schema::hasTable('email_templates')) {
            $template = EmailTemplate::query()
                ->where('type', 'offer')
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
            '{{salary_offered}}' => e(number_format((float) $this->offer->salary_offered, 0, ',', '.').' VND'),
            '{{start_date}}' => e(optional($this->offer->start_date)->format('d/m/Y') ?? 'Chua cap nhat'),
            '{{probation_months}}' => e((string) $this->offer->probation_months.' thang'),
            '{{offer_content}}' => $this->resolveOfferContentHtml(),
            '{{app_name}}' => e((string) config('app.name')),
        ];

        return [
            strtr($subject, $replacements),
            strtr($body, $replacements),
        ];
    }

    protected function resolveOfferContentHtml(): string
    {
        $content = trim((string) $this->offer->content);

        if ($content !== '') {
            return nl2br(e($content));
        }

        if ($this->offer->offer_letter_template_id) {
            return '<p><em>Nội dung chi tiết xem file PDF đính kèm.</em></p>';
        }

        return nl2br(e($content));
    }
}
