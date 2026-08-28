<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Services\CvTextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessApplicationCvText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $applicationId) {}

    public function handle(CvTextExtractor $extractor): void
    {
        $application = Application::query()->find($this->applicationId);
        $cvPath = $application?->submittedCvPath();

        if (! $application || blank($cvPath)) {
            return;
        }

        $text = $extractor->extractFromPublicPath($cvPath);
        $snapshot = is_string($text) && trim($text) !== ''
            ? mb_substr($text, 0, 200000)
            : null;

        $application->forceFill(['cv_text_snapshot' => $snapshot])->save();

        CandidateJobSubmission::query()
            ->where('job_id', $application->job_id)
            ->where('candidate_id', $application->candidate_id)
            ->update(['cv_text_snapshot' => $snapshot]);
    }
}
