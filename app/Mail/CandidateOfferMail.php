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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
            view: 'emails.candidate-offer',
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

        $absolutePath = Storage::disk('local')->path($path);

        return [
            Attachment::fromPath($absolutePath)
                ->as('Thu-moi-nhan-viec-' . \Illuminate\Support\Str::slug($this->candidate->name) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    protected function resolveTemplate(): array
    {
        // Nội dung dự phòng khi chưa cấu hình mẫu email thư mời nhận việc.
        $fallbackSubject = 'Đề nghị tuyển dụng - Vị trí {{job_title}} - {{app_name}}';
        
        $fallbackBody = implode("\n", [
            '<p>Thân gửi <strong>{{candidate_name}}</strong>,</p>',
            '<p><strong>{{app_name}}</strong> trân trọng gửi đến bạn đề nghị tuyển dụng cho vị trí <strong>{{job_title}}</strong>.</p>',
            '<p>Dựa trên kết quả đánh giá trong quá trình tuyển dụng, chúng tôi tin rằng kinh nghiệm và định hướng nghề nghiệp của bạn phù hợp với nhu cầu của vị trí này.</p>',
            '<p>Thông tin chính của đề nghị:</p>',
            '<ul>',
            '<li><strong>Mã đề nghị:</strong> #{{offer_id}}</li>',
            '<li><strong>Mức lương đề nghị:</strong> {{salary_offered}}</li>',
            '<li><strong>Ngày bắt đầu làm việc:</strong> {{start_date}}</li>',
            '<li><strong>Thời gian thử việc:</strong> {{probation_months}}</li>',
            '<li><strong>Hạn phản hồi:</strong> {{expiration_date}}</li>',
            '</ul>',
            '<p>Vui lòng xem file PDF đính kèm để biết chi tiết nội dung đề nghị tuyển dụng.</p>',
            '<div>{{offer_content}}</div>',
            '{{offer_response_actions}}',
            '<p>Đề nghị này không thay thế hợp đồng lao động chính thức. Sau khi bạn xác nhận đồng ý, bộ phận tuyển dụng sẽ liên hệ để hướng dẫn các thủ tục tiếp theo.</p>',
            '<p>Trân trọng,<br><strong>Bộ phận Tuyển dụng - {{app_name}}</strong></p>',
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

        if (! str_contains($body, '{{offer_response_actions}}')) {
            $body .= "\n{{offer_response_actions}}";
        }

        $replacements = [
            '{{candidate_name}}' => e($this->candidate->name),
            '{{candidate_email}}' => e((string) $this->candidate->email),
            '{{offer_id}}' => e((string) $this->offer->id),
            '{{job_title}}' => e($this->job->title),
            '{{salary_offered}}' => e(number_format((float) $this->offer->salary_offered, 0, ',', '.').' VND'),
            '{{start_date}}' => e(optional($this->offer->start_date)->format('d/m/Y') ?? 'Chưa cập nhật'),
            '{{probation_months}}' => e((string) $this->offer->probation_months.' tháng'),
            '{{expiration_date}}' => e(optional($this->offer->expires_at)->format('d/m/Y H:i') ?? 'hết hạn sau 3 ngày'),
            '{{offer_content}}' => $this->resolveOfferContentHtml(),
            '{{offer_response_actions}}' => $this->buildOfferResponseActionsHtml(),
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
            return '<div style="margin: 16px 0; padding: 14px 18px; border-left: 3px solid #09090b; background: #fafafa; border-radius: 0 8px 8px 0; color: #3f3f46; font-size: 13px; line-height: 1.65;">' . nl2br(e($content)) . '</div>';
        }

        return '';
    }

    protected function buildOfferResponseActionsHtml(): string
    {
        $expiresAt = $this->offer->expires_at ?? now()->addDays(3);
        $sentAt = $this->offer->sent_at ?? now();
        $responseParameters = [
            'offer' => $this->offer->getKey(),
            'sent' => $sentAt->getTimestamp(),
        ];
        $baseUrl = $this->resolvePublicBaseUrl();

        $acceptPath = URL::signedRoute(
            'offers.respond.accept',
            $responseParameters,
            absolute: false,
        );

        $declinePath = URL::signedRoute(
            'offers.respond.decline',
            $responseParameters,
            absolute: false,
        );

        $acceptUrl = $baseUrl . $acceptPath;
        $declineUrl = $baseUrl . $declinePath;

        return implode('', [
            '<div style="margin: 28px 0 16px; padding: 22px 20px; background-color: #fafafa; border: 1px solid #ebebeb; border-radius: 8px; text-align: center;">',
            '<p style="margin: 0 0 16px; color: #111111; font-weight: 600; font-size: 14px;">Phản hồi thư mời nhận việc</p>',
            '<a href="' . e($acceptUrl) . '" style="display: inline-block; margin: 0 4px 8px; padding: 11px 24px; background-color: #111111; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px;">Đồng ý nhận việc</a>',
            '<a href="' . e($declineUrl) . '" style="display: inline-block; margin: 0 4px 8px; padding: 10px 22px; background-color: #ffffff; color: #111111; border: 1px solid #d4d4d4; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 13px;">Từ chối thư mời</a>',
            '<p style="margin: 14px 0 0; color: #8c8c8c; font-size: 12px; line-height: 1.5;">Liên kết có hiệu lực đến ' . e($expiresAt->format('d/m/Y H:i')) . '.</p>',
            '</div>',
        ]);
    }

    protected function resolvePublicBaseUrl(): string
    {
        $request = request();
        $requestBaseUrl = $request?->getSchemeAndHttpHost();
        $requestHost = $request?->getHost();

        if (filled($requestBaseUrl) && ! in_array($requestHost, ['127.0.0.1', 'localhost'], true)) {
            return rtrim($requestBaseUrl, '/');
        }

        return rtrim((string) config('app.public_url', config('app.url')), '/');
    }
}
