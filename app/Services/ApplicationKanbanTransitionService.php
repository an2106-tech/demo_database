<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\User;

class ApplicationKanbanTransitionService
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
        private readonly ApplicationWorkflowGuard $workflowGuard,
    ) {}

    /**
     * @return array{allowed: bool, target_status: ?string, requires: ?string, message: string}
     */
    public function evaluateStageMove(Application $application, string $targetStage, ?User $actor): array
    {
        $currentStatus = $this->pipelineService->normalizeStatus($application->status);

        if (! $currentStatus) {
            return $this->blocked('Hồ sơ chưa có trạng thái hợp lệ.');
        }

        if (! $this->workflowGuard->canRunHrPipelineActions($actor)) {
            return $this->blocked('Tài khoản hiện tại không có quyền xử lý quy trình tuyển dụng.');
        }

        if (! $this->workflowGuard->canAccessApplicationBranch($actor, $application)) {
            return $this->blocked('Hồ sơ không thuộc phạm vi chi nhánh của tài khoản hiện tại.');
        }

        if ($currentStatus->getPipelineStageKey() === $targetStage) {
            return $this->blocked('Hồ sơ đã ở giai đoạn này.');
        }

        if (in_array($currentStatus, [StatusApplicationEnum::HIRED, StatusApplicationEnum::REJECTED], true)) {
            return $this->blocked('Hồ sơ đã kết thúc quy trình, không thể kéo sang giai đoạn khác.');
        }

        if ($targetStage === StatusApplicationEnum::REJECTED->getPipelineStageKey()) {
            if (! $this->workflowGuard->canRejectApplication($actor, $application)) {
                return $this->blocked('Không thể từ chối hồ sơ ở trạng thái hiện tại.');
            }

            return $this->requires(
                StatusApplicationEnum::REJECTED,
                'rejection_reason',
                'Cần nhập lý do từ chối trước khi dừng hồ sơ.',
            );
        }

        if (
            $targetStage === StatusApplicationEnum::OFFERED->getPipelineStageKey()
            && in_array($currentStatus, [StatusApplicationEnum::INTERVIEW_SCHEDULED, StatusApplicationEnum::INTERVIEWING], true)
        ) {
            return $this->requires(
                StatusApplicationEnum::OFFERED,
                'interview_evaluation',
                'Cần ghi nhận đánh giá phỏng vấn trước khi tạo đề nghị tuyển dụng.',
            );
        }

        $targetStatus = $this->targetStatusForStage($targetStage);

        if (! $targetStatus) {
            return $this->blocked('Giai đoạn Kanban không hợp lệ.');
        }

        if (! $this->pipelineService->canTransition($currentStatus, $targetStatus)) {
            return $this->blocked('Không thể chuyển hồ sơ sang giai đoạn này theo quy trình hiện tại.');
        }

        return match ($targetStatus) {
            StatusApplicationEnum::SCREENING => $this->requires(
                $targetStatus,
                'cv_screening',
                'Cần ghi nhận kết quả sàng lọc CV trước khi chuyển sang sơ tuyển.',
            ),
            StatusApplicationEnum::INTERVIEW_SCHEDULED => $this->requires(
                $targetStatus,
                'interview_schedule',
                'Cần tạo lịch phỏng vấn trước khi chuyển sang giai đoạn phỏng vấn.',
            ),
            StatusApplicationEnum::INTERVIEWING => $this->requires(
                $targetStatus,
                'interview_evaluation',
                'Cần ghi nhận đánh giá phỏng vấn trước khi chuyển tiếp.',
            ),
            StatusApplicationEnum::OFFERED => $this->requires(
                $targetStatus,
                'offer_draft',
                'Cần tạo đề nghị tuyển dụng trước khi chuyển sang giai đoạn đề nghị.',
            ),
            StatusApplicationEnum::HIRED => $this->evaluateHiredMove($application, $targetStatus),
            default => $this->blocked('Giai đoạn này chưa hỗ trợ thao tác kéo thả.'),
        };
    }

    private function targetStatusForStage(string $targetStage): ?StatusApplicationEnum
    {
        return match ($targetStage) {
            'screening' => StatusApplicationEnum::SCREENING,
            'interview' => StatusApplicationEnum::INTERVIEW_SCHEDULED,
            'offer' => StatusApplicationEnum::OFFERED,
            'hired' => StatusApplicationEnum::HIRED,
            default => null,
        };
    }

    /**
     * @return array{allowed: bool, target_status: ?string, requires: ?string, message: string}
     */
    private function evaluateHiredMove(Application $application, StatusApplicationEnum $targetStatus): array
    {
        $offer = $application->latestOffer;

        if ($offer?->status !== 'accepted') {
            return $this->blocked('Chỉ chuyển sang đã tuyển khi ứng viên đã đồng ý đề nghị tuyển dụng.');
        }

        return [
            'allowed' => true,
            'target_status' => $targetStatus->value,
            'requires' => null,
            'message' => 'Có thể hoàn tất tuyển dụng cho hồ sơ này.',
        ];
    }

    /**
     * @return array{allowed: bool, target_status: ?string, requires: ?string, message: string}
     */
    private function requires(StatusApplicationEnum $targetStatus, string $requirement, string $message): array
    {
        return [
            'allowed' => false,
            'target_status' => $targetStatus->value,
            'requires' => $requirement,
            'message' => $message,
        ];
    }

    /**
     * @return array{allowed: bool, target_status: ?string, requires: ?string, message: string}
     */
    private function blocked(string $message): array
    {
        return [
            'allowed' => false,
            'target_status' => null,
            'requires' => null,
            'message' => $message,
        ];
    }
}
