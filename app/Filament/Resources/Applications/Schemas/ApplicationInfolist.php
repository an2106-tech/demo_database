<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin ứng tuyển')
                ->description('Tổng quan hồ sơ, vị trí và trạng thái xử lý hiện tại.')
                ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('candidate_name')
                        ->label('Ứng viên')
                        ->state(fn (Application $record): string => $record->snapshotCandidateName()),
                    TextEntry::make('job.title')
                        ->label('Vị trí ứng tuyển')
                        ->placeholder('-'),
                    TextEntry::make('job.branch.name')
                        ->label('Chi nhánh')
                        ->placeholder('-')
                        ->columnSpan(['default' => 1, 'md' => 2, 'xl' => 1]),
                    TextEntry::make('status')
                        ->label('Trạng thái hiện tại')
                        ->badge()
                        ->formatStateUsing(fn (Application $record): string => static::statusLabel($record))
                        ->color(fn (Application $record): string => static::statusColor($record)),
                    TextEntry::make('candidate_email')
                        ->label('Email liên hệ')
                        ->state(fn (Application $record): string => $record->snapshotCandidateEmail() ?: '-')
                        ->copyable(),
                    TextEntry::make('candidate_phone')
                        ->label('Số điện thoại')
                        ->state(fn (Application $record): string => $record->snapshotCandidatePhone() ?: '-')
                        ->copyable(),
                    TextEntry::make('applied_at')
                        ->label('Ngày ứng tuyển')
                        ->dateTime('d/m/Y H:i')
                        ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                        ->placeholder('-'),
                    TextEntry::make('apply_method')
                        ->label('Cách nộp')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => static::applyMethodLabel($state)),
                    TextEntry::make('source')
                        ->label('Nguồn')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => static::sourceLabel($state)),
                ]),

            Grid::make(['default' => 1, 'xl' => 12])
                ->columnSpanFull()
                ->schema([
                    Section::make('Hồ sơ tại thời điểm nộp')
                        ->description('CV và snapshot dùng làm căn cứ đánh giá ứng viên.')
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextEntry::make('cv')
                                ->label('CV ứng tuyển')
                                ->state(fn (Application $record): string => $record->submittedCvName() ?: 'Chưa có CV')
                                ->url(fn (Application $record): ?string => $record->submittedCvUrl())
                                ->openUrlInNewTab()
                                ->icon(fn (Application $record): ?string => $record->submittedCvUrl() ? 'heroicon-o-document-text' : null)
                                ->columnSpanFull(),
                            TextEntry::make('snapshot_profile_title')
                                ->label('Tiêu đề hồ sơ')
                                ->state(fn (Application $record): string => $record->snapshotProfileTitle() ?: '-'),
                            TextEntry::make('snapshot_experience')
                                ->label('Kinh nghiệm')
                                ->state(fn (Application $record): string => static::snapshotExperience($record)),
                            TextEntry::make('salary_expected')
                                ->label('Lương mong muốn')
                                ->state(fn (Application $record): string => static::salaryExpected($record)),
                            TextEntry::make('cv_text_snapshot')
                                ->label('Trích xuất CV')
                                ->placeholder('Chưa có dữ liệu trích xuất')
                                ->limit(360)
                                ->prose()
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 7]),

                    Section::make('Trạng thái xử lý')
                        ->description('Thông tin cần xem ở giai đoạn hiện tại.')
                        ->schema([
                            TextEntry::make('current_step')
                                ->hiddenLabel()
                                ->state(fn (Application $record): HtmlString => static::currentStepHtml($record))
                                ->html(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 5]),
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

        return match ($status) {
            StatusApplicationEnum::CV_REVIEWING => static::screeningStepHtml($record),
            StatusApplicationEnum::SCREENING => static::interviewPreparationStepHtml($record),
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::INTERVIEWING => static::interviewStepHtml($record),
            StatusApplicationEnum::OFFERED => static::offerStepHtml($record),
            StatusApplicationEnum::HIRED => static::finalStepHtml('Đã tuyển', 'Hồ sơ đã hoàn tất tuyển dụng.', 'success'),
            StatusApplicationEnum::REJECTED => static::finalStepHtml('Từ chối', $record->rejected_reason ?: 'Hồ sơ đã dừng trong pipeline.', 'danger'),
            default => static::finalStepHtml('Chưa xác định', 'Chưa có trạng thái hợp lệ cho hồ sơ này.', 'gray'),
        };
    }

    protected static function screeningStepHtml(Application $record): HtmlString
    {
        return static::panelHtml(
            'Sàng lọc CV',
            'Cần quyết định hồ sơ có đủ điều kiện đi tiếp sang sơ tuyển hay không.',
            [
                'Căn cứ chính' => $record->submittedCvName() ?: 'Chưa có CV',
                'Thông tin đối chiếu' => static::snapshotExperience($record),
                'Quyết định cần ghi nhận' => 'Đạt sơ tuyển hoặc từ chối hồ sơ.',
            ]
        );
    }

    protected static function interviewPreparationStepHtml(Application $record): HtmlString
    {
        $comment = static::latestHistoryComment($record, StatusApplicationEnum::SCREENING->value);

        return static::panelHtml(
            'Sơ tuyển',
            'Hồ sơ đã qua sàng lọc CV và đang chờ sắp xếp phỏng vấn.',
            [
                'Ghi chú sàng lọc' => $comment ?: '-',
                'Người liên hệ' => $record->snapshotCandidateEmail() ?: $record->snapshotCandidatePhone() ?: '-',
                'Việc cần làm' => 'Tạo lịch phỏng vấn và gửi thư mời.',
            ]
        );
    }

    protected static function interviewStepHtml(Application $record): HtmlString
    {
        $interview = $record->latestInterview;
        $scorecard = $record->latestScorecard;
        $inviteSentAt = $interview?->invite_sent_at
            ? $interview->invite_sent_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i')
            : null;

        return static::panelHtml(
            'Phỏng vấn',
            $scorecard ? 'Đã có kết quả đánh giá phỏng vấn gần nhất.' : 'Đang chờ ghi nhận kết quả phỏng vấn.',
            [
                'Vòng phỏng vấn' => $interview?->round_name ?: '-',
                'Thời gian' => $interview?->scheduled_at
                    ? $interview->scheduled_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i')
                    : '-',
                'Trạng thái gửi lịch' => $inviteSentAt ? 'Đã gửi email lịch lúc '.$inviteSentAt : 'Chưa gửi email lịch phỏng vấn',
                'Người phỏng vấn' => $interview?->interviewer?->name ?: '-',
                'Điểm trung bình' => $scorecard?->average_score !== null ? (string) $scorecard->average_score : 'Chưa chấm',
                'Kết luận' => static::scorecardConclusionLabel($scorecard?->conclusion),
                'Việc cần làm' => $scorecard
                    ? 'Xem kết luận và chuyển bước phù hợp.'
                    : ($inviteSentAt ? 'Chờ/chấm scorecard phỏng vấn sau buổi phỏng vấn.' : 'Gửi lịch phỏng vấn cho ứng viên và người liên quan.'),
            ]
        );
    }

    protected static function offerStepHtml(Application $record): HtmlString
    {
        $offer = $record->latestOffer;

        return static::panelHtml(
            'Đề nghị tuyển dụng',
            'Hồ sơ đã qua đánh giá và đang ở bước đề nghị tuyển dụng.',
            [
                'Trạng thái đề nghị' => static::offerStatusLabel($offer?->status),
                'Lương đề nghị' => $offer?->salary_offered !== null ? number_format((float) $offer->salary_offered, 0, ',', '.').' VND' : '-',
                'Ngày bắt đầu' => $offer?->start_date?->format('d/m/Y') ?: '-',
                'Hạn phản hồi' => $offer?->expires_at?->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i') ?: '-',
                'Người duyệt' => $offer?->approvedByUser?->name ?: '-',
                'Việc cần làm' => static::offerNextAction($offer?->status),
            ]
        );
    }

    protected static function finalStepHtml(string $title, string $description, string $color): HtmlString
    {
        return static::panelHtml($title, $description, [
            'Trạng thái' => $title,
        ], $color);
    }

    /**
     * @param array<string, string> $items
     */
    protected static function panelHtml(string $title, string $description, array $items, string $color = 'primary'): HtmlString
    {
        $accentClasses = match ($color) {
            'success' => 'border-success-500',
            'danger' => 'border-danger-500',
            'gray' => 'border-gray-500',
            default => 'border-warning-500',
        };

        $rows = collect($items)
            ->map(fn (string $value, string $label): string => '<div class="grid gap-1 py-2.5">'
                .'<div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">'.e($label).'</div>'
                .'<div class="break-words text-sm font-normal leading-6 text-gray-950 dark:text-gray-100">'.e($value).'</div>'
                .'</div>')
            ->implode('');

        return new HtmlString(
            '<div class="space-y-3">'
            .'<div class="border-l-4 py-1 pl-3 '.$accentClasses.'">'
            .'<div class="text-sm font-semibold leading-5 text-gray-950 dark:text-white">'.e($title).'</div>'
            .'<div class="mt-1 text-sm font-normal leading-6 text-gray-600 dark:text-gray-300">'.e($description).'</div>'
            .'</div>'
            .'<div class="divide-y divide-gray-200 dark:divide-gray-800">'.$rows.'</div>'
            .'</div>'
        );
    }

    protected static function statusLabel(Application $record): string
    {
        return static::statusEnum($record)?->getLabel() ?? '-';
    }

    protected static function statusColor(Application $record): string
    {
        return static::statusEnum($record)?->getColor() ?? 'gray';
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
            'draft' => 'Đề nghị đang là bản nháp. Bấm gửi duyệt để chuyển cho giám đốc chi nhánh.',
            'awaiting_approval' => 'Chờ giám đốc duyệt đề nghị.',
            'pending' => 'Chờ ứng viên phản hồi thư mời.',
            'accepted' => 'Hoàn tất tuyển dụng nếu ứng viên đã xác nhận nhận việc.',
            'declined' => 'Xem lý do từ chối và kết thúc hoặc tạo đề nghị mới nếu phù hợp.',
            'expired' => 'Kiểm tra hạn phản hồi và gửi lại nếu cần.',
            'rejected' => 'Điều chỉnh đề nghị theo lý do từ chối của giám đốc.',
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
