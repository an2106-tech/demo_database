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
                ->as('Thu-moi-nhan-viec-' . \Illuminate\Support\Str::slug($this->candidate->name) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    protected function resolveTemplate(): array
    {
        // 1. Cập nhật nội dung Offer chuyên nghiệp theo yêu cầu
        $fallbackSubject = 'Thư mời làm việc (Job Offer) – Vị trí {{job_title}} – {{app_name}}';
        
        $fallbackBody = implode("\n", [
            '<p>Thân gửi <strong>{{candidate_name}}</strong>,</p>',
            '<p>Thay mặt ban lãnh đạo <strong>{{app_name}}</strong>, tôi rất vui mừng thông báo rằng chúng tôi chính thức mời bạn gia nhập công ty với vị trí <strong>{{job_title}}</strong>.</p>',
            '<p>Chúng tôi rất ấn tượng với năng lực chuyên môn và thái độ làm việc của bạn trong suốt quá trình phỏng vấn. Chúng tôi tin rằng bạn sẽ là một mảnh ghép tuyệt vời cho đội ngũ của chúng tôi.</p>',
            '<p>Dưới đây là tóm tắt một số thông tin cơ bản:</p>',
            '<ul>',
            '<li><strong>Mức lương đề nghị:</strong> {{salary_offered}}</li>',
            '<li><strong>Ngày bắt đầu làm việc:</strong> {{start_date}}</li>',
            '<li><strong>Thời gian thử việc:</strong> {{probation_months}}</li>',
            '</ul>',
            '<p><strong>Vui lòng xem file PDF đính kèm</strong> để biết chi tiết về các điều khoản công việc, quyền lợi bảo hiểm, và chính sách phúc lợi dành cho bạn.</p>',
            '<div>{{offer_content}}</div>',
            '{{offer_response_actions}}',
            '<p>Để xác nhận lời mời này, bạn vui lòng phản hồi email này hoặc ký tên vào bản Offer Letter đính kèm và gửi lại cho chúng tôi trước ngày <strong>{{expiration_date}}</strong>.</p>',
            '<p>Chào mừng bạn đến với đội ngũ của chúng tôi!</p>',
            '<p>Trân trọng,<br><strong>Phòng Nhân sự - {{app_name}}</strong></p>',
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
            '{{start_date}}' => e(optional($this->offer->start_date)->format('d/m/Y') ?? 'Chưa cập nhật'),
            '{{probation_months}}' => e((string) $this->offer->probation_months.' tháng'),
            '{{expiration_date}}' => e(optional($this->offer->expires_at)->format('d/m/Y') ?? 'hết hạn sau 3 ngày'),
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
            return '<div style="margin: 15px 0; padding: 10px; border-left: 4px solid #eee;">' . nl2br(e($content)) . '</div>';
        }

        return '';
    }

    protected function buildOfferResponseActionsHtml(): string
    {
        $expiresAt = $this->offer->expires_at ?? now()->addDays(3);
        $baseUrl = $this->resolvePublicBaseUrl();

        $acceptPath = URL::temporarySignedRoute(
            'offers.respond.accept',
            $expiresAt,
            ['offer' => $this->offer->getKey()],
            absolute: false,
        );

        $declinePath = URL::temporarySignedRoute(
            'offers.respond.decline',
            $expiresAt,
            ['offer' => $this->offer->getKey()],
            absolute: false,
        );

        $acceptUrl = $baseUrl . $acceptPath;
        $declineUrl = $baseUrl . $declinePath;

        return implode('', [
            '<div style="margin: 24px 0; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; text-align: center;">',
            '<p style="margin: 0 0 16px;"><strong>Phản hồi offer ngay từ email</strong></p>',
            '<a href="' . e($acceptUrl) . '" style="display: inline-block; margin: 0 8px 8px; padding: 12px 22px; background: #16a34a; color: #ffffff; text-decoration: none; border-radius: 999px; font-weight: 700;">Đồng ý offer</a>',
            '<a href="' . e($declineUrl) . '" style="display: inline-block; margin: 0 8px 8px; padding: 12px 22px; background: #dc2626; color: #ffffff; text-decoration: none; border-radius: 999px; font-weight: 700;">Từ chối offer</a>',
            '<p style="margin: 12px 0 0; color: #475569; font-size: 13px;">Liên kết có hiệu lực đến ' . e($expiresAt->format('d/m/Y H:i')) . '.</p>',
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
