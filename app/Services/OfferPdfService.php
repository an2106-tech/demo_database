<?php

namespace App\Services;

use App\Models\Offer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OfferPdfService
{
    public function __construct(
        private OfferLetterMergeService $merge,
    ) {}

    public function refreshForOffer(Offer $offer): void
    {
        $offer->loadMissing(['letterTemplate', 'application.candidate', 'application.job.branch']);

        $letterBody = $offer->letterTemplate
            ? $this->merge->mergeTemplateBody($offer->letterTemplate->body_html, $offer)
            : $this->buildFallbackBody($offer);

        $extra = trim((string) $offer->content);
        $additionalBlock = $offer->letterTemplate && $extra !== ''
            ? '<div style="margin-top:14px;"><strong>Ghi chú thêm:</strong><p style="margin:6px 0 0;">'.nl2br(e($extra)).'</p></div>'
            : '';

        $candidateName = $offer->application?->candidate?->name ?? '';

        $pdf = Pdf::loadView('pdf.offer-letter', [
            'offer' => $offer,
            'letterInnerHtml' => $letterBody.$additionalBlock,
            'candidateName' => $candidateName,
        ])->setPaper('a4');

        $relative = 'offers/'.$offer->id.'/offer-letter.pdf';
        Storage::disk('local')->makeDirectory('offers/'.$offer->id);

        $pdf->save(Storage::disk('local')->path($relative));

        $offer->forceFill(['pdf_path' => $relative])->save();
    }

    protected function buildFallbackBody(Offer $offer): string
    {
        $candidateName = e($offer->application?->candidate?->name ?? 'ứng viên');
        $jobTitle = e($offer->application?->job?->title ?? 'vị trí ứng tuyển');
        $salary = e(number_format((float) $offer->salary_offered, 0, ',', '.').' VND');
        $startDate = e($offer->start_date?->format('d/m/Y') ?? '-');
        $probation = e((string) ((int) ($offer->probation_months ?? 0)));
        $content = trim((string) $offer->content);

        return implode('', [
            "<p>Thân gửi <strong>{$candidateName}</strong>,</p>",
            "<p>FPT Career trân trọng gửi đến bạn đề nghị tuyển dụng cho vị trí <strong>{$jobTitle}</strong>.</p>",
            '<p>Thông tin chính của đề nghị:</p>',
            '<ul>',
            "<li><strong>Mức lương đề nghị:</strong> {$salary}</li>",
            "<li><strong>Ngày bắt đầu dự kiến:</strong> {$startDate}</li>",
            "<li><strong>Thời gian thử việc:</strong> {$probation} tháng</li>",
            '</ul>',
            $content !== ''
                ? '<div style="margin-top:14px;"><strong>Nội dung bổ sung:</strong><p style="margin:6px 0 0;">'.nl2br(e($content)).'</p></div>'
                : '',
        ]);
    }
}
