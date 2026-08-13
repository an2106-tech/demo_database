<?php

namespace App\Services;

use App\Models\Offer;

class OfferLetterMergeService
{
    /**
     * @param  array<string, string>  $extra  Thêm placeholder tùy chỉnh (đã escape HTML nếu cần).
     */
    public function mergeTemplateBody(string $bodyHtml, Offer $offer, array $extra = []): string
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch']);

        $application = $offer->application;
        $candidate = $application?->candidate;
        $job = $application?->job;
        $branch = $job?->branch;

        $base = [
            '{{offer_id}}' => e((string) $offer->id),
            '{{issued_date}}' => e(optional($offer->approved_at ?? $offer->sent_at ?? $offer->created_at)->format('d/m/Y') ?? now()->format('d/m/Y')),
            '{{candidate_name}}' => e($application?->snapshotCandidateName() ?? $candidate?->name ?? ''),
            '{{candidate_email}}' => e($application?->snapshotCandidateEmail() ?? $candidate?->email ?? ''),
            '{{job_title}}' => e($job?->title ?? ''),
            '{{branch_name}}' => e($branch?->name ?? ''),
            '{{salary_offered}}' => e(number_format((float) $offer->salary_offered, 0, ',', '.').' VND'),
            '{{start_date}}' => e(optional($offer->start_date)->format('d/m/Y') ?? ''),
            '{{probation_months}}' => e((string) $offer->probation_months),
            '{{expiration_date}}' => e(optional($offer->expires_at)->format('d/m/Y H:i') ?? ''),
            '{{app_name}}' => e((string) config('app.name')),
        ];

        return strtr($bodyHtml, $extra + $base);
    }
}
