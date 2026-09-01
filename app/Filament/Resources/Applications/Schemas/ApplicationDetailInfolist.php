<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Offer;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ApplicationDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->compact()
                ->columnSpanFull()
                ->schema([
                    View::make('filament.resources.applications.infolists.application-summary')
                        ->viewData(fn (Application $record): array => static::summaryData($record)),
                ]),
            Tabs::make('Chi tiết ứng tuyển')
                ->contained(false)
                ->persistTab()
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Tổng quan')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            View::make('filament.resources.applications.infolists.application-overview')
                                ->viewData(fn (Application $record): array => static::overviewData($record)),
                        ]),
                    Tab::make('Quy trình')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->schema([
                            View::make('filament.resources.applications.infolists.application-workflow')
                                ->viewData(fn (Application $record): array => static::workflowData($record)),
                        ]),
                    Tab::make('Phỏng vấn')
                        ->icon('heroicon-o-user-group')
                        ->badge(fn (Application $record): ?string => $record->interviews->isNotEmpty() ? (string) $record->interviews->count() : null)
                        ->schema([
                            View::make('filament.resources.applications.infolists.application-interviews')
                                ->viewData(fn (Application $record): array => static::interviewsData($record)),
                        ]),
                    Tab::make('Đề nghị tuyển dụng')
                        ->icon('heroicon-o-document-check')
                        ->badge(fn (Application $record): ?string => $record->offers->isNotEmpty() ? (string) $record->offers->count() : null)
                        ->visible(fn (Application $record): bool => $record->offers->isNotEmpty()
                            || in_array(static::statusEnum($record), [StatusApplicationEnum::OFFERED, StatusApplicationEnum::HIRED], true))
                        ->schema([
                            View::make('filament.resources.applications.infolists.application-offers')
                                ->viewData(fn (Application $record): array => static::offersData($record)),
                        ]),
                ]),
        ]);
    }

    /** @return array<string, mixed> */
    private static function summaryData(Application $record): array
    {
        $status = static::statusEnum($record);
        $context = static::statusContext($record, $status);

        return [
            'candidateName' => $record->snapshotCandidateName() ?: 'Ứng viên',
            'applicationCode' => '#'.str_pad((string) $record->id, 4, '0', STR_PAD_LEFT),
            'jobTitle' => $record->job?->title ?: 'Chưa có vị trí',
            'branchName' => $record->job?->branch?->name ?? $record->branch?->name ?? 'Chưa có chi nhánh',
            'stageLabel' => $status?->getPipelineStageLabel() ?? 'Chưa xác định',
            'stageColor' => $status?->getPipelineStageColor() ?? 'gray',
            'statusLabel' => $context['label'],
            'statusDescription' => $context['description'],
            'appliedAt' => static::formatDateTime($record->applied_at),
        ];
    }

    /** @return array<string, mixed> */
    private static function overviewData(Application $record): array
    {
        $analysis = $record->latestScreeningAiAnalysis;

        if ($analysis?->status !== 'completed') {
            $analysis = $record->aiAnalyses
                ->where('analysis_type', 'screening')
                ->where('status', 'completed')
                ->sortByDesc('id')
                ->first() ?? $analysis;
        }

        $score = is_numeric($analysis?->score) ? (int) $analysis->score : null;

        return [
            'candidate' => [
                'email' => $record->snapshotCandidateEmail() ?: '-',
                'phone' => $record->snapshotCandidatePhone() ?: '-',
                'experience' => is_numeric($record->snapshotCandidateExperienceYears()) ? $record->snapshotCandidateExperienceYears().' năm' : '-',
                'profileTitle' => $record->snapshotProfileTitle() ?: '-',
            ],
            'application' => [
                'job' => $record->job?->title ?: '-',
                'department' => $record->job?->department?->name ?: '-',
                'branch' => $record->job?->branch?->name ?? $record->branch?->name ?? '-',
                'source' => static::sourceLabel($record->source).' · '.static::applyMethodLabel($record->apply_method),
                'appliedAt' => static::formatDateTime($record->applied_at),
            ],
            'cv' => [
                'name' => $record->submittedCvName() ?: 'Chưa có CV',
                'url' => $record->submittedCvUrl(),
            ],
            'ai' => [
                'available' => $analysis?->status === 'completed',
                'score' => $score,
                'tone' => static::scoreTone($score),
                'recommendation' => static::aiRecommendationLabel($analysis?->recommendation),
                'summary' => $analysis?->summary ?: 'Chưa có kết quả phân tích CV.',
                'strengths' => collect((array) $analysis?->strengths)->filter()->take(3)->values()->all(),
                'gaps' => collect((array) $analysis?->gaps)->filter()->take(3)->values()->all(),
                'analyzedAt' => static::formatDateTime($analysis?->analyzed_at),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function workflowData(Application $record): array
    {
        $status = static::statusEnum($record);
        $currentStage = $status?->getPipelineStageKey();
        $stageOrder = ['applied', 'screening', 'interview', 'offer', 'hired'];
        $currentIndex = array_search($currentStage, $stageOrder, true);
        $isTerminal = in_array($status, [StatusApplicationEnum::REJECTED, StatusApplicationEnum::WITHDRAWN], true);
        $terminalStageIndex = $isTerminal ? static::terminalStageIndex($record) : null;

        $stages = collect(StatusApplicationEnum::pipelineStages())
            ->reject(fn (array $stage, string $key): bool => $key === 'rejected')
            ->map(function (array $stage, string $key) use ($currentStage, $currentIndex, $isTerminal, $stageOrder, $terminalStageIndex): array {
                $index = array_search($key, $stageOrder, true);
                $state = 'pending';

                if ($isTerminal && $index !== false && $terminalStageIndex !== null && $index <= $terminalStageIndex) {
                    $state = 'completed';
                } elseif ($key === $currentStage) {
                    $state = 'current';
                } elseif ($currentIndex !== false && $index !== false && $index < $currentIndex) {
                    $state = 'completed';
                }

                return ['key' => $key, 'label' => $stage['label'], 'state' => $state];
            })
            ->values()
            ->all();

        $preScreenings = $record->preScreenings
            ->sortByDesc('id')
            ->map(fn ($item): array => [
                'channel' => static::contactChannelLabel($item->contact_channel, $item->contact_channel_detail),
                'contactedAt' => static::formatDateTime($item->contacted_at),
                'outcome' => static::preScreeningOutcomeLabel($item->outcome),
                'outcomeTone' => static::preScreeningOutcomeTone($item->outcome),
                'followUpAt' => static::formatDateTime($item->follow_up_at),
                'note' => static::preScreeningNote($item->note, $item->rejection_reason, $item->outcome),
                'handler' => $item->handledBy?->name ?: 'Hệ thống',
            ])
            ->values()
            ->all();

        $histories = $record->statusHistories
            ->sortByDesc('id')
            ->map(fn ($history): array => [
                'from' => static::statusLabel($history->from_status),
                'to' => static::statusLabel($history->to_status),
                'comment' => trim((string) $history->comment),
                'actor' => $history->user?->name ?: 'Hệ thống tự động',
                'actorMeta' => collect([static::roleLabel($history->user?->role), $history->user?->branch?->name])->filter()->implode(' · '),
                'time' => static::formatDateTime($history->created_at),
            ])
            ->values();

        return [
            'stages' => $stages,
            'isRejected' => $isTerminal,
            'finalLabel' => $status === StatusApplicationEnum::WITHDRAWN ? 'Ứng viên rút hồ sơ' : 'Đã kết thúc',
            'preScreenings' => $preScreenings,
            'recentHistories' => $histories->take(5)->values()->all(),
            'olderHistories' => $histories->skip(5)->values()->all(),
            'historyCount' => $histories->count(),
        ];
    }

    /** @return array<string, mixed> */
    private static function interviewsData(Application $record): array
    {
        $interviews = $record->interviews->sortBy([
            ['round_number', 'asc'],
            ['id', 'asc'],
        ])->values();

        return [
            'interviews' => $interviews
                ->map(fn (Interview $interview, int $index): array => static::interviewData($interview, $index === $interviews->count() - 1))
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private static function interviewData(Interview $interview, bool $isLatest): array
    {
        $assignments = $interview->evaluators
            ->sortBy(fn ($assignment): string => sprintf('%d-%010d', $assignment->role === 'lead' ? 0 : 1, $assignment->id));
        $scorecards = $interview->scorecards->keyBy('evaluator_id');

        $participants = $assignments->map(function ($assignment) use ($scorecards): array {
            $scorecard = $scorecards->get($assignment->user_id);
            $isWaived = ! $assignment->is_required && filled($assignment->waived_at);
            $isSubmitted = filled($assignment->submitted_at) || filled($scorecard?->submitted_at);

            return [
                'name' => $assignment->user?->name ?: 'Thành viên không xác định',
                'role' => $assignment->role === 'lead' ? 'Người phụ trách' : 'Cùng đánh giá',
                'status' => $isWaived ? 'Không yêu cầu phiếu' : ($isSubmitted ? 'Đã gửi phiếu' : 'Chưa gửi phiếu'),
                'statusTone' => $isWaived ? 'gray' : ($isSubmitted ? 'success' : 'warning'),
                'average' => $scorecard?->average_score !== null ? number_format((float) $scorecard->average_score, 2, ',', '.').'/10' : null,
                'conclusion' => static::conclusionLabel($scorecard?->conclusion),
                'notes' => $scorecard?->notes,
                'submittedAt' => static::formatDateTime($scorecard?->submitted_at ?? $assignment->submitted_at),
            ];
        })->values();

        if ($participants->isEmpty() && $interview->interviewer) {
            $participants->push([
                'name' => $interview->interviewer->name,
                'role' => 'Người phụ trách',
                'status' => 'Chưa gửi phiếu',
                'statusTone' => 'warning',
                'average' => null,
                'conclusion' => 'Chưa có kết luận',
                'notes' => null,
                'submittedAt' => '-',
            ]);
        }

        [$required, $submitted] = static::evaluatorProgress($interview);
        $submittedScorecards = $interview->scorecards->filter(fn ($scorecard): bool => filled($scorecard->submitted_at));
        $panelAverage = $submittedScorecards->whereNotNull('average_score')->avg('average_score');

        return [
            'isLatest' => $isLatest,
            'roundNumber' => (int) ($interview->round_number ?: 1),
            'roundName' => $interview->round_name ?: 'Vòng '.($interview->round_number ?: 1),
            'status' => static::interviewStatus($interview),
            'statusTone' => static::interviewStatusTone($interview),
            'scheduledAt' => static::formatDateTime($interview->scheduled_at),
            'duration' => $interview->duration_minutes ? $interview->duration_minutes.' phút' : '-',
            'type' => static::interviewTypeLabel($interview->type),
            'location' => $interview->type === 'online' ? ($interview->meeting_link ?: 'Chưa có liên kết họp') : ($interview->workplace?->name ?: 'Chưa chọn địa điểm'),
            'inviteStatus' => $interview->invite_sent_at ? 'Đã gửi lịch' : 'Chưa gửi lịch',
            'inviteSentAt' => static::formatDateTime($interview->invite_sent_at),
            'templateName' => data_get($interview->scorecard_template_snapshot, 'name') ?: $interview->scorecardTemplate?->name ?: 'Chưa có mẫu đánh giá',
            'progress' => $required > 0 ? "{$submitted}/{$required} phiếu" : 'Chưa phân công',
            'panelAverage' => $panelAverage !== null ? number_format((float) $panelAverage, 2, ',', '.').'/10' : '-',
            'result' => static::interviewResultLabel($interview->result),
            'finalizedAt' => static::formatDateTime($interview->finalized_at),
            'finalizedBy' => $interview->finalizedBy?->name ?: '-',
            'finalNotes' => $interview->final_notes,
            'participants' => $participants->all(),
        ];
    }

    /** @return array<string, mixed> */
    private static function offersData(Application $record): array
    {
        return [
            'offers' => $record->offers->sortByDesc('id')->map(fn (Offer $offer): array => [
                'code' => 'OFF-'.str_pad((string) $offer->id, 6, '0', STR_PAD_LEFT),
                'status' => static::offerStatusLabel($offer->status),
                'statusTone' => static::offerStatusTone($offer->status, $offer),
                'salary' => $offer->salary_offered !== null ? number_format((float) $offer->salary_offered, 0, ',', '.').' VND' : '-',
                'startDate' => $offer->start_date?->format('d/m/Y') ?: '-',
                'probation' => $offer->probation_months !== null ? $offer->probation_months.' tháng' : '-',
                'expiresAt' => static::formatDateTime($offer->expires_at),
                'template' => $offer->letterTemplate?->name ?: 'Không dùng mẫu',
                'requestedAt' => static::formatDateTime($offer->approval_requested_at),
                'approvedAt' => static::formatDateTime($offer->approved_at),
                'approvedBy' => $offer->approvedByUser?->name ?: '-',
                'sentAt' => static::formatDateTime($offer->sent_at),
                'responseAt' => static::formatDateTime($offer->response_at),
                'approvalNotes' => $offer->approval_notes,
                'declinedReason' => $offer->declined_reason,
                'hasPdf' => filled($offer->pdf_path),
            ])->values()->all(),
        ];
    }

    /** @return array{label: string, description: string} */
    private static function statusContext(Application $record, ?StatusApplicationEnum $status): array
    {
        $interview = $record->interviews->sortBy([
            ['round_number', 'desc'],
            ['id', 'desc'],
        ])->first();
        $offer = $record->offers->sortByDesc('id')->first();
        $preScreening = $record->preScreenings->sortByDesc('id')->first();

        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => ['label' => 'Chờ sàng lọc CV', 'description' => 'Cần đối chiếu CV với vị trí ứng tuyển trước khi chuyển bước.'],
            StatusApplicationEnum::SCREENING => static::screeningContext($interview, $preScreening),
            StatusApplicationEnum::INTERVIEW_SCHEDULED, StatusApplicationEnum::INTERVIEWING => static::interviewContext($interview),
            StatusApplicationEnum::OFFERED => static::offerContext($offer),
            StatusApplicationEnum::HIRED => ['label' => 'Hoàn tất tuyển dụng', 'description' => 'Ứng viên đã hoàn tất quy trình và được ghi nhận tuyển dụng.'],
            StatusApplicationEnum::REJECTED => static::rejectedContext($record, $offer),
            StatusApplicationEnum::WITHDRAWN => ['label' => 'Ứng viên đã rút hồ sơ', 'description' => 'Ứng viên chủ động dừng tham gia quy trình tuyển dụng.'],
            default => ['label' => 'Chưa xác định', 'description' => 'Hồ sơ chưa có trạng thái tuyển dụng hợp lệ.'],
        };
    }

    private static function screeningContext(?Interview $interview, mixed $preScreening): array
    {
        if ($interview) {
            return $interview->invite_sent_at
                ? ['label' => 'Đã gửi lịch phỏng vấn', 'description' => 'Ứng viên và hội đồng đã nhận thông tin lịch phỏng vấn.']
                : ['label' => 'Cần gửi lịch phỏng vấn', 'description' => 'Lịch đã được tạo và đang chờ gửi cho các bên liên quan.'];
        }

        if (! $preScreening) {
            return ['label' => 'Chờ liên hệ sơ tuyển', 'description' => 'Cần liên hệ ứng viên và ghi nhận kết quả trao đổi sơ bộ.'];
        }

        if ($preScreening->outcome === 'follow_up') {
            return [
                'label' => $preScreening->follow_up_at?->isPast() ? 'Quá hạn liên hệ lại' : 'Cần liên hệ lại',
                'description' => $preScreening->follow_up_at ? 'Thời điểm liên hệ lại: '.static::formatDateTime($preScreening->follow_up_at).'.' : 'Cần tiếp tục trao đổi với ứng viên.',
            ];
        }

        return ['label' => 'Đã xác nhận sơ tuyển', 'description' => 'Hồ sơ đã sẵn sàng để tạo lịch phỏng vấn.'];
    }

    private static function interviewContext(?Interview $interview): array
    {
        if (! $interview) {
            return ['label' => 'Chưa có lịch phỏng vấn', 'description' => 'Cần kiểm tra và tạo lịch cho vòng phỏng vấn hiện tại.'];
        }

        if (! $interview->invite_sent_at) {
            return [
                'label' => $interview->scheduled_at?->isPast() ? 'Cần đặt lại lịch' : 'Chưa gửi lịch phỏng vấn',
                'description' => $interview->scheduled_at?->isPast() ? 'Thời gian dự kiến đã qua, cần cập nhật lịch mới trước khi gửi.' : 'Lịch đã tạo và đang chờ gửi cho ứng viên cùng hội đồng.',
            ];
        }

        if ($interview->finalized_at) {
            return ['label' => 'Đã chốt kết quả vòng', 'description' => 'Kết quả vòng phỏng vấn đã được người phụ trách xác nhận.'];
        }

        if ($interview->scheduled_at?->isPast()) {
            [$required, $submitted] = static::evaluatorProgress($interview);

            return [
                'label' => $required > 0 && $submitted >= $required ? 'Chờ chốt kết quả vòng' : 'Đang nhận phiếu đánh giá',
                'description' => $required > 0 ? "Đã nhận {$submitted}/{$required} phiếu đánh giá bắt buộc." : 'Buổi phỏng vấn đã đến hạn và cần ghi nhận đánh giá.',
            ];
        }

        return ['label' => 'Chờ đến lịch phỏng vấn', 'description' => 'Lịch đã gửi và đang chờ buổi phỏng vấn diễn ra.'];
    }

    /** @return array{label: string, description: string} */
    private static function rejectedContext(Application $record, ?Offer $offer): array
    {
        if ($offer?->status === 'declined') {
            return [
                'label' => 'Ứng viên từ chối đề nghị',
                'description' => $offer->declined_reason ?: 'Ứng viên đã từ chối đề nghị tuyển dụng.',
            ];
        }

        $label = match ($record->rejected_stage) {
            'screening' => 'Từ chối sau sàng lọc CV',
            'pre_screening' => 'Từ chối sau sơ tuyển',
            'interview' => 'Từ chối sau phỏng vấn',
            'offer' => 'Từ chối ở bước đề nghị',
            default => 'Hồ sơ đã bị từ chối',
        };

        return [
            'label' => $label,
            'description' => $record->rejected_reason ?: 'Quy trình tuyển dụng của hồ sơ đã kết thúc.',
        ];
    }

    /** @return array{0: int, 1: int} */
    private static function evaluatorProgress(Interview $interview): array
    {
        $submittedEvaluatorIds = $interview->scorecards
            ->filter(fn ($scorecard): bool => filled($scorecard->submitted_at))
            ->pluck('evaluator_id');
        $requiredAssignments = $interview->evaluators->where('is_required', true);
        $submitted = $requiredAssignments
            ->filter(fn ($assignment): bool => filled($assignment->submitted_at)
                || $submittedEvaluatorIds->contains($assignment->user_id))
            ->count();

        return [$requiredAssignments->count(), $submitted];
    }

    private static function terminalStageIndex(Application $record): int
    {
        $rejectedStage = match ($record->rejected_stage) {
            'screening' => 0,
            'pre_screening' => 1,
            'interview' => 2,
            'offer' => 3,
            default => null,
        };

        if ($rejectedStage !== null) {
            return $rejectedStage;
        }

        if ($record->offers->isNotEmpty()) {
            return 3;
        }

        if ($record->interviews->isNotEmpty()) {
            return 2;
        }

        if ($record->preScreenings->isNotEmpty()) {
            return 1;
        }

        return 0;
    }

    private static function offerContext(?Offer $offer): array
    {
        if (! $offer) {
            return ['label' => 'Cần tạo đề nghị', 'description' => 'Ứng viên đã qua phỏng vấn và chưa có đề nghị tuyển dụng.'];
        }

        return [
            'label' => static::offerStatusLabel($offer->status),
            'description' => match ($offer->status) {
                'draft' => 'Bản nháp cần được kiểm tra và gửi giám đốc chi nhánh duyệt.',
                'awaiting_approval' => 'Đề nghị đang chờ quyết định của giám đốc chi nhánh.',
                'rejected' => $offer->approval_notes ?: 'Đề nghị cần được chỉnh sửa trước khi gửi duyệt lại.',
                'pending' => $offer->expires_at?->isPast() ? 'Đề nghị đã quá hạn phản hồi và cần được xử lý.' : 'Thư mời đã gửi và đang chờ ứng viên phản hồi.',
                'accepted' => 'Ứng viên đã đồng ý với đề nghị tuyển dụng.',
                'declined' => $offer->declined_reason ?: 'Ứng viên đã từ chối đề nghị tuyển dụng.',
                'expired' => 'Đề nghị đã hết hạn phản hồi.',
                default => 'Đề nghị đang được xử lý.',
            },
        ];
    }

    private static function interviewStatus(Interview $interview): string
    {
        return match (true) {
            filled($interview->finalized_at) => 'Đã chốt kết quả',
            ($interview->result ?? 'pending') !== 'pending' => 'Đã có kết quả',
            blank($interview->invite_sent_at) => 'Chưa gửi lịch',
            $interview->scheduled_at?->isPast() => 'Chờ hoàn tất đánh giá',
            default => 'Sắp diễn ra',
        };
    }

    private static function interviewStatusTone(Interview $interview): string
    {
        return match (true) {
            filled($interview->finalized_at), ($interview->result ?? 'pending') !== 'pending' => 'success',
            blank($interview->invite_sent_at) => 'warning',
            $interview->scheduled_at?->isPast() => 'danger',
            default => 'info',
        };
    }

    private static function offerStatusTone(?string $status, Offer $offer): string
    {
        if ($status === 'pending' && $offer->expires_at?->isPast()) {
            return 'danger';
        }

        return match ($status) {
            'accepted' => 'success',
            'pending' => 'info',
            'draft', 'awaiting_approval' => 'warning',
            'rejected', 'declined' => 'danger',
            default => 'gray',
        };
    }

    private static function statusEnum(Application $record): ?StatusApplicationEnum
    {
        return $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);
    }

    private static function sourceLabel(?string $state): string
    {
        return match ($state) {
            'website' => 'Website', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn',
            'referral' => 'Giới thiệu', 'other' => 'Khác', default => $state ?: 'Không xác định',
        };
    }

    private static function applyMethodLabel(?string $state): string
    {
        return match ($state) {
            'profile' => 'Hồ sơ ứng viên', 'cv' => 'CV tải lên', default => $state ?: 'Không xác định',
        };
    }

    private static function aiRecommendationLabel(?string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => 'Ưu tiên sơ tuyển', 'consider' => 'Cần đối chiếu thêm',
            'reject' => 'Chưa nên chuyển bước', default => 'Chưa có khuyến nghị',
        };
    }

    private static function scoreTone(?int $score): string
    {
        return match (true) {
            $score === null => 'gray', $score < 50 => 'danger', $score < 75 => 'warning', default => 'success',
        };
    }

    private static function statusLabel(?string $status): string
    {
        return $status ? (StatusApplicationEnum::tryFrom($status)?->getLabel() ?? $status) : 'Tạo hồ sơ';
    }

    private static function roleLabel(?string $role): ?string
    {
        return match ($role) {
            'super_admin', 'admin' => 'Quản trị hệ thống', 'director' => 'Giám đốc chi nhánh',
            'pm' => 'Quản lý phòng ban', 'hr' => 'Nhân sự tuyển dụng', default => $role,
        };
    }

    private static function contactChannelLabel(?string $channel, ?string $detail): string
    {
        $label = match ($channel) {
            'phone' => 'Điện thoại', 'email' => 'Email', 'zalo' => 'Zalo',
            'in_person' => 'Trực tiếp', 'other' => 'Khác', default => 'Chưa xác định',
        };

        return filled($detail) ? $label.' · '.$detail : $label;
    }

    private static function preScreeningOutcomeLabel(?string $outcome): string
    {
        return match ($outcome) {
            'passed', 'pass' => 'Đạt sơ tuyển',
            'follow_up' => 'Hẹn liên hệ lại',
            'rejected', 'reject' => 'Từ chối hồ sơ',
            default => 'Chưa xác định',
        };
    }

    private static function preScreeningOutcomeTone(?string $outcome): string
    {
        return match ($outcome) {
            'passed', 'pass' => 'success',
            'follow_up' => 'warning',
            'rejected', 'reject' => 'danger',
            default => 'gray',
        };
    }

    private static function preScreeningNote(?string $note, ?string $rejectionReason, ?string $outcome): ?string
    {
        $value = trim((string) ($note ?: $rejectionReason));

        if ($value === '') {
            return null;
        }

        $normalized = mb_strtolower($value);
        $redundantValues = match ($outcome) {
            'passed', 'pass' => ['đạt', 'đạt sơ tuyển', 'passed'],
            'follow_up' => ['hẹn liên hệ lại', 'cần liên hệ lại', 'follow up'],
            'rejected', 'reject' => ['từ chối', 'từ chối hồ sơ', 'rejected'],
            default => [],
        };

        return in_array($normalized, $redundantValues, true) ? null : $value;
    }

    private static function interviewTypeLabel(?string $type): string
    {
        return match ($type) {
            'online' => 'Trực tuyến', 'offline' => 'Tại cơ sở', default => 'Chưa xác định',
        };
    }

    private static function interviewResultLabel(?string $result): string
    {
        return match ($result) {
            'pass' => 'Đạt vòng', 'fail' => 'Không đạt', 'hold' => 'Cần xem xét thêm',
            'pending', null => 'Chưa có kết quả', default => $result,
        };
    }

    private static function conclusionLabel(?string $conclusion): string
    {
        return match ($conclusion) {
            'pass' => 'Đạt phỏng vấn', 'fail' => 'Không đạt', 'hold' => 'Cần xem xét thêm', default => 'Chưa có kết luận',
        };
    }

    private static function offerStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Bản nháp', 'awaiting_approval' => 'Chờ giám đốc duyệt',
            'pending' => 'Chờ ứng viên phản hồi', 'accepted' => 'Ứng viên đồng ý',
            'declined' => 'Ứng viên từ chối', 'expired' => 'Hết hạn phản hồi',
            'rejected' => 'Yêu cầu chỉnh sửa', default => $status ?: 'Chưa xác định',
        };
    }

    private static function formatDateTime(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        return $value->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y');
    }
}
