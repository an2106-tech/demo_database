<?php

namespace App\Services;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Interview;
use App\Models\User;

final class InterviewSchedulePresentationService
{
    /**
     * @return array{status: string, action: string, badge_color: string, background_color: string, text_color: string, url: string}
     */
    public function present(Interview $interview, ?User $actor): array
    {
        $application = $interview->application;
        $detailUrl = $application
            ? ApplicationResource::getUrl('view', ['record' => $application])
            : ApplicationResource::getUrl('kanban');
        $isPm = $actor && RecruitmentDashboardContext::current()->isPm();

        if (in_array($interview->result, ['pass', 'fail'], true) || $interview->finalized_at) {
            return $this->item(
                $interview->result === 'pass' ? 'Đã đạt vòng' : 'Đã kết thúc',
                'Xem kết quả',
                $interview->result === 'pass' ? 'success' : 'gray',
                $interview->result === 'pass' ? '#16a34a' : '#64748b',
                $detailUrl,
            );
        }

        if (blank($interview->invite_sent_at) && $interview->scheduled_at?->isPast()) {
            return $this->item(
                'Lịch nháp quá hạn',
                $isPm ? 'Xem hồ sơ' : 'Đặt lại lịch',
                'danger',
                '#dc2626',
                $application ? $this->kanbanUrl((int) $application->id, 'interview_invite_unsent') : $detailUrl,
            );
        }

        if (blank($interview->invite_sent_at)) {
            return $this->item(
                'Chưa gửi thư mời',
                $isPm ? 'Chờ HR gửi lịch' : 'Gửi thư mời',
                'warning',
                '#d97706',
                $application ? $this->kanbanUrl((int) $application->id, 'interview_invite_unsent') : $detailUrl,
            );
        }

        if ($interview->scheduled_at?->isPast()) {
            return $this->item(
                'Đến hạn đánh giá',
                $isPm ? 'Đánh giá' : 'Theo dõi đánh giá',
                'danger',
                '#ea580c',
                $application ? $this->kanbanUrl((int) $application->id, 'interview_overdue') : $detailUrl,
            );
        }

        return $this->item(
            'Đã gửi lịch',
            'Xem hồ sơ',
            'info',
            '#2563eb',
            $detailUrl,
        );
    }

    /**
     * @return array{status: string, action: string, badge_color: string, background_color: string, text_color: string, url: string}
     */
    private function item(string $status, string $action, string $badgeColor, string $backgroundColor, string $url): array
    {
        return [
            'status' => $status,
            'action' => $action,
            'badge_color' => $badgeColor,
            'background_color' => $backgroundColor,
            'text_color' => '#ffffff',
            'url' => $url,
        ];
    }

    private function kanbanUrl(int $applicationId, string $queue): string
    {
        $url = ApplicationResource::getUrl('kanban');
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query([
            'q' => $applicationId,
            'queue' => $queue,
        ]);
    }
}
