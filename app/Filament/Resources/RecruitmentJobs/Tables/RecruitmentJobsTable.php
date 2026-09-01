<?php

namespace App\Filament\Resources\RecruitmentJobs\Tables;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Models\Skill;
use App\Models\Workplace;
use App\Services\RecruitmentInternalNotificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid as FormGrid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Tin tuyển dụng')
                    ->description(fn (RecruitmentJob $record): string => static::jobContext($record))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap()
                    ->limit(56)
                    ->tooltip(fn (RecruitmentJob $record): string => $record->title),
                TextColumn::make('branch.name')
                    ->label('Chi nhánh')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->visible(fn (): bool => Auth::user()?->branchScopeId() === null),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(),
                TextColumn::make('positions_count')
                    ->label('Chỉ tiêu')
                    ->numeric()
                    ->suffix(' vị trí')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('applications_count')
                    ->label('Hồ sơ')
                    ->badge()
                    ->color(fn (int|string|null $state): string => (int) $state > 0 ? 'info' : 'gray')
                    ->formatStateUsing(fn (int|string|null $state): string => ((int) $state).' hồ sơ')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('deadline')
                    ->label('Hạn tuyển')
                    ->state(fn (RecruitmentJob $record): string => $record->deadline?->format('d/m/Y') ?? 'Không giới hạn')
                    ->description(fn (RecruitmentJob $record): string => static::deadlineContext($record))
                    ->color(fn (RecruitmentJob $record): string => static::deadlineColor($record))
                    ->sortable(),
                TextColumn::make('formatted_salary')
                    ->label('Mức lương')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->label('Người tạo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn (RecruitmentJob $record): string => RecruitmentJobResource::getUrl('view', ['record' => $record]))
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn (): array => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->visible(fn (): bool => (bool) Auth::user()?->hasRole('super_admin')),
                SelectFilter::make('department_id')
                    ->label('Phòng ban')
                    ->options(fn (): array => Department::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('workplace_id')
                    ->label('Nơi làm việc')
                    ->options(fn (): array => Workplace::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                SelectFilter::make('skills')
                    ->label('Kỹ năng')
                    ->options(fn (): array => Skill::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['values'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'skills',
                            fn (Builder $skillQuery): Builder => $skillQuery->whereIn('skills.id', $data['values']),
                        );
                    }),
                SelectFilter::make('categories')
                    ->label('Danh mục')
                    ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['values'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'categories',
                            fn (Builder $categoryQuery): Builder => $categoryQuery->whereIn('categories.id', $data['values']),
                        );
                    }),
                Filter::make('deadline_range')
                    ->label('Hạn nộp hồ sơ')
                    ->form([
                        FormGrid::make(2)
                            ->schema([
                                DatePicker::make('deadline_from')
                                    ->label('Từ ngày')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                DatePicker::make('deadline_until')
                                    ->label('Đến ngày')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['deadline_from'] ?? null)) {
                            $query->whereDate('deadline', '>=', $data['deadline_from']);
                        }

                        if (filled($data['deadline_until'] ?? null)) {
                            $query->whereDate('deadline', '<=', $data['deadline_until']);
                        }

                        return $query;
                    }),
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Chỉnh sửa nội dung')
                        ->visible(fn (RecruitmentJob $record): bool => RecruitmentJobResource::canEdit($record)),
                    ...static::lifecycleActions(),
                    Action::make('viewPublicLink')
                        ->label('Xem tin công khai')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('info')
                        ->url(fn (RecruitmentJob $record): string => route('jobs.public', ['slug' => $record->slug]))
                        ->openUrlInNewTab()
                        ->visible(fn (RecruitmentJob $record): bool => filled($record->slug)
                            && $record->status === StatusRecruitmentJobsEnum::PUBLISHED),
                    DeleteAction::make()
                        ->label('Xóa bản nháp')
                        ->visible(fn (RecruitmentJob $record): bool => RecruitmentJobResource::canDelete($record)),
                ])
                    ->label('Thao tác')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->tooltip('Thao tác khác')
                    ->iconButton(),
            ], position: RecordActionsPosition::AfterColumns)
            ->recordActionsColumnLabel('Thao tác')
            ->recordActionsAlignment('center')
            ->paginationPageOptions([10, 25, 50]);
    }

    /** @return array<int, Action> */
    public static function lifecycleActions(): array
    {
        return [
            static::submitForApprovalAction(),
            static::approveAndPublishAction(),
            static::requestRevisionAction(),
            static::closeRecruitmentAction(),
            static::reopenRecruitmentAction(),
            static::extendAndReopenAction(),
            static::returnToDraftAction(),
            static::archiveAction(),
            static::restoreDraftAction(),
        ];
    }

    protected static function submitForApprovalAction(): Action
    {
        return static::transitionAction(
            name: 'submitForApproval',
            label: 'Gửi duyệt',
            icon: 'heroicon-o-paper-airplane',
            color: 'warning',
            from: [StatusRecruitmentJobsEnum::DRAFT],
            to: StatusRecruitmentJobsEnum::PENDING,
            heading: 'Gửi tin tuyển dụng để phê duyệt?',
            description: 'Tin sẽ được khóa chỉnh sửa trong thời gian chờ người có thẩm quyền xử lý.',
            notification: 'Đã gửi tin chờ phê duyệt',
            after: function (RecruitmentJob $record): void {
                app(RecruitmentInternalNotificationService::class)
                    ->notifyRecruitmentJobSubmittedForApproval($record, Auth::user());
            },
        );
    }

    protected static function approveAndPublishAction(): Action
    {
        return static::transitionAction(
            name: 'approveAndPublish',
            label: 'Duyệt và đăng',
            icon: 'heroicon-o-check-circle',
            color: 'success',
            from: [StatusRecruitmentJobsEnum::PENDING],
            to: StatusRecruitmentJobsEnum::PUBLISHED,
            heading: 'Duyệt và công khai tin tuyển dụng?',
            description: 'Ứng viên sẽ nhìn thấy tin và có thể nộp hồ sơ ngay sau khi duyệt.',
            notification: 'Tin tuyển dụng đã được duyệt và công khai',
            requiresApprover: true,
        );
    }

    protected static function requestRevisionAction(): Action
    {
        return static::transitionAction(
            name: 'requestRevision',
            label: 'Yêu cầu chỉnh sửa',
            icon: 'heroicon-o-arrow-uturn-left',
            color: 'warning',
            from: [StatusRecruitmentJobsEnum::PENDING],
            to: StatusRecruitmentJobsEnum::DRAFT,
            heading: 'Chuyển tin về bản nháp?',
            description: 'HR có thể chỉnh sửa nội dung và gửi duyệt lại sau đó.',
            notification: 'Tin đã được chuyển về bản nháp để chỉnh sửa',
            requiresApprover: true,
        );
    }

    protected static function closeRecruitmentAction(): Action
    {
        return static::transitionAction(
            name: 'closeRecruitment',
            label: 'Đóng tuyển',
            icon: 'heroicon-o-lock-closed',
            color: 'danger',
            from: [StatusRecruitmentJobsEnum::PUBLISHED],
            to: StatusRecruitmentJobsEnum::CLOSED,
            heading: 'Đóng đợt tuyển dụng này?',
            description: 'Tin sẽ ngừng hiển thị công khai và không nhận thêm hồ sơ mới.',
            notification: 'Đã đóng tuyển',
        );
    }

    protected static function reopenRecruitmentAction(): Action
    {
        return static::transitionAction(
            name: 'reopenRecruitment',
            label: 'Mở lại tuyển',
            icon: 'heroicon-o-arrow-path',
            color: 'success',
            from: [StatusRecruitmentJobsEnum::CLOSED],
            to: StatusRecruitmentJobsEnum::PUBLISHED,
            heading: 'Mở lại đợt tuyển dụng?',
            description: 'Tin sẽ được công khai và tiếp tục nhận hồ sơ.',
            notification: 'Đã mở lại tuyển',
            extraVisible: fn (RecruitmentJob $record): bool => ! static::hasPastDeadline($record),
        );
    }

    protected static function extendAndReopenAction(): Action
    {
        return Action::make('extendAndReopen')
            ->label('Gia hạn và mở lại')
            ->icon('heroicon-o-calendar-days')
            ->color('success')
            ->visible(fn (RecruitmentJob $record): bool => RecruitmentJobResource::canManageLifecycle($record)
                && ($record->status === StatusRecruitmentJobsEnum::EXPIRED
                    || ($record->status === StatusRecruitmentJobsEnum::CLOSED && static::hasPastDeadline($record))))
            ->modalHeading('Gia hạn và mở lại tuyển dụng')
            ->modalDescription('Chọn hạn nhận hồ sơ mới trước khi công khai lại tin.')
            ->form([
                DatePicker::make('deadline')
                    ->label('Hạn nhận hồ sơ mới')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->minDate(now()->startOfDay())
                    ->default(now()->addDays(7)->startOfDay())
                    ->required(),
            ])
            ->action(function (RecruitmentJob $record, array $data): void {
                abort_unless(RecruitmentJobResource::canManageLifecycle($record), 403);
                abort_unless(
                    $record->status === StatusRecruitmentJobsEnum::EXPIRED
                    || ($record->status === StatusRecruitmentJobsEnum::CLOSED && static::hasPastDeadline($record)),
                    409,
                );

                $record->update([
                    'deadline' => $data['deadline'],
                    'status' => StatusRecruitmentJobsEnum::PUBLISHED,
                ]);

                static::notify('Đã gia hạn và mở lại tuyển', $record);
            })
            ->modalSubmitActionLabel('Gia hạn và mở lại');
    }

    protected static function archiveAction(): Action
    {
        return static::transitionAction(
            name: 'archiveRecruitment',
            label: 'Lưu trữ',
            icon: 'heroicon-o-archive-box',
            color: 'gray',
            from: [
                StatusRecruitmentJobsEnum::DRAFT,
                StatusRecruitmentJobsEnum::CLOSED,
                StatusRecruitmentJobsEnum::EXPIRED,
            ],
            to: StatusRecruitmentJobsEnum::ARCHIVED,
            heading: 'Lưu trữ tin tuyển dụng?',
            description: 'Tin được giữ lại để tra cứu nhưng không còn nằm trong nhóm đang vận hành.',
            notification: 'Đã lưu trữ tin tuyển dụng',
        );
    }

    protected static function returnToDraftAction(): Action
    {
        return static::transitionAction(
            name: 'returnToDraft',
            label: 'Chuyển về bản nháp',
            icon: 'heroicon-o-pencil-square',
            color: 'warning',
            from: [
                StatusRecruitmentJobsEnum::CLOSED,
                StatusRecruitmentJobsEnum::EXPIRED,
            ],
            to: StatusRecruitmentJobsEnum::DRAFT,
            heading: 'Chuyển tin về bản nháp để chỉnh sửa?',
            description: 'Sau khi chỉnh sửa, tin cần được gửi duyệt lại trước khi công khai.',
            notification: 'Tin đã được chuyển về bản nháp',
        );
    }

    protected static function restoreDraftAction(): Action
    {
        return static::transitionAction(
            name: 'restoreDraft',
            label: 'Khôi phục bản nháp',
            icon: 'heroicon-o-arrow-uturn-left',
            color: 'gray',
            from: [StatusRecruitmentJobsEnum::ARCHIVED],
            to: StatusRecruitmentJobsEnum::DRAFT,
            heading: 'Khôi phục tin về bản nháp?',
            description: 'Tin có thể được chỉnh sửa và gửi duyệt lại sau khi khôi phục.',
            notification: 'Đã khôi phục tin về bản nháp',
        );
    }

    /** @param array<int, StatusRecruitmentJobsEnum> $from */
    protected static function transitionAction(
        string $name,
        string $label,
        string $icon,
        string $color,
        array $from,
        StatusRecruitmentJobsEnum $to,
        string $heading,
        string $description,
        string $notification,
        bool $requiresApprover = false,
        ?\Closure $extraVisible = null,
        ?\Closure $after = null,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible(function (RecruitmentJob $record) use ($from, $requiresApprover, $extraVisible): bool {
                $authorized = $requiresApprover
                    ? static::canApprove($record)
                    : RecruitmentJobResource::canManageLifecycle($record);

                return $authorized
                    && in_array($record->status, $from, true)
                    && ($extraVisible === null || $extraVisible($record));
            })
            ->requiresConfirmation()
            ->modalHeading($heading)
            ->modalDescription($description)
            ->action(function (RecruitmentJob $record) use ($from, $to, $requiresApprover, $notification, $after): void {
                abort_unless(
                    $requiresApprover ? static::canApprove($record) : RecruitmentJobResource::canManageLifecycle($record),
                    403,
                );
                abort_unless(in_array($record->status, $from, true), 409);

                $record->update(['status' => $to]);
                $after?->__invoke($record);
                static::notify($notification, $record);
            })
            ->modalSubmitActionLabel($label);
    }

    protected static function canApprove(RecruitmentJob $record): bool
    {
        $user = Auth::user();

        if (! $user || ! RecruitmentJobResource::canManageLifecycle($record)) {
            return false;
        }

        return in_array($user->role, ['admin', 'director'], true)
            || $user->hasAnyRole(['super_admin', 'director']);
    }

    protected static function hasPastDeadline(RecruitmentJob $record): bool
    {
        return $record->deadline?->copy()->startOfDay()->isBefore(now()->startOfDay()) ?? false;
    }

    protected static function notify(string $title, RecruitmentJob $record): void
    {
        Notification::make()
            ->title($title)
            ->body($record->title)
            ->success()
            ->send();
    }

    protected static function jobContext(RecruitmentJob $record): string
    {
        return collect([
            $record->department?->name,
            $record->workplace?->name,
        ])->filter()->join(' · ') ?: 'Chưa gắn phòng ban hoặc nơi làm việc';
    }

    protected static function deadlineContext(RecruitmentJob $record): string
    {
        if (! $record->deadline) {
            return 'Không đặt hạn nhận hồ sơ';
        }

        $days = (int) now()->startOfDay()->diffInDays($record->deadline->copy()->startOfDay(), false);

        return match (true) {
            $days < 0 => 'Quá hạn '.abs($days).' ngày',
            $days === 0 => 'Hết hạn hôm nay',
            $days === 1 => 'Còn 1 ngày',
            default => 'Còn '.$days.' ngày',
        };
    }

    protected static function deadlineColor(RecruitmentJob $record): string
    {
        if (! $record->deadline) {
            return 'gray';
        }

        $days = (int) now()->startOfDay()->diffInDays($record->deadline->copy()->startOfDay(), false);

        return match (true) {
            $days < 0 => 'danger',
            $days <= 3 => 'warning',
            default => 'gray',
        };
    }
}
