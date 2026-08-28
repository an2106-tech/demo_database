<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Interview;
use Illuminate\Validation\ValidationException;

class InterviewRoundWorkflowService
{
    public function __construct(
        private readonly InterviewProcessTemplateService $processTemplates,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rounds(Application $application): array
    {
        $application->loadMissing('job');

        $process = $application->job
            ? $this->processTemplates->resolveForJob($application->job)
            : $this->processTemplates->singleRoundFallback();

        return collect((array) ($process['rounds'] ?? []))
            ->values()
            ->map(function ($round, int $index): array {
                $round = is_array($round) ? $round : [];
                $number = (int) ($round['round_number'] ?? $index + 1);

                return array_merge($round, [
                    'round_number' => max(1, $number),
                    'name' => trim((string) ($round['name'] ?? '')) ?: 'Phỏng vấn vòng '.($index + 1),
                ]);
            })
            ->sortBy('round_number')
            ->values()
            ->all();
    }

    /**
     * Resolve the one interview that can be created or edited now.
     *
     * @return array{interview: ?Interview, round: array<string, mixed>, round_number: int, total_rounds: int, is_update: bool, is_final_round: bool}
     */
    public function schedulingContext(Application $application): array
    {
        $rounds = $this->rounds($application);

        if ($rounds === []) {
            throw ValidationException::withMessages([
                'interview' => 'Quy trình tuyển dụng chưa có vòng phỏng vấn hợp lệ.',
            ]);
        }

        $latest = $this->latestInterview($application);
        $targetNumber = 1;
        $editableInterview = null;

        if ($latest) {
            if ($latest->result === 'pending' && ! $latest->finalized_at) {
                $targetNumber = (int) $latest->round_number;
                $editableInterview = $latest;
            } elseif ($latest->result === 'pass' && $latest->finalized_at) {
                $targetNumber = (int) $latest->round_number + 1;
            } else {
                throw ValidationException::withMessages([
                    'interview' => 'Vòng phỏng vấn hiện tại chưa được chốt ở trạng thái có thể chuyển tiếp.',
                ]);
            }
        }

        $round = collect($rounds)->first(
            fn (array $item): bool => (int) $item['round_number'] === $targetNumber,
        );

        if (! is_array($round)) {
            throw ValidationException::withMessages([
                'interview' => 'Ứng viên đã hoàn tất vòng phỏng vấn cuối cùng.',
            ]);
        }

        if ($editableInterview) {
            $round['name'] = $editableInterview->round_name ?: $round['name'];

            if ($editableInterview->scorecard_template_id && is_array($editableInterview->scorecard_template_snapshot)) {
                $round['scorecard_template'] = array_merge(
                    $editableInterview->scorecard_template_snapshot,
                    ['id' => (int) $editableInterview->scorecard_template_id],
                );
            }
        }

        $scorecard = is_array($round['scorecard_template'] ?? null)
            ? $round['scorecard_template']
            : null;

        if (! $scorecard || empty($scorecard['id']) || empty($scorecard['criteria'])) {
            throw ValidationException::withMessages([
                'scorecard_template_id' => 'Vòng '.$targetNumber.' chưa được gắn mẫu đánh giá hợp lệ.',
            ]);
        }

        return [
            'interview' => $editableInterview,
            'round' => $round,
            'round_number' => $targetNumber,
            'total_rounds' => count($rounds),
            'is_update' => $editableInterview !== null,
            'is_final_round' => $targetNumber === (int) collect($rounds)->max('round_number'),
        ];
    }

    public function latestInterview(Application $application): ?Interview
    {
        if ($application->relationLoaded('latestInterview')) {
            return $application->latestInterview;
        }

        return $application->interviews()
            ->orderByDesc('round_number')
            ->orderByDesc('id')
            ->first();
    }

    public function isFinalRound(Application $application, Interview $interview): bool
    {
        $lastRound = collect($this->rounds($application))->max('round_number');

        return (int) $interview->round_number >= (int) $lastRound;
    }

    /**
     * @return array<int, string>
     */
    public function evaluatorRolesForRound(Application $application, int $roundNumber): array
    {
        $application->loadMissing('job');
        $job = $application->job;
        $snapshot = is_array($job?->interview_process_snapshot)
            ? $job->interview_process_snapshot
            : [];

        if (empty($snapshot['rounds']) && blank($job?->interview_process_template_id)) {
            return ['director', 'pm', 'hr'];
        }

        $round = collect($this->rounds($application))->first(
            fn (array $item): bool => (int) $item['round_number'] === $roundNumber,
        );

        $roles = collect((array) ($round['evaluator_roles'] ?? []))
            ->map(fn ($role): string => strtolower(trim((string) $role)))
            ->filter(fn (string $role): bool => in_array($role, ['director', 'pm', 'hr'], true))
            ->unique()
            ->values()
            ->all();

        return $roles !== [] ? $roles : ['director', 'pm', 'hr'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function nextRound(Application $application, Interview $interview): ?array
    {
        $number = (int) $interview->round_number + 1;

        return collect($this->rounds($application))->first(
            fn (array $round): bool => (int) $round['round_number'] === $number,
        );
    }
}
