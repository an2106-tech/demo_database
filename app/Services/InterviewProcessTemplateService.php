<?php

namespace App\Services;

use App\Models\InterviewProcessTemplate;
use App\Models\RecruitmentJob;
use App\Models\ScorecardTemplate;
use Illuminate\Validation\ValidationException;

class InterviewProcessTemplateService
{
    /**
     * @return array<string, mixed>
     */
    public function snapshotFromTemplateId(int $templateId): array
    {
        $template = InterviewProcessTemplate::query()
            ->with(['rounds.scorecardTemplate'])
            ->find($templateId);

        if (! $template || ! $template->is_active) {
            throw ValidationException::withMessages([
                'interview_process_template_id' => 'Quy trình phỏng vấn đã chọn không còn được áp dụng.',
            ]);
        }

        if ($template->rounds->isEmpty()) {
            throw ValidationException::withMessages([
                'interview_process_template_id' => 'Quy trình phỏng vấn chưa có vòng đánh giá.',
            ]);
        }

        return [
            'version' => 1,
            'template_id' => $template->id,
            'code' => $template->code,
            'name' => $template->name,
            'description' => $template->description,
            'round_count' => $template->rounds->count(),
            'rounds' => $template->rounds
                ->values()
                ->map(fn ($round): array => [
                    'round_number' => (int) $round->round_number,
                    'name' => $round->name,
                    'candidate_label' => $round->candidate_label ?: $round->name,
                    'objective' => $round->objective,
                    'evaluator_roles' => array_values((array) $round->evaluator_roles),
                    'scorecard_template' => $this->scorecardSnapshot($round->scorecardTemplate),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveForJob(RecruitmentJob $job): array
    {
        $snapshot = is_array($job->interview_process_snapshot)
            ? $job->interview_process_snapshot
            : [];

        if (! empty($snapshot['rounds']) && is_array($snapshot['rounds'])) {
            return $snapshot;
        }

        return $this->singleRoundFallback();
    }

    /**
     * @return array<string, mixed>
     */
    public function singleRoundFallback(): array
    {
        $scorecard = ScorecardTemplate::query()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        return [
            'version' => 1,
            'template_id' => null,
            'code' => 'legacy-single-round',
            'name' => 'Quy trình phỏng vấn tiêu chuẩn',
            'description' => 'Quy trình tương thích dành cho tin tuyển dụng chưa chọn mẫu phỏng vấn.',
            'round_count' => 1,
            'rounds' => [[
                'round_number' => 1,
                'name' => 'Phỏng vấn và đánh giá',
                'candidate_label' => 'Phỏng vấn với đơn vị tuyển dụng',
                'objective' => 'Đánh giá năng lực chuyên môn và mức độ phù hợp với đơn vị tuyển dụng.',
                'evaluator_roles' => ['hr', 'pm'],
                'scorecard_template' => $this->scorecardSnapshot($scorecard),
            ]],
        ];
    }

    /**
     * @return array{round_count: int, rounds: array<int, array{number: int, label: string}>}
     */
    public function publicSummaryForJob(RecruitmentJob $job): array
    {
        $process = $this->resolveForJob($job);
        $rounds = collect($process['rounds'] ?? [])
            ->values()
            ->map(fn (array $round, int $index): array => [
                'number' => (int) ($round['round_number'] ?? $index + 1),
                'label' => (string) (
                    $round['candidate_label']
                    ?? $round['name']
                    ?? 'Phỏng vấn với đơn vị tuyển dụng'
                ),
            ])
            ->all();

        if ($rounds === []) {
            $rounds = [[
                'number' => 1,
                'label' => 'Phỏng vấn với đơn vị tuyển dụng',
            ]];
        }

        return [
            'round_count' => count($rounds),
            'rounds' => $rounds,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function scorecardSnapshot(?ScorecardTemplate $template): ?array
    {
        if (! $template) {
            return null;
        }

        return [
            'id' => $template->id,
            'name' => $template->name,
            'criteria' => array_values((array) $template->criteria),
        ];
    }
}
