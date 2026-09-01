<?php

namespace App\Services;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\OfferResource;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RecruitmentInternalNotificationService
{
    public function notifyRecruitmentJobSubmittedForApproval(RecruitmentJob $job, ?User $submitter = null): void
    {
        $job->loadMissing(['branch', 'department']);

        $this->branchUsers($job->branch_id, ['director'])
            ->reject(fn (User $user): bool => $submitter?->is($user) ?? false)
            ->each(function (User $user) use ($job, $submitter): void {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => 'job_pending_approval',
                    'data' => array_filter([
                        'title' => 'Tin tuyển dụng chờ phê duyệt',
                        'message' => ($submitter?->name ?? 'HR').' vừa gửi một tin tuyển dụng cần xử lý.',
                        'url' => RecruitmentJobResource::getUrl('view', ['record' => $job]),
                        'job_id' => $job->id,
                        'subject' => $job->title,
                        'context' => collect([
                            $job->branch?->name,
                            $job->department?->name,
                        ])->filter()->join(' · '),
                        'action_label' => 'Xem và phê duyệt',
                    ], fn (mixed $value): bool => $value !== null && $value !== ''),
                ]);
            });
    }

    public function notifyInterviewPanelAssigned(Interview $interview, bool $isUpdate = false): void
    {
        $interview->loadMissing([
            'application.candidate',
            'application.job.branch',
            'evaluators.user',
        ]);

        $application = $interview->application;
        $candidateName = $application?->snapshotCandidateName() ?: 'Ứng viên';
        $jobTitle = $application?->job?->title ?: 'vị trí tuyển dụng';
        $scheduledAt = $interview->scheduled_at
            ? $interview->scheduled_at
                ->copy()
                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                ->format('H:i, d/m/Y')
            : 'chưa xác định';

        $interview->evaluators
            ->filter(fn ($assignment): bool => (bool) $assignment->user?->is_active)
            ->each(function ($assignment) use ($interview, $isUpdate, $candidateName, $jobTitle, $scheduledAt): void {
                $isLead = $assignment->role === 'lead';
                $this->upsertUnreadInterviewNotification(
                    $assignment->user,
                    $interview,
                    'interview_panel_assigned',
                    $isUpdate
                        ? 'Lịch phỏng vấn đã cập nhật'
                        : ($isLead ? 'Bạn phụ trách một buổi phỏng vấn' : 'Bạn được phân công đánh giá'),
                    ($isUpdate ? 'Thời gian mới: ' : 'Thời gian: ').$scheduledAt.'.',
                    $candidateName,
                    $jobTitle,
                    $isLead ? 'Người phụ trách vòng phỏng vấn' : 'Thành viên đánh giá',
                    'Mở Kanban',
                );
            });
    }

    public function notifyInterviewPanelReady(Interview $interview): void
    {
        $interview->loadMissing([
            'application.candidate',
            'application.job.branch',
            'interviewer',
        ]);

        $lead = $interview->interviewer;
        if (! $lead?->is_active) {
            return;
        }

        $progress = app(InterviewEvaluatorService::class)->progress($interview);
        if (! $progress['is_panel'] || ! $progress['all_submitted']) {
            return;
        }

        $application = $interview->application;
        $this->upsertUnreadInterviewNotification(
            $lead,
            $interview,
            'interview_panel_ready',
            'Đã đủ phiếu đánh giá',
            'Đã nhận '.$progress['submitted'].'/'.$progress['required'].' phiếu. Vui lòng chốt kết quả vòng phỏng vấn.',
            $application?->snapshotCandidateName() ?: 'Ứng viên',
            $application?->job?->title ?: 'vị trí tuyển dụng',
            'Người phụ trách vòng phỏng vấn',
            'Chốt kết quả',
        );
    }

    public function notifyInterviewRoundHandoff(Interview $interview, bool $hasNextRound): void
    {
        $interview->loadMissing(['application.candidate', 'application.job.branch']);
        $application = $interview->application;
        if (! $application) {
            return;
        }

        $branchId = $application?->job?->branch_id ?? $application?->branch_id;
        $candidateName = $application?->snapshotCandidateName() ?: 'Ứng viên';
        $jobTitle = $application?->job?->title ?: 'vị trí tuyển dụng';

        $this->branchUsers($branchId, ['hr'])
            ->reject(fn (User $user): bool => (int) $user->id === (int) $interview->finalized_by_user_id)
            ->each(function (User $user) use ($interview, $hasNextRound, $candidateName, $jobTitle): void {
                $nextRound = $hasNextRound
                    ? app(InterviewRoundWorkflowService::class)->nextRound($interview->application, $interview)
                    : null;

                $this->upsertUnreadInterviewNotification(
                    $user,
                    $interview,
                    $hasNextRound ? 'interview_next_round_ready' : 'interview_process_passed',
                    $hasNextRound ? 'Cần tạo lịch vòng tiếp theo' : 'Ứng viên đã đạt vòng cuối',
                    $hasNextRound
                        ? 'Đã chốt đạt vòng '.(int) $interview->round_number.'. Tạo lịch cho '.mb_strtolower((string) ($nextRound['name'] ?? 'vòng tiếp theo')).'.'
                        : 'Quy trình phỏng vấn đã hoàn tất. Có thể tạo đề nghị tuyển dụng.',
                    $candidateName,
                    $jobTitle,
                    'Bàn giao cho HR',
                    $hasNextRound ? 'Tạo lịch' : 'Tạo đề nghị',
                );
            });
    }

    public function notifyOfferSubmittedForApproval(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch']);

        $this->notifyUsers(
            $this->branchUsers($this->branchId($offer), ['director']),
            'offer_approval_requested',
            'Cần duyệt đề nghị tuyển dụng',
            '',
            OfferResource::getUrl('edit', ['record' => $offer]),
            $offer,
            $this->offerNotificationContext($offer, 'Mở đề nghị'),
        );
    }

    public function notifyOfferRejectedByDirector(Offer $offer, ?User $rejector = null): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $notes = trim((string) $offer->approval_notes);
        $message = $notes !== ''
            ? 'Giám đốc ghi chú: '.$notes
            : 'Giám đốc đã gửi đề nghị lại để HR chỉnh sửa.';

        $this->notifyUsers(
            $this->offerOperators($offer)->reject(fn (User $user): bool => $rejector && $user->is($rejector)),
            'offer_rejected_by_director',
            'Đề nghị cần chỉnh sửa',
            $message,
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem ghi chú'),
        );
    }

    public function notifyOfferAcceptedByCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_accepted_by_candidate',
            'Ứng viên đã đồng ý đề nghị',
            '',
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem hồ sơ'),
        );
    }

    public function notifyOfferSentToCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_sent_to_candidate',
            'Đề nghị đã gửi ứng viên',
            '',
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem hồ sơ'),
        );
    }

    public function notifyOfferDeclinedByCandidate(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $reason = trim((string) $offer->declined_reason);
        $message = $reason !== ''
            ? 'Lý do phản hồi: '.$reason
            : 'Cần trao đổi hướng xử lý tiếp theo với ứng viên.';

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_declined_by_candidate',
            'Ứng viên đã từ chối đề nghị',
            $message,
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem phản hồi'),
        );
    }

    public function notifyOfferExpired(Offer $offer): void
    {
        $offer->loadMissing(['application.candidate', 'application.job.branch', 'application.assignedHr', 'application.job.creator']);

        $this->notifyUsers(
            $this->offerTeam($offer),
            'offer_expired',
            'Đề nghị đã hết hạn phản hồi',
            'Ứng viên chưa phản hồi trước hạn. HR cần xem lại hướng xử lý hồ sơ.',
            ApplicationResource::getUrl('view', ['record' => $offer->application]),
            $offer,
            $this->offerNotificationContext($offer, 'Xem hồ sơ'),
        );
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function notifyUsers(Collection $users, string $type, string $title, string $message, string $url, Offer $offer, array $context = []): void
    {
        $application = $offer->application;

        $users
            ->filter(fn (User $user): bool => (bool) $user->id)
            ->unique('id')
            ->each(function (User $user) use ($type, $title, $message, $url, $offer, $application, $context): void {
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'data' => array_filter([
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'offer_id' => $offer->id,
                        'application_id' => $application?->id,
                        ...$context,
                    ], fn (mixed $value): bool => $value !== null && $value !== ''),
                ]);
            });
    }

    /**
     * @return Collection<int, User>
     */
    private function offerTeam(Offer $offer): Collection
    {
        return $this->offerOperators($offer)
            ->merge($this->branchUsers($this->branchId($offer), ['director']))
            ->unique('id')
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function offerOperators(Offer $offer): Collection
    {
        $application = $offer->application;

        $operators = collect([
            $application?->assignedHr,
            $application?->job?->creator,
        ])
            ->filter()
            ->merge($this->branchUsers($this->branchId($offer), ['hr']))
            ->filter(fn (User $user): bool => (bool) $user->is_active)
            ->unique('id')
            ->values();

        return $operators;
    }

    /**
     * @param  array<int, string>  $roles
     * @return Collection<int, User>
     */
    private function branchUsers(?int $branchId, array $roles): Collection
    {
        if (! $branchId) {
            return collect();
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function (Builder $query) use ($roles): void {
                $query->whereIn('role', $roles)
                    ->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->whereIn('name', $roles));
            })
            ->get();
    }

    private function branchId(Offer $offer): ?int
    {
        return $offer->application?->job?->branch_id
            ?? $offer->application?->branch_id;
    }

    /** @return array<string, string> */
    private function offerNotificationContext(Offer $offer, string $actionLabel): array
    {
        $application = $offer->application;
        $candidateName = $application?->snapshotCandidateName() ?: 'Ứng viên';
        $jobTitle = $application?->job?->title ?: 'vị trí tuyển dụng';
        $branchName = $application?->job?->branch?->name ?: $application?->branch?->name;

        return [
            'subject' => $candidateName,
            'context' => trim($jobTitle.($branchName ? ' · '.$branchName : '')),
            'action_label' => $actionLabel,
        ];
    }

    private function upsertUnreadInterviewNotification(
        User $user,
        Interview $interview,
        string $type,
        string $title,
        string $message,
        string $subject,
        string $context,
        string $role,
        string $actionLabel,
    ): void {
        $data = [
            'title' => $title,
            'message' => $message,
            'url' => ApplicationResource::getUrl('kanban'),
            'application_id' => $interview->application_id,
            'interview_id' => $interview->id,
            'subject' => $subject,
            'context' => $context.' · '.$role,
            'action_label' => $actionLabel,
        ];

        $notification = UserNotification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('read_at')
            ->where('data->interview_id', $interview->id)
            ->latest('id')
            ->first();

        if ($notification) {
            $notification->forceFill([
                'data' => $data,
                'created_at' => now(),
            ])->save();

            return;
        }

        UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
        ]);
    }
}
