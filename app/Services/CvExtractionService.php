<?php

namespace App\Services;

use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Models\CvExtraction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CvExtractionService
{
    public function __construct(
        private CvTextExtractor $extractor,
    ) {}

    public function ensureForApplication(Application $application): ?CvExtraction
    {
        $application->loadMissing(['cvAttachment']);

        $path = $application->submittedCvPath();
        if (blank($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($path);
        $hash = hash_file('sha256', $absolutePath);

        if (! is_string($hash) || $hash === '') {
            return null;
        }

        $attachment = $application->cvAttachment;
        $extraction = CvExtraction::query()->firstOrNew(['cv_hash' => $hash]);

        $extraction->fill([
            'file_path' => $path,
            'original_filename' => $application->submittedCvName(),
            'mime_type' => $attachment?->mime_type,
            'size_bytes' => $attachment?->size_bytes ?: @filesize($absolutePath) ?: null,
        ]);

        if ($extraction->exists
            && $extraction->status === 'completed'
            && filled($extraction->extracted_text)
        ) {
            $extraction->save();
            $this->syncApplicationText($application, $extraction->extracted_text);

            return $extraction;
        }

        $extraction->forceFill([
            'status' => 'processing',
            'error_message' => null,
        ])->save();

        try {
            $text = $this->extractor->extractFromPublicPath($path);

            if (blank($text)) {
                $extraction->forceFill([
                    'status' => 'failed',
                    'extracted_text' => null,
                    'error_message' => 'Không trích xuất được nội dung từ CV. File có thể là ảnh scan hoặc định dạng chưa hỗ trợ.',
                    'extracted_at' => now(),
                ])->save();

                return $extraction;
            }

            $extraction->forceFill([
                'status' => 'completed',
                'extracted_text' => trim((string) $text),
                'error_message' => null,
                'extracted_at' => now(),
            ])->save();

            $this->syncApplicationText($application, $extraction->extracted_text);

            return $extraction;
        } catch (\Throwable $exception) {
            Log::warning('CV extraction service failed.', [
                'application_id' => $application->id,
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            $extraction->forceFill([
                'status' => 'failed',
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                'extracted_at' => now(),
            ])->save();

            return $extraction;
        }
    }

    protected function syncApplicationText(Application $application, ?string $text): void
    {
        if (blank($text)) {
            return;
        }

        $text = (string) $text;
        $application->forceFill([
            'cv_text_snapshot' => mb_substr($text, 0, 200000),
        ])->save();

        CandidateJobSubmission::query()
            ->where('job_id', $application->job_id)
            ->where('candidate_id', $application->candidate_id)
            ->update([
                'cv_text_snapshot' => mb_substr($text, 0, 200000),
            ]);
    }
}
