<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Services\ApplicationWorkflowSummaryService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin ứng tuyển')
                ->description('Thông tin chính của hồ sơ và vị trí ứng tuyển.')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('application_overview')
                        ->hiddenLabel()
                        ->state(fn (Application $record): HtmlString => static::overviewHtml($record))
                        ->html(),
                ]),

            Section::make('Tiến độ xử lý')
                ->description('Trạng thái hiện tại và bước xử lý tiếp theo.')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('current_step')
                        ->hiddenLabel()
                        ->state(fn (Application $record): HtmlString => static::currentStepHtml($record))
                        ->html(),
                ]),

            Section::make('Hồ sơ ứng tuyển')
                ->description('CV và thông tin hồ sơ được lưu tại thời điểm nộp.')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('application_snapshot')
                        ->hiddenLabel()
                        ->state(fn (Application $record): HtmlString => static::snapshotHtml($record))
                        ->html(),
                ]),

            Section::make('Nhật ký xử lý')
                ->description('Các lần cập nhật trạng thái và ghi chú xử lý gần nhất.')
                ->collapsed()
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('status_history')
                        ->hiddenLabel()
                        ->state(fn (Application $record): HtmlString => static::statusHistoryHtml($record))
                        ->html(),
                ]),
        ]);
    }

    protected static function currentStepHtml(Application $record): HtmlString
    {
        $status = static::statusEnum($record);
        $summary = static::workflowSummary($record);

        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => static::screeningStepHtml($record),
            StatusApplicationEnum::SCREENING => static::interviewPreparationStepHtml($record),
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::INTERVIEWING => static::interviewStepHtml($record),
            StatusApplicationEnum::OFFERED => static::offerStepHtml($record),
            StatusApplicationEnum::HIRED,
            StatusApplicationEnum::REJECTED,
            StatusApplicationEnum::WITHDRAWN => static::finalStepHtml($summary['status_label'], $summary['description'], $summary['color']),
            default => static::finalStepHtml('Chưa xác định', 'Chưa có trạng thái hợp lệ cho hồ sơ này.', 'gray'),
        };
    }

    protected static function overviewHtml(Application $record): HtmlString
    {
        $summary = static::workflowSummary($record);
        $appliedAt = $record->applied_at
            ? $record->applied_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i')
            : '-';

        $meta = [
            'Email liên hệ' => $record->snapshotCandidateEmail() ?: '-',
            'Số điện thoại' => $record->snapshotCandidatePhone() ?: '-',
            'Ngày nộp' => $appliedAt,
            'Nguồn hồ sơ' => static::sourceLabel($record->source).' · '.static::applyMethodLabel($record->apply_method),
        ];

        $metaHtml = collect($meta)
            ->map(fn (string $value, string $label): string => static::compactInfoHtml($label, $value))
            ->implode('');

        return new HtmlString(
            '<div class="grid gap-4 xl:grid-cols-3">'
            .'<div class="min-w-0 xl:col-span-2">'
            .'<div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Ứng viên</div>'
            .'<div class="mt-1 text-xl font-semibold leading-7 text-gray-950 dark:text-white">'.e($record->snapshotCandidateName() ?: '-').'</div>'
            .'<div class="mt-3 grid gap-3 md:grid-cols-2">'
            .static::compactInfoHtml('Vị trí ứng tuyển', $record->job?->title ?: '-')
            .static::compactInfoHtml('Chi nhánh', $record->job?->branch?->name ?: '-')
            .'</div>'
            .'</div>'
            .'<div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">'
            .'<div class="flex flex-wrap items-center justify-between gap-3">'
            .'<div>'
            .'<div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Giai đoạn</div>'
            .'<div class="mt-2">'.static::badgeHtml($summary['stage_label'], $summary['color']).'</div>'
            .'</div>'
            .'<div class="text-right text-sm font-semibold leading-5 text-gray-950 dark:text-white">'.e($summary['status_label']).'</div>'
            .'</div>'
            .'<div class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">'.e($summary['description']).'</div>'
            .'</div>'
            .'</div>'
            .'<div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">'.$metaHtml.'</div>'
        );
    }

    protected static function snapshotHtml(Application $record): HtmlString
    {
        $cvName = $record->submittedCvName() ?: 'Chưa có CV';
        $cvUrl = $record->submittedCvUrl();
        $cvAction = $cvUrl
            ? '<a href="'.e($cvUrl).'" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">Mở CV</a>'
            : '<span class="text-sm text-gray-500 dark:text-gray-400">Chưa có file CV</span>';

        return new HtmlString(
            '<div class="space-y-3">'
            .'<div class="flex min-w-0 flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">'
            .'<div class="min-w-0">'
            .'<div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">CV ứng tuyển</div>'
            .'<div class="mt-1 break-words text-sm font-semibold leading-6 text-gray-950 dark:text-white">'.e($cvName).'</div>'
            .'</div>'
            .$cvAction
            .'</div>'
            .'<div class="grid gap-3 md:grid-cols-3">'
            .static::compactInfoHtml('Tiêu đề hồ sơ', $record->snapshotProfileTitle() ?: '-')
            .static::compactInfoHtml('Kinh nghiệm', static::snapshotExperience($record))
            .static::compactInfoHtml('Lương mong muốn', static::salaryExpected($record))
            .'</div>'
            .'</div>'
        );
    }

    protected static function compactInfoHtml(string $label, string $value): string
    {
        return '<div class="min-w-0 rounded-lg border border-gray-200 px-3 py-2.5 dark:border-white/10">'
            .'<div class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">'.e($label).'</div>'
            .'<div class="mt-1 break-words text-sm font-medium leading-5 text-gray-950 dark:text-gray-100">'.e($value).'</div>'
            .'</div>';
    }

    protected static function badgeHtml(string $label, string $color): string
    {
        $style = match ($color) {
            'success' => 'background:rgba(34,197,94,.12);color:#16a34a;border-color:rgba(34,197,94,.25);',
            'danger' => 'background:rgba(239,68,68,.12);color:#dc2626;border-color:rgba(239,68,68,.25);',
            'info', 'primary' => 'background:rgba(59,130,246,.12);color:#2563eb;border-color:rgba(59,130,246,.25);',
            'warning' => 'background:rgba(245,158,11,.14);color:#d97706;border-color:rgba(245,158,11,.28);',
            default => 'background:rgba(148,163,184,.13);color:inherit;border-color:rgba(148,163,184,.25);',
        };

        return '<span style="display:inline-flex;align-items:center;border:1px solid;border-radius:999px;padding:.25rem .65rem;font-size:.75rem;font-weight:700;'.$style.'">'.e($label).'</span>';
    }

    protected static function screeningStepHtml(Application $record): HtmlString
    {
        $summary = static::workflowSummary($record);

        return static::panelHtml(
            $summary['status_label'],
            $summary['description'],
            [
                'Quyết định' => 'Đạt sơ tuyển hoặc từ chối.',
            ],
            $summary['color'],
        );
    }

    protected static function interviewPreparationStepHtml(Application $record): HtmlString
    {
        $comment = static::latestHistoryComment($record, StatusApplicationEnum::SCREENING->value);
        $summary = static::workflowSummary($record);

        return static::panelHtml(
            $summary['status_label'],
            $summary['description'],
            [
                'Ghi chú sàng lọc' => $comment ?: '-',
                'Liên hệ ứng viên' => $record->snapshotCandidateEmail() ?: $record->snapshotCandidatePhone() ?: '-',
                'Bước tiếp theo' => 'Tạo lịch phỏng vấn và gửi thư mời.',
            ],
            $summary['color'],
        );
    }

    protected static function interviewStepHtml(Application $record): HtmlString
    {
        $interview = $record->latestInterview;
        $scorecard = $record->latestScorecard;
        $summary = static::workflowSummary($record);
        $inviteSentAt = $interview?->invite_sent_at
            ? $interview->invite_sent_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i')
            : null;

        return static::panelHtml(
            $summary['status_label'],
            $summary['description'],
            [
                'Vòng phỏng vấn' => $interview?->round_name ?: '-',
                'Thời gian' => $interview?->scheduled_at
                    ? $interview->scheduled_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i')
                    : '-',
                'Thư mời' => $inviteSentAt ? 'Đã gửi lúc '.$inviteSentAt : 'Chưa gửi',
                'Người phỏng vấn' => $interview?->interviewer?->name ?: '-',
                'Điểm' => $scorecard?->average_score !== null ? (string) $scorecard->average_score : 'Chưa chấm',
                'Kết luận' => static::scorecardConclusionLabel($scorecard?->conclusion),
                'Bước tiếp theo' => $scorecard
                    ? 'Xem kết luận và chuyển bước phù hợp.'
                    : ($inviteSentAt ? 'Chấm scorecard sau buổi phỏng vấn.' : 'Gửi lịch cho ứng viên và người liên quan.'),
            ],
            $summary['color'],
        );
    }

    protected static function offerStepHtml(Application $record): HtmlString
    {
        $offer = $record->latestOffer;
        $summary = static::workflowSummary($record);

        return static::panelHtml(
            $summary['status_label'],
            $summary['description'],
            [
                'Tình trạng đề nghị' => static::offerStatusLabel($offer?->status),
                'Lương đề nghị' => $offer?->salary_offered !== null ? number_format((float) $offer->salary_offered, 0, ',', '.').' VND' : '-',
                'Ngày bắt đầu' => $offer?->start_date?->format('d/m/Y') ?: '-',
                'Hạn phản hồi' => $offer?->expires_at?->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i') ?: '-',
                'Giám đốc duyệt' => $offer?->approvedByUser?->name ?: '-',
                'Bước tiếp theo' => static::offerNextAction($offer?->status),
            ],
            $summary['color'],
        );
    }

    protected static function finalStepHtml(string $title, string $description, string $color): HtmlString
    {
        return static::panelHtml($title, $description, [], $color);
    }

    /**
     * @param array<string, string> $items
     */
    protected static function panelHtml(string $title, string $description, array $items, string $color = 'primary'): HtmlString
    {
        $accentClasses = match ($color) {
            'success' => 'bg-success-500',
            'danger' => 'bg-danger-500',
            'gray' => 'bg-gray-500',
            default => 'bg-warning-500',
        };

        $rows = collect($items)
            ->map(fn (string $value, string $label): string => '<div class="min-w-0 rounded-lg border border-gray-200 px-3 py-2.5 dark:border-white/10">'
                .'<div class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">'.e($label).'</div>'
                .'<div class="mt-1 break-words text-sm font-medium leading-5 text-gray-950 dark:text-gray-100">'.e($value).'</div>'
                .'</div>')
            ->implode('');

        $itemsHtml = $rows !== ''
            ? '<div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">'.$rows.'</div>'
            : '';

        return new HtmlString(
            '<div class="space-y-3">'
            .'<div class="flex flex-wrap items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">'
            .'<div class="mt-1 h-10 w-1 rounded-full '.$accentClasses.'"></div>'
            .'<div class="min-w-0 flex-1">'
            .'<div class="text-base font-semibold leading-6 text-gray-950 dark:text-white">'.e($title).'</div>'
            .'<div class="mt-1 text-sm font-normal leading-6 text-gray-600 dark:text-gray-300">'.e($description).'</div>'
            .'</div>'
            .'</div>'
            .$itemsHtml
            .'</div>'
        );
    }

    protected static function statusLabel(Application $record): string
    {
        return static::workflowSummary($record)['status_label'];
    }

    protected static function statusColor(Application $record): string
    {
        return static::workflowSummary($record)['color'];
    }

    protected static function workflowSummary(Application $record): array
    {
        return app(ApplicationWorkflowSummaryService::class)->summarize($record);
    }

    protected static function statusEnum(Application $record): ?StatusApplicationEnum
    {
        return $record->status instanceof StatusApplicationEnum
            ? $record->status
            : StatusApplicationEnum::tryFrom((string) $record->status);
    }

    protected static function salaryExpected(Application $record): string
    {
        $salary = $record->salary_expected;

        if (! is_array($salary)) {
            return '-';
        }

        $min = isset($salary['min']) && $salary['min'] !== null
            ? number_format((float) $salary['min'], 0, ',', '.').' VND'
            : null;
        $max = isset($salary['max']) && $salary['max'] !== null
            ? number_format((float) $salary['max'], 0, ',', '.').' VND'
            : null;

        return match (true) {
            $min && $max => "{$min} - {$max}",
            $min !== null => "Từ {$min}",
            $max !== null => "Đến {$max}",
            default => '-',
        };
    }

    protected static function snapshotExperience(Application $record): string
    {
        $experience = $record->snapshotCandidateExperienceYears();

        return is_numeric($experience) ? $experience.' năm' : '-';
    }

    protected static function applyMethodLabel(?string $state): string
    {
        return match ($state) {
            'profile' => 'Hồ sơ ứng viên',
            'cv' => 'CV tải lên',
            default => $state ?: '-',
        };
    }

    protected static function sourceLabel(?string $state): string
    {
        return match ($state) {
            'website' => 'Website',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'referral' => 'Giới thiệu',
            'other' => 'Khác',
            default => $state ?: '-',
        };
    }

    protected static function scorecardConclusionLabel(?string $state): string
    {
        return match ($state) {
            'pass' => 'Đạt',
            'fail' => 'Không đạt',
            'hold' => 'Cần xem xét thêm',
            default => $state ?: 'Chưa có kết luận',
        };
    }

    protected static function offerStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Bản nháp',
            'awaiting_approval' => 'Chờ giám đốc duyệt',
            'pending' => 'Chờ ứng viên phản hồi',
            'accepted' => 'Ứng viên đồng ý',
            'declined' => 'Ứng viên từ chối',
            'expired' => 'Hết hạn phản hồi',
            'rejected' => 'Giám đốc từ chối',
            default => $status ?: 'Chưa có đề nghị',
        };
    }

    protected static function offerNextAction(?string $status): string
    {
        return match ($status) {
            'draft' => 'Gửi giám đốc chi nhánh duyệt.',
            'awaiting_approval' => 'Chờ giám đốc duyệt đề nghị.',
            'pending' => 'Chờ ứng viên phản hồi thư mời.',
            'accepted' => 'Hoàn tất tuyển dụng.',
            'declined' => 'Xem lý do và quyết định tạo đề nghị mới hoặc kết thúc hồ sơ.',
            'expired' => 'Kiểm tra hạn phản hồi và xử lý tiếp nếu cần.',
            'rejected' => 'Chỉnh sửa theo góp ý của giám đốc.',
            default => 'Tạo đề nghị tuyển dụng hoặc gửi duyệt.',
        };
    }

    protected static function latestHistoryComment(Application $record, string $toStatus): ?string
    {
        return $record->statusHistories()
            ->where('to_status', $toStatus)
            ->latest('id')
            ->value('comment');
    }

    protected static function statusHistoryHtml(Application $record): HtmlString
    {
        $histories = $record->statusHistories()
            ->with('user.branch')
            ->latest('id')
            ->limit(6)
            ->get();

        if ($histories->isEmpty()) {
            return new HtmlString('<div class="text-sm text-gray-500 dark:text-gray-400">Chưa có nhật ký xử lý.</div>');
        }

        $items = $histories
            ->map(function ($history): string {
                $from = static::statusText($history->from_status);
                $to = static::statusText($history->to_status);
                [$actorName, $actorMeta] = static::historyActorParts($history->user);
                $time = optional($history->created_at)
                    ->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                    ->format('d/m/Y H:i') ?: '-';
                $comment = trim((string) $history->comment);
                $badgeStyle = static::historyStatusStyle($history->to_status);
                $transition = $history->from_status
                    ? '<span style="opacity:.72">'.e($from).'</span><span style="opacity:.45"> → </span><span style="'.$badgeStyle.'">'.e($to).'</span>'
                    : '<span style="'.$badgeStyle.'">'.e('Tạo hồ sơ: '.$to).'</span>';

                return '<div style="border:1px solid rgba(148,163,184,.18); background:rgba(148,163,184,.045); border-radius:12px; padding:14px 16px; margin-bottom:10px;">'
                    .'<div class="flex flex-wrap items-center justify-between gap-3">'
                    .'<div class="text-sm font-medium leading-6" style="color:inherit">'.$transition.'</div>'
                    .'<div class="text-xs font-medium leading-6" style="opacity:.68">'.e($time).'</div>'
                    .'</div>'
                    .'<div class="mt-1 text-xs" style="opacity:.74">'.e($actorName).($actorMeta ? ' · <span style="opacity:.82">'.e($actorMeta).'</span>' : '').'</div>'
                    .($comment !== '' ? '<div class="text-sm leading-6" style="margin-top:12px; border-left:3px solid rgba(148,163,184,.28); padding-left:12px; color:inherit; opacity:.86">'.e($comment).'</div>' : '')
                    .'</div>';
            })
            ->implode('');

        return new HtmlString('<div>'.$items.'</div>');
    }

    protected static function statusText(?string $status): string
    {
        if (! $status) {
            return 'Tạo mới';
        }

        return StatusApplicationEnum::tryFrom($status)?->getLabel() ?? $status;
    }

    protected static function transitionText(?string $fromStatus, ?string $toStatus): string
    {
        $to = static::statusText($toStatus);

        if (! $fromStatus) {
            return 'Tạo hồ sơ ở trạng thái '.$to;
        }

        return static::statusText($fromStatus).' → '.$to;
    }

    protected static function historyStatusStyle(?string $status): string
    {
        $statusEnum = $status ? StatusApplicationEnum::tryFrom($status) : null;

        return match ($statusEnum?->getPipelineStageKey()) {
            'hired' => 'display:inline-flex;border-radius:999px;padding:.125rem .55rem;font-size:.75rem;font-weight:600;background:rgba(34,197,94,.12);color:#4ade80;',
            'rejected' => 'display:inline-flex;border-radius:999px;padding:.125rem .55rem;font-size:.75rem;font-weight:600;background:rgba(239,68,68,.12);color:#f87171;',
            'offer' => 'display:inline-flex;border-radius:999px;padding:.125rem .55rem;font-size:.75rem;font-weight:600;background:rgba(59,130,246,.12);color:#93c5fd;',
            'interview' => 'display:inline-flex;border-radius:999px;padding:.125rem .55rem;font-size:.75rem;font-weight:600;background:rgba(245,158,11,.13);color:#fbbf24;',
            'screening' => 'display:inline-flex;border-radius:999px;padding:.125rem .55rem;font-size:.75rem;font-weight:600;background:rgba(14,165,233,.12);color:#7dd3fc;',
            default => 'display:inline-flex;border-radius:999px;padding:.125rem .55rem;font-size:.75rem;font-weight:600;background:rgba(148,163,184,.12);color:inherit;',
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected static function historyActorParts(?\App\Models\User $user): array
    {
        if (! $user) {
            return ['Hệ thống tự động', ''];
        }

        $meta = array_filter([
            static::roleLabel($user->role),
            $user->branch?->name,
        ]);

        return [$user->name, implode(' · ', $meta)];
    }

    protected static function roleLabel(?string $role): ?string
    {
        return match ($role) {
            'super_admin', 'admin' => 'Quản trị hệ thống',
            'director' => 'Giám đốc chi nhánh',
            'pm' => 'Quản lý phòng ban',
            'hr' => 'Nhân sự tuyển dụng',
            'candidate' => 'Ứng viên',
            default => $role ?: null,
        };
    }
}
