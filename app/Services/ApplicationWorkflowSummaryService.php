<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Offer;
use Carbon\CarbonInterface;

class ApplicationWorkflowSummaryService
{
    /**
     * @return array{stage_label: string, status_label: string, description: string, color: string, is_terminal: bool}
     */
    public function summarize(Application $application): array
    {
        $status = $this->status($application);

        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => $this->summary(
                $status,
                'Chờ sàng lọc CV',
                'Đối chiếu CV với vị trí ứng tuyển trước khi chuyển bước.',
                'gray',
            ),
            StatusApplicationEnum::SCREENING => $this->screeningSummary($application, $status),
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::INTERVIEWING => $this->interviewSummary($application, $status),
            StatusApplicationEnum::OFFERED => $this->offerSummary($application, $status),
            StatusApplicationEnum::HIRED => $this->hiredSummary($application, $status),
            StatusApplicationEnum::REJECTED => $this->rejectedSummary($application, $status),
            default => [
                'stage_label' => 'Chưa xác định',
                'status_label' => 'Trạng thái không hợp lệ',
                'description' => 'Hồ sơ chưa có trạng thái tuyển dụng hợp lệ.',
                'color' => 'gray',
                'is_terminal' => false,
            ],
        };
    }

    private function screeningSummary(Application $application, StatusApplicationEnum $status): array
    {
        $interview = $this->latestInterview($application);

        if ($interview) {
            return $this->summary(
                $status,
                blank($interview->invite_sent_at) ? 'Cần gửi thư mời' : 'Đã gửi thư mời',
                blank($interview->invite_sent_at)
                    ? 'Lịch đã tạo, cần gửi thư mời cho ứng viên và người liên quan.'
                    : 'Ứng viên đã nhận thông tin lịch phỏng vấn.',
                blank($interview->invite_sent_at) ? 'warning' : 'info',
            );
        }

        return $this->summary(
            $status,
            'Cần tạo lịch phỏng vấn',
            'Hồ sơ đã qua sàng lọc, cần sắp xếp lịch phỏng vấn.',
            'info',
        );
    }

    private function interviewSummary(Application $application, StatusApplicationEnum $status): array
    {
        $interview = $this->latestInterview($application);

        if (! $interview) {
            return $this->summary(
                $status,
                'Chưa có lịch phỏng vấn',
                'Hồ sơ đang ở giai đoạn phỏng vấn nhưng chưa tìm thấy lịch phỏng vấn.',
                'warning',
            );
        }

        if (blank($interview->invite_sent_at)) {
            return $this->summary(
                $status,
                'Chưa gửi thư mời',
                'Lịch đã tạo, cần gửi thư mời cho ứng viên và người liên quan.',
                'warning',
            );
        }

        if (($interview->result ?? 'pending') !== 'pending') {
            return $this->summary(
                $status,
                'Đã có kết quả phỏng vấn',
                'Kết quả đã được ghi nhận, cần quyết định bước tiếp theo.',
                'primary',
            );
        }

        if ($interview->scheduled_at?->lte(now())) {
            return $this->summary(
                $status,
                'Cần chấm phỏng vấn',
                'Buổi phỏng vấn đã đến hạn, cần ghi nhận scorecard.',
                'danger',
            );
        }

        return $this->summary(
            $status,
            'Chờ đến lịch phỏng vấn',
            'Thư mời đã gửi, hồ sơ đang chờ buổi phỏng vấn.',
            'warning',
        );
    }

    private function offerSummary(Application $application, StatusApplicationEnum $status): array
    {
        $offer = $this->latestOffer($application);

        if (! $offer) {
            return $this->summary(
                $status,
                'Cần tạo đề nghị',
                'Ứng viên đã qua đánh giá, cần tạo đề nghị tuyển dụng.',
                'warning',
            );
        }

        if ($this->isExpiredOffer($offer)) {
            return $this->summary(
                $status,
                'Hết hạn phản hồi',
                'Đề nghị đã quá hạn phản hồi, cần quyết định xử lý tiếp.',
                'gray',
            );
        }

        return match ($offer->status) {
            'draft' => $this->summary(
                $status,
                'Đang soạn đề nghị',
                'Bản nháp cần được gửi giám đốc chi nhánh duyệt.',
                'warning',
            ),
            'awaiting_approval' => $this->summary(
                $status,
                'Chờ giám đốc duyệt',
                'Đề nghị đang chờ quyết định từ giám đốc chi nhánh.',
                'warning',
            ),
            'rejected' => $this->summary(
                $status,
                'Giám đốc yêu cầu chỉnh sửa',
                $offer->approval_notes
                    ? 'Lý do: '.trim((string) $offer->approval_notes)
                    : 'Đề nghị bị từ chối duyệt, cần kiểm tra và chỉnh sửa.',
                'danger',
            ),
            'pending' => $this->summary(
                $status,
                $offer->sent_at ? 'Chờ ứng viên phản hồi' : 'Chưa gửi thư mời',
                $offer->sent_at
                    ? 'Đề nghị đã gửi cho ứng viên, đang chờ phản hồi.'
                    : 'Đề nghị đã duyệt nhưng chưa ghi nhận thời điểm gửi.',
                'success',
            ),
            'accepted' => $this->summary(
                $status,
                'Đã đồng ý đề nghị',
                'Ứng viên đã xác nhận đồng ý đề nghị tuyển dụng.',
                'success',
            ),
            'declined' => $this->summary(
                $status,
                'Từ chối đề nghị',
                $offer->declined_reason
                    ? 'Lý do: '.trim((string) $offer->declined_reason)
                    : 'Ứng viên đã từ chối đề nghị tuyển dụng.',
                'danger',
            ),
            'expired' => $this->summary(
                $status,
                'Hết hạn phản hồi',
                'Đề nghị đã hết hạn phản hồi từ ứng viên.',
                'gray',
            ),
            default => $this->summary(
                $status,
                'Đang xử lý đề nghị',
                'Đề nghị tuyển dụng đang ở trạng thái cần kiểm tra thêm.',
                'gray',
            ),
        };
    }

    private function hiredSummary(Application $application, StatusApplicationEnum $status): array
    {
        $offer = $this->latestOffer($application);
        $acceptedAt = $offer?->accepted_at;

        return $this->summary(
            $status,
            $acceptedAt ? 'Đã đồng ý đề nghị' : 'Hoàn tất tuyển dụng',
            $acceptedAt
                ? 'Ứng viên đã xác nhận nhận việc lúc '.$this->formatDateTime($acceptedAt).'.'
                : 'Hồ sơ đã hoàn tất tuyển dụng.',
            'success',
            true,
        );
    }

    private function rejectedSummary(Application $application, StatusApplicationEnum $status): array
    {
        $offer = $this->latestOffer($application);

        if ($offer?->status === 'declined') {
            return $this->summary(
                $status,
                'Từ chối đề nghị',
                $offer->declined_reason
                    ? 'Lý do: '.trim((string) $offer->declined_reason)
                    : 'Ứng viên đã từ chối đề nghị tuyển dụng.',
                'danger',
                true,
            );
        }

        if (in_array($offer?->status, ['expired'], true) || $this->isExpiredOffer($offer)) {
            return $this->summary(
                $status,
                'Hết hạn phản hồi',
                'Hồ sơ dừng vì đề nghị tuyển dụng đã quá hạn phản hồi.',
                'gray',
                true,
            );
        }

        $stage = match ($application->rejected_stage) {
            'screening' => 'Từ chối sau sàng lọc CV',
            'interview' => 'Từ chối sau phỏng vấn',
            'offer' => 'Từ chối ở giai đoạn đề nghị',
            default => 'Hồ sơ đã bị từ chối',
        };

        return $this->summary(
            $status,
            $stage,
            $application->rejected_reason ?: 'Hồ sơ đã dừng trong quy trình tuyển dụng.',
            'danger',
            true,
        );
    }

    /**
     * @return array{stage_label: string, status_label: string, description: string, color: string, is_terminal: bool}
     */
    private function summary(
        StatusApplicationEnum $status,
        string $statusLabel,
        string $description,
        string $color,
        bool $isTerminal = false,
    ): array {
        return [
            'stage_label' => $status->getPipelineStageLabel(),
            'status_label' => $statusLabel,
            'description' => $description,
            'color' => $color,
            'is_terminal' => $isTerminal,
        ];
    }

    private function status(Application $application): ?StatusApplicationEnum
    {
        return $application->status instanceof StatusApplicationEnum
            ? $application->status
            : StatusApplicationEnum::tryFrom((string) $application->status);
    }

    private function latestInterview(Application $application): ?Interview
    {
        return $application->latestInterview ?? $application->interviews()->latest('id')->first();
    }

    private function latestOffer(Application $application): ?Offer
    {
        return $application->latestOffer ?? $application->offers()->latest('id')->first();
    }

    private function isExpiredOffer(?Offer $offer): bool
    {
        return (bool) $offer
            && $offer->status === 'pending'
            && $offer->expires_at
            && $offer->expires_at->isPast();
    }

    private function formatDateTime(CarbonInterface $date): string
    {
        return $date
            ->copy()
            ->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->format('d/m/Y H:i');
    }
}
