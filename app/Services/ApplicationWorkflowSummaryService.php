<?php

namespace App\Services;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\Scorecard;
use App\Models\User;
use Carbon\CarbonInterface;

class ApplicationWorkflowSummaryService
{
    /**
     * @return array{stage_label: string, status_label: string, description: string, color: string, is_terminal: bool}
     */
    public function summarize(Application $application, ?User $actor = null): array
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
            StatusApplicationEnum::INTERVIEWING => $this->interviewSummary($application, $status, $actor),
            StatusApplicationEnum::OFFERED => $this->offerSummary($application, $status),
            StatusApplicationEnum::HIRED => $this->hiredSummary($application, $status),
            StatusApplicationEnum::REJECTED => $this->rejectedSummary($application, $status),
            StatusApplicationEnum::WITHDRAWN => $this->summary(
                $status,
                'Ứng viên đã rút hồ sơ',
                $application->withdrawn_at
                    ? 'Hồ sơ được ứng viên rút lúc '.$this->formatDateTime($application->withdrawn_at).'.'
                    : 'Ứng viên đã chủ động dừng tham gia quy trình tuyển dụng.',
                'gray',
                true,
            ),
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

        $preScreening = app(ApplicationPreScreeningService::class)->latest($application);

        if (! $preScreening) {
            return $this->summary(
                $status,
                'Chờ liên hệ',
                'Cần liên hệ ứng viên và ghi nhận kết quả sơ tuyển.',
                'info',
            );
        }

        if ($preScreening->outcome === 'follow_up' && $preScreening->follow_up_at?->isPast()) {
            return $this->summary(
                $status,
                'Quá hạn liên hệ lại',
                'Đã hẹn liên hệ lại lúc '.$this->formatDateTime($preScreening->follow_up_at).'.',
                'danger',
            );
        }

        if ($preScreening->outcome === 'follow_up') {
            return $this->summary(
                $status,
                'Cần liên hệ lại',
                $preScreening->follow_up_at
                    ? 'Hẹn liên hệ lại lúc '.$this->formatDateTime($preScreening->follow_up_at).'.'
                    : 'Cần sắp xếp liên hệ lại với ứng viên.',
                'warning',
            );
        }

        return $this->summary(
            $status,
            'Đã xác nhận sơ tuyển',
            'Có thể tạo lịch phỏng vấn.',
            'info',
        );
    }

    private function interviewSummary(Application $application, StatusApplicationEnum $status, ?User $actor): array
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

        if (blank($interview->invite_sent_at) && $interview->scheduled_at?->lte(now())) {
            return $this->summary(
                $status,
                'Cần đặt lại lịch',
                'Cập nhật thời gian mới trước khi gửi thư mời cho ứng viên.',
                'danger',
            );
        }

        if (blank($interview->invite_sent_at)) {
            return $this->summary(
                $status,
                'Chưa gửi thư mời',
                'Lịch đã tạo, cần gửi thư mời cho ứng viên và hội đồng phỏng vấn.',
                'warning',
            );
        }

        $evaluationProgress = app(InterviewEvaluatorService::class)->progressForDisplay($interview);
        $actorAssignment = $actor
            ? ($interview->relationLoaded('evaluators')
                ? $interview->evaluators->firstWhere('user_id', $actor->id)
                : $interview->evaluators()->where('user_id', $actor->id)->first())
            : null;
        $actorIsLead = $actor && (int) $interview->interviewer_id === (int) $actor->id;

        if ($evaluationProgress['is_panel'] && ($interview->scheduled_at?->lte(now()) || $evaluationProgress['submitted'] > 0)) {
            if ($evaluationProgress['all_submitted']) {
                if (! $actor) {
                    return $this->summary(
                        $status,
                        'Chờ chốt kết quả',
                        'Đã đủ '.$evaluationProgress['submitted'].'/'.$evaluationProgress['required'].' phiếu. Người phụ trách cần chốt kết quả vòng.',
                        'warning',
                    );
                }

                return $this->summary(
                    $status,
                    $actorIsLead || $actor?->isSuperAdmin() ? 'Cần chốt kết quả vòng' : 'Đã đủ phiếu đánh giá',
                    $actorIsLead || $actor?->isSuperAdmin()
                        ? 'Đã nhận đủ '.$evaluationProgress['submitted'].'/'.$evaluationProgress['required'].' phiếu. Kiểm tra và chốt kết quả vòng.'
                        : 'Đã nhận đủ phiếu, đang chờ người phụ trách chốt kết quả vòng.',
                    'warning',
                );
            }

            if ($actorAssignment?->submitted_at) {
                return $this->summary(
                    $status,
                    'Đã gửi phiếu đánh giá',
                    'Đang chờ '.$evaluationProgress['pending'].' phiếu còn lại trước khi chốt kết quả vòng.',
                    'info',
                );
            }

            if ($actorAssignment?->is_required) {
                return $this->summary(
                    $status,
                    'Cần gửi phiếu đánh giá',
                    'Buổi phỏng vấn đã đến hạn và phiếu của bạn chưa được gửi.',
                    'danger',
                );
            }

            return $this->summary(
                $status,
                'Đang nhận phiếu đánh giá',
                'Đã nhận '.$evaluationProgress['submitted'].'/'.$evaluationProgress['required'].' phiếu, còn '.$evaluationProgress['pending'].' phiếu.',
                'info',
            );
        }

        $scorecard = $this->latestScorecardForInterview($application, $interview);

        if ($scorecard?->conclusion === 'hold') {
            $average = $scorecard->average_score !== null
                ? number_format((float) $scorecard->average_score, 2, ',', '.').'/10'
                : null;

            return $this->summary(
                $status,
                'Cần đánh giá bổ sung',
                $average
                    ? 'Đã chấm '.$average.', cần xem xét thêm trước khi chốt kết quả.'
                    : 'Đã có nhận xét, cần xem xét thêm trước khi chốt kết quả.',
                'warning',
            );
        }

        if ($scorecard && $scorecard->conclusion === null) {
            $scoredCount = collect((array) $scorecard->criteria)
                ->filter(fn ($criterion): bool => is_array($criterion) && filled($criterion['score'] ?? null))
                ->count();
            $criteriaCount = count((array) $scorecard->criteria);

            return $this->summary(
                $status,
                'Đang đánh giá',
                $criteriaCount > 0
                    ? "Đã lưu {$scoredCount}/{$criteriaCount} tiêu chí, cần hoàn tất đánh giá."
                    : 'Đã lưu bản nháp, cần hoàn tất đánh giá.',
                'info',
            );
        }

        if (($interview->result ?? 'pending') !== 'pending') {
            if ($interview->result === 'pass' && $interview->finalized_at) {
                $nextRound = app(InterviewRoundWorkflowService::class)->nextRound($application, $interview);

                if ($nextRound) {
                    return $this->summary(
                        $status,
                        'Đã đạt vòng '.(int) $interview->round_number,
                        'Sẵn sàng tạo lịch cho '.mb_strtolower((string) $nextRound['name']).'.',
                        'success',
                    );
                }
            }

            return $this->summary(
                $status,
                'Đã có kết quả phỏng vấn',
                'Kết quả đã được ghi nhận, cần quyết định bước tiếp theo.',
                'primary',
            );
        }

        if ($interview->scheduled_at?->lte(now())) {
            if ($actor && ! $actorIsLead) {
                return $this->summary(
                    $status,
                    'Chờ người phụ trách đánh giá',
                    'Đang chờ '.($interview->interviewer?->name ?: 'người phụ trách vòng').' ghi nhận kết quả phỏng vấn.',
                    'info',
                );
            }

            return $this->summary(
                $status,
                'Cần đánh giá phỏng vấn',
                'Buổi phỏng vấn đã đến hạn và cần hoàn tất phiếu đánh giá.',
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
            'pre_screening' => 'Từ chối sau sơ tuyển',
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
        return app(InterviewRoundWorkflowService::class)->latestInterview($application);
    }

    private function latestOffer(Application $application): ?Offer
    {
        if ($application->relationLoaded('latestOffer')) {
            return $application->latestOffer;
        }

        return $application->offers()->latest('id')->first();
    }

    private function latestScorecardForInterview(Application $application, Interview $interview): ?Scorecard
    {
        if ($interview->relationLoaded('scorecards')) {
            return $interview->scorecards->sortByDesc('updated_at')->first();
        }

        return $application->scorecards()
            ->where('interview_id', $interview->id)
            ->latest('updated_at')
            ->first();
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
