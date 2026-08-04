<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Scorecard;
use App\Models\ScorecardTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterviewEvaluationService
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
        private readonly ApplicationWorkflowGuard $workflowGuard,
    ) {}

    /**
     * @param  array<int, array{name?: mixed, score?: mixed, note?: mixed}>  $criteria
     * @return array<int, array{name: string, score: float, note: mixed}>
     */
    public function validateCriteria(array $criteria, bool $requireAllScores = true): array
    {
        if ($criteria === []) {
            throw ValidationException::withMessages([
                'criteria' => 'Vui lòng nhập ít nhất một tiêu chí chấm điểm.',
            ]);
        }

        return collect($criteria)
            ->values()
            ->map(function ($row, int $index) use ($requireAllScores): array {
                $position = $index + 1;

                if (! is_array($row)) {
                    throw ValidationException::withMessages([
                        'criteria' => "Tiêu chí #{$position} không hợp lệ.",
                    ]);
                }

                $name = trim((string) ($row['name'] ?? ''));
                $score = $row['score'] ?? null;

                if ($name === '') {
                    throw ValidationException::withMessages([
                        'criteria' => "Vui lòng nhập tên cho tiêu chí #{$position}.",
                    ]);
                }

                if (($score === null || $score === '') && ! $requireAllScores) {
                    return [
                        'name' => $name,
                        'score' => null,
                        'note' => filled($row['note'] ?? null) ? trim((string) $row['note']) : null,
                    ];
                }

                if ($score === null || $score === '' || ! is_numeric($score)) {
                    throw ValidationException::withMessages([
                        'criteria' => "Vui lòng nhập điểm hợp lệ cho tiêu chí #{$position}.",
                    ]);
                }

                $score = (float) $score;

                if ($score < 0 || $score > 10) {
                    throw ValidationException::withMessages([
                        'criteria' => "Điểm của tiêu chí #{$position} phải nằm trong khoảng 0-10.",
                    ]);
                }

                return [
                    'name' => $name,
                    'score' => $score,
                    'note' => filled($row['note'] ?? null) ? trim((string) $row['note']) : null,
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array{score?: mixed}>  $criteria
     */
    public function calculateAverage(array $criteria): ?float
    {
        $scores = collect($criteria)
            ->map(fn ($row) => is_array($row) ? ($row['score'] ?? null) : null)
            ->filter(fn ($score) => $score !== null && $score !== '')
            ->map(fn ($score) => (float) $score);

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    public function recommendedConclusion(?float $average): ?string
    {
        if ($average === null) {
            return null;
        }

        return match (true) {
            $average >= 7 => 'pass',
            $average >= 5 => 'hold',
            default => 'fail',
        };
    }

    public function conclusionLabel(?string $conclusion): string
    {
        return match ($conclusion) {
            'pass' => 'Đạt phỏng vấn',
            'fail' => 'Không đạt phỏng vấn',
            'hold' => 'Cân nhắc thêm',
            default => 'Chưa kết luận',
        };
    }

    public function isConclusionOverride(?string $conclusion, ?string $recommendedConclusion): bool
    {
        return filled($conclusion)
            && filled($recommendedConclusion)
            && $conclusion !== $recommendedConclusion;
    }

    public function buildComment(
        string $conclusion,
        ?float $average,
        ?string $notes = null,
        ?string $recommendedConclusion = null,
        ?string $overrideReason = null,
    ): string {
        $scoreText = $average !== null ? ' Điểm trung bình: '.number_format($average, 2, ',', '.').'/10.' : '';
        $recommendationText = $recommendedConclusion !== null
            ? ' Khuyến nghị: '.$this->conclusionLabel($recommendedConclusion).'.'
            : '';
        $overrideText = $this->isConclusionOverride($conclusion, $recommendedConclusion) && filled($overrideReason)
            ? ' Lý do quyết định khác khuyến nghị: '.trim((string) $overrideReason).'.'
            : '';
        $noteText = filled($notes) ? ' Nhận xét: '.trim((string) $notes) : '';

        return 'Đánh giá phỏng vấn: '.$this->conclusionLabel($conclusion).'.'.$scoreText.$recommendationText.$overrideText.$noteText;
    }

    /**
     * @param  array{template_id?: mixed, criteria?: mixed, conclusion?: mixed, notes?: mixed, override_reason?: mixed, rejected_reason?: mixed}  $data
     * @return array{scorecard_id: int, average: ?float, is_complete: bool, saved: bool}
     */
    public function saveDraft(Application $application, array $data, ?User $actor): array
    {
        if (! $this->workflowGuard->canEvaluateInterview($actor, $application)) {
            throw ValidationException::withMessages([
                'interview' => 'Chưa thể ghi nhận đánh giá. Buổi phỏng vấn chưa bắt đầu hoặc tài khoản không có quyền thực hiện.',
            ]);
        }

        /** @var Interview|null $interview */
        $interview = $application->interviews()->latest('id')->first();
        if (! $interview) {
            throw ValidationException::withMessages([
                'interview' => 'Chưa có lịch phỏng vấn để đánh giá.',
            ]);
        }

        [$template, $criteria] = $this->templateCriteria($data, false);
        $average = $this->calculateAverage($criteria);
        $notes = filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null;
        $existingScorecard = Scorecard::withTrashed()
            ->where('interview_id', $interview->id)
            ->where('evaluator_id', (int) $actor?->id)
            ->first();

        $unchanged = $existingScorecard
            && ! $existingScorecard->trashed()
            && $existingScorecard->conclusion === null
            && (int) $existingScorecard->template_id === (int) $template->id
            && json_encode($existingScorecard->criteria) === json_encode($criteria)
            && trim((string) $existingScorecard->notes) === (string) $notes;

        $scorecard = $unchanged
            ? $existingScorecard
            : $this->persistScorecard(
                $application,
                $interview,
                $actor,
                $template,
                $criteria,
                $average,
                null,
                null,
                null,
                $notes,
            );

        return [
            'scorecard_id' => $scorecard->id,
            'average' => $average,
            'is_complete' => ! collect($criteria)->contains(fn (array $criterion): bool => $criterion['score'] === null),
            'saved' => ! $unchanged,
        ];
    }

    /**
     * @param  array{template_id?: mixed, criteria?: mixed, conclusion?: mixed, notes?: mixed, override_reason?: mixed, rejected_reason?: mixed}  $data
     * @return array{conclusion: string, average: ?float, recommended_conclusion: ?string}
     */
    public function complete(Application $application, array $data, ?User $actor): array
    {
        $confirmedEarlyCompletion = (bool) ($data['confirm_early_completion'] ?? false);

        if (! $this->workflowGuard->canFinalizeInterviewEvaluation($actor, $application, $confirmedEarlyCompletion)) {
            throw ValidationException::withMessages([
                'interview' => 'Chỉ có thể hoàn tất đánh giá sau khi buổi phỏng vấn kết thúc và với tài khoản có quyền thực hiện.',
            ]);
        }

        /** @var Interview|null $interview */
        $interview = $application->interviews()->latest('id')->first();
        if (! $interview) {
            throw ValidationException::withMessages([
                'interview' => 'Chưa có lịch phỏng vấn để hoàn tất đánh giá.',
            ]);
        }

        $scheduledEnd = $interview->scheduled_at
            ? $interview->scheduled_at->copy()->addMinutes(max(1, (int) $interview->duration_minutes))
            : null;
        $finishedEarly = $confirmedEarlyCompletion
            && ! $interview->actual_ended_at
            && $scheduledEnd?->gt(now($interview->scheduled_at?->getTimezone()));

        [$template, $criteria] = $this->templateCriteria($data, true);
        $average = $this->calculateAverage($criteria);
        $recommendedConclusion = $this->recommendedConclusion($average);
        $conclusion = (string) ($data['conclusion'] ?? '');

        if (! in_array($conclusion, ['pass', 'hold', 'fail'], true)) {
            throw ValidationException::withMessages([
                'conclusion' => 'Vui lòng chọn kết luận phỏng vấn hợp lệ.',
            ]);
        }

        $overrideReason = trim((string) ($data['override_reason'] ?? ''));
        if ($this->isConclusionOverride($conclusion, $recommendedConclusion) && $overrideReason === '') {
            throw ValidationException::withMessages([
                'override_reason' => 'Vui lòng nhập lý do khi kết luận cuối khác khuyến nghị từ điểm số.',
            ]);
        }

        $rejectedReason = trim((string) ($data['rejected_reason'] ?? ''));
        if ($conclusion === 'fail' && $rejectedReason === '') {
            throw ValidationException::withMessages([
                'rejected_reason' => 'Vui lòng nhập lý do từ chối khi kết luận không đạt.',
            ]);
        }

        $comment = $this->buildComment(
            $conclusion,
            $average,
            $data['notes'] ?? null,
            $recommendedConclusion,
            $overrideReason,
        );

        DB::transaction(function () use ($application, $interview, $actor, $template, $criteria, $average, $recommendedConclusion, $conclusion, $data, $overrideReason, $rejectedReason, $comment, $finishedEarly): void {
            $this->persistScorecard(
                $application,
                $interview,
                $actor,
                $template,
                $criteria,
                $average,
                $recommendedConclusion,
                $conclusion,
                $overrideReason,
                $data['notes'] ?? null,
            );

            $interview->forceFill([
                'result' => $conclusion === 'hold' ? 'pending' : $conclusion,
                'actual_ended_at' => $finishedEarly
                    ? now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                    : $interview->actual_ended_at,
            ])->save();

            $currentStatus = $this->pipelineService->normalizeStatus($application->status);
            $transitionedToInterview = false;
            if ($currentStatus === StatusApplicationEnum::INTERVIEW_SCHEDULED) {
                $this->pipelineService->transition(
                    $application,
                    StatusApplicationEnum::INTERVIEWING,
                    $actor,
                    $conclusion === 'hold' ? $comment : 'Đã hoàn tất đánh giá phỏng vấn trước khi chuyển bước tiếp theo.',
                );
                $application->refresh();
                $transitionedToInterview = true;
            }

            if ($conclusion === 'pass') {
                $application->forceFill(['rejected_reason' => null])->save();
                $this->pipelineService->transition($application, StatusApplicationEnum::OFFERED, $actor, $comment);
            } elseif ($conclusion === 'fail') {
                $application->forceFill([
                    'rejected_stage' => 'interview',
                    'rejected_reason' => $rejectedReason,
                ])->save();
                $this->pipelineService->transition($application, StatusApplicationEnum::REJECTED, $actor, $comment.' Lý do từ chối: '.$rejectedReason);
            } elseif (! $transitionedToInterview) {
                $application->recordStatusHistory(
                    StatusApplicationEnum::INTERVIEWING->value,
                    StatusApplicationEnum::INTERVIEWING->value,
                    $comment,
                );
            }
        });

        return [
            'conclusion' => $conclusion,
            'average' => $average,
            'recommended_conclusion' => $recommendedConclusion,
        ];
    }

    /**
     * Compatibility entry point for existing callers that complete an evaluation.
     *
     * @param  array{template_id?: mixed, criteria?: mixed, conclusion?: mixed, notes?: mixed, override_reason?: mixed, rejected_reason?: mixed}  $data
     * @return array{conclusion: string, average: ?float, recommended_conclusion: ?string}
     */
    public function evaluate(Application $application, array $data, ?User $actor): array
    {
        return $this->complete($application, $data, $actor);
    }

    /**
     * @param  array{template_id?: mixed, criteria?: mixed}  $data
     * @return array{0: ScorecardTemplate, 1: array<int, array{name: string, score: float|null, note: string|null}>}
     */
    private function templateCriteria(array $data, bool $requireAllScores): array
    {
        $templateId = $data['template_id'] ?? null;
        if (! filled($templateId) || ! ($template = ScorecardTemplate::query()->find($templateId))) {
            throw ValidationException::withMessages([
                'template_id' => 'Vui lòng chọn mẫu scorecard hợp lệ trước khi đánh giá.',
            ]);
        }

        $templateCriteria = array_values((array) $template->criteria);
        if ($templateCriteria === []) {
            throw ValidationException::withMessages([
                'template_id' => 'Mẫu scorecard này chưa có tiêu chí đánh giá.',
            ]);
        }

        $submittedCriteria = array_values((array) ($data['criteria'] ?? []));
        if (count($submittedCriteria) !== count($templateCriteria)) {
            throw ValidationException::withMessages([
                'criteria' => 'Tiêu chí đánh giá phải đầy đủ theo mẫu scorecard đã chọn.',
            ]);
        }

        $criteria = collect($templateCriteria)->values()->map(function ($templateCriterion, int $index) use ($submittedCriteria): array {
            $templateCriterion = is_array($templateCriterion) ? $templateCriterion : [];
            $submittedCriterion = is_array($submittedCriteria[$index] ?? null) ? $submittedCriteria[$index] : [];

            return [
                'name' => trim((string) ($templateCriterion['name'] ?? 'Tiêu chí '.($index + 1))),
                'score' => $submittedCriterion['score'] ?? null,
                'note' => $submittedCriterion['note'] ?? null,
            ];
        })->all();

        return [$template, $this->validateCriteria($criteria, $requireAllScores)];
    }

    /**
     * @param  array<int, array{name: string, score: float|null, note: string|null}>  $criteria
     */
    private function persistScorecard(
        Application $application,
        Interview $interview,
        ?User $actor,
        ScorecardTemplate $template,
        array $criteria,
        ?float $average,
        ?string $recommendedConclusion,
        ?string $conclusion,
        ?string $overrideReason,
        ?string $notes = null,
    ): Scorecard {
        $scorecard = Scorecard::withTrashed()->firstOrNew([
            'interview_id' => $interview->id,
            'evaluator_id' => (int) $actor?->id,
        ]);

        if ($scorecard->trashed()) {
            $scorecard->restore();
        }

        $scorecard->fill([
            'application_id' => $application->id,
            'interview_id' => $interview->id,
            'template_id' => $template->id,
            'evaluator_id' => (int) $actor?->id,
            'criteria' => $criteria,
            'average_score' => $average,
            'recommended_conclusion' => $recommendedConclusion,
            'notes' => filled($notes) ? trim($notes) : null,
            'override_reason' => $overrideReason,
            'conclusion' => $conclusion,
        ])->save();

        return $scorecard;
    }
}
