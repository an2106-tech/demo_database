<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApplicationPipelineService
{
    /**
     * @return array<string, array<int, StatusApplicationEnum>>
     */
    private function transitions(): array
    {
        return [
            StatusApplicationEnum::CV_REVIEWING->value => [
                StatusApplicationEnum::SCREENING,
                StatusApplicationEnum::REJECTED,
            ],
            StatusApplicationEnum::SCREENING->value => [
                StatusApplicationEnum::INTERVIEW_SCHEDULED,
                StatusApplicationEnum::REJECTED,
            ],
            StatusApplicationEnum::INTERVIEW_SCHEDULED->value => [
                StatusApplicationEnum::INTERVIEWING,
                StatusApplicationEnum::REJECTED,
            ],
            StatusApplicationEnum::INTERVIEWING->value => [
                StatusApplicationEnum::OFFERED,
                StatusApplicationEnum::REJECTED,
            ],
            StatusApplicationEnum::OFFERED->value => [
                StatusApplicationEnum::HIRED,
                StatusApplicationEnum::REJECTED,
            ],
            StatusApplicationEnum::HIRED->value => [],
            StatusApplicationEnum::REJECTED->value => [],
        ];
    }

    /**
     * @return array<int, StatusApplicationEnum>
     */
    public function allowedTransitions(StatusApplicationEnum|string|null $status): array
    {
        $status = $this->normalizeStatus($status);

        if (! $status) {
            return [];
        }

        return $this->transitions()[$status->value] ?? [];
    }

    public function canTransition(StatusApplicationEnum|string|null $from, StatusApplicationEnum|string|null $to): bool
    {
        $to = $this->normalizeStatus($to);

        if (! $to) {
            return false;
        }

        return in_array($to, $this->allowedTransitions($from), true);
    }

    public function transition(
        Application $application,
        StatusApplicationEnum|string $to,
        ?User $actor = null,
        ?string $comment = null,
    ): void {
        $targetStatus = $this->normalizeStatus($to);
        $currentStatus = $this->normalizeStatus($application->status);

        if (! $targetStatus || ! $this->canTransition($currentStatus, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => 'Không thể chuyển trạng thái ứng tuyển theo luồng này.',
            ]);
        }

        if ($targetStatus === StatusApplicationEnum::HIRED) {
            $acceptedOffer = $application->offers()
                ->where('status', 'accepted')
                ->whereNotNull('accepted_at')
                ->latest('id')
                ->first();

            if (! $acceptedOffer) {
                throw ValidationException::withMessages([
                    'status' => 'Chỉ có thể chuyển sang Đã tuyển sau khi ứng viên chấp nhận đề nghị tuyển dụng.',
                ]);
            }
        }

        $application->forceFill([
            'status' => $targetStatus,
        ])->save();

        if (filled($comment)) {
            $application->statusHistories()
                ->latest('id')
                ->first()
                ?->forceFill([
                    'changed_by_id' => $actor?->id,
                    'comment' => $comment,
                ])
                ->save();
        }
    }

    public function normalizeStatus(StatusApplicationEnum|string|null $status): ?StatusApplicationEnum
    {
        if ($status instanceof StatusApplicationEnum) {
            return $status;
        }

        if (! is_string($status) || trim($status) === '') {
            return null;
        }

        return StatusApplicationEnum::tryFrom($status);
    }
}
