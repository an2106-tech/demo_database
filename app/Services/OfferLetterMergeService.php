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
            '{{candidate_name}}' => e($candidate?->name ?? ''),
            '{{job_title}}' => e($job?->title ?? ''),
            '{{branch_name}}' => e($branch?->name ?? ''),
            '{{salary_offered}}' => e(number_format((float) $offer->salary_offered, 0, ',', '.').' VND'),
            '{{start_date}}' => e(optional($offer->start_date)->format('d/m/Y') ?? ''),
            '{{probation_months}}' => e((string) $offer->probation_months),
            '{{app_name}}' => e((string) config('app.name')),
        ];

        return strtr($bodyHtml, $extra + $base);
    }
}
