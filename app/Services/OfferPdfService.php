<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OfferPdfService
{
    public function __construct(
        private OfferLetterMergeService $merge,
    ) {}

    /**
     * Generate the candidate-facing PDF before the offer can be submitted or sent.
     * A temporary file prevents an incomplete render from replacing a valid document.
     */
    public function refreshForOffer(
        Offer $offer,
        ?CarbonInterface $issuedAt = null,
        ?CarbonInterface $responseDeadline = null,
        ?User $approver = null,
    ): void
    {
        $offer->loadMissing(['letterTemplate', 'approvedByUser', 'application.candidate', 'application.job.branch']);

        $issuedAt ??= $offer->approved_at ?? $offer->sent_at ?? $offer->created_at ?? now();
        $responseDeadline ??= $offer->expires_at;
        $approver ??= $offer->approvedByUser;

        $templateSnapshot = is_array($offer->letter_template_snapshot)
            ? $offer->letter_template_snapshot
            : [];
        $templateBody = $templateSnapshot['body_html'] ?? $offer->letterTemplate?->body_html;
        $usesTemplate = filled($templateBody);

        $letterBody = $usesTemplate
            ? $this->merge->mergeTemplateBody((string) $templateBody, $offer, [
                '{{issued_date}}' => e($issuedAt->format('d/m/Y')),
                '{{expiration_date}}' => e($responseDeadline?->format('d/m/Y H:i') ?? ''),
            ])
            : $this->buildFallbackBody($offer);

        $extra = trim((string) $offer->content);
        $additionalBlock = $usesTemplate && $extra !== ''
            ? '<div style="margin-top:14px;"><strong>Ghi chú thêm:</strong><p style="margin:6px 0 0;">'.nl2br(e($extra)).'</p></div>'
            : '';

        $candidateName = $offer->application?->candidate?->name ?? '';

        $relative = 'offers/'.$offer->id.'/offer-letter.pdf';
        $temporaryRelative = 'offers/'.$offer->id.'/offer-letter.tmp.pdf';
        Storage::disk('local')->makeDirectory('offers/'.$offer->id);

        try {
            Pdf::loadView('pdf.offer-letter', [
                'offer' => $offer,
                'letterInnerHtml' => $letterBody.$additionalBlock,
                'candidateName' => $candidateName,
                'issuedAt' => $issuedAt,
                'responseDeadline' => $responseDeadline,
                'offerReference' => sprintf('OFR-%s-%06d', $issuedAt->format('Y'), $offer->id),
                'approverName' => $approver?->name ?? 'Đại diện đơn vị tuyển dụng',
                'approverTitle' => $approver ? $this->approverTitle($approver) : 'Đã phê duyệt trên hệ thống',
            ])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                ])
                ->save(Storage::disk('local')->path($temporaryRelative));

            if (! Storage::disk('local')->exists($temporaryRelative)
                || Storage::disk('local')->size($temporaryRelative) === 0) {
                throw new RuntimeException('PDF generated without content.');
            }

            Storage::disk('local')->move($temporaryRelative, $relative);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($temporaryRelative);

            Log::warning('Unable to generate offer PDF.', [
                'offer_id' => $offer->id,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Không thể tạo PDF đề nghị tuyển dụng.', previous: $exception);
        }

        $offer->forceFill(['pdf_path' => $relative])->save();
    }

    protected function buildFallbackBody(Offer $offer): string
    {
        $candidateName = e($offer->application?->candidate?->name ?? 'ứng viên');
        $jobTitle = e($offer->application?->job?->title ?? 'vị trí ứng tuyển');
        $content = trim((string) $offer->content);

        return implode('', [
            "<p>Thân gửi <strong>{$candidateName}</strong>,</p>",
            "<p>FPT Education trân trọng gửi đến bạn thư mời nhận việc cho vị trí <strong>{$jobTitle}</strong>.</p>",
            '<p>Các điều khoản chính được thể hiện trong bảng thông tin của thư mời này.</p>',
            $content !== ''
                ? '<div style="margin-top:14px;"><strong>Nội dung bổ sung:</strong><p style="margin:6px 0 0;">'.nl2br(e($content)).'</p></div>'
                : '',
            '<p>Trân trọng,<br/><strong>Khối Nhân sự FPT Education</strong></p>',
        ]);
    }

    private function approverTitle(User $approver): string
    {
        return ($approver->role === 'director' || $approver->hasRole('director'))
            ? 'Giám đốc chi nhánh'
            : 'Đại diện đơn vị tuyển dụng';
    }
}
