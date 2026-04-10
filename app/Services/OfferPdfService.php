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

        if (! $offer->offer_letter_template_id || ! $offer->letterTemplate) {
            if (filled($offer->pdf_path)) {
                Storage::disk('local')->delete($offer->pdf_path);
                $offer->forceFill(['pdf_path' => null])->save();
            }

            return;
        }

        $merged = $this->merge->mergeTemplateBody($offer->letterTemplate->body_html, $offer);
        $extra = trim((string) $offer->content);
        $additionalBlock = $extra !== ''
            ? '<div style="margin-top:14px;"><strong>Ghi chú thêm:</strong><p style="margin:6px 0 0;">'.nl2br(e($extra)).'</p></div>'
            : '';

        $candidateName = $offer->application?->candidate?->name ?? '';

        $pdf = Pdf::loadView('pdf.offer-letter', [
            'letterInnerHtml' => $merged.$additionalBlock,
            'candidateName' => $candidateName,
        ])->setPaper('a4');

        $relative = 'offers/'.$offer->id.'/offer-letter.pdf';
        Storage::disk('local')->makeDirectory('offers/'.$offer->id);

        $fullPath = Storage::disk('local')->path($relative);
        $pdf->save($fullPath);

        $offer->forceFill(['pdf_path' => $relative])->save();
    }
}
