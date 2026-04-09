<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\Workplace;
use App\Services\InterviewCalendarService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        $statusOptions = collect(StatusApplicationEnum::cases())
            ->mapWithKeys(fn (StatusApplicationEnum $status) => [$status->value => $status->getLabel()])
            ->all();

        return $table
            ->defaultSort('applied_at', 'desc')
            ->searchPlaceholder('Tìm theo ID, công việc, ứng viên...')
            ->columns([
                TextColumn::make('interview_action')
                    ->label('')
                    ->state(fn (Application $record): ?string => static::canManageInterview($record) ? 'Phỏng vấn' : null)
                    ->badge(fn (?string $state): bool => filled($state))
                    ->color(fn (Application $record): string => static::hasInterviewStatus($record) ? 'info' : 'warning')
                    ->alignCenter()
                    ->action(static::makeInterviewAction())
                    ->placeholder('-'),
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job.title')
                    ->label('Công việc')
                    ->sortable(),
                TextColumn::make('job.branch.name')
                    ->label('Chi nhánh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('candidate.name')
                    ->label('Ứng viên')
                    ->sortable(),
                TextColumn::make('cv_path')
                    ->label('CV')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Mo CV' : '-')
                    ->url(fn ($record) => $record->cv_path ? asset('storage/' . ltrim($record->cv_path, '/')) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('apply_method')
                    ->label('Cách nộp hồ sơ')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'profile' => 'Hồ sơ',
                        'cv' => 'CV',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'info' => 'profile',
                        'primary' => 'cv',
                    ]),
                TextColumn::make('source')
                    ->label('Nguồn')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'website' => 'Website',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'referral' => 'Giới thiệu',
                        'other' => 'Khác',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'primary' => 'website',
                        'info' => 'linkedin',
                        'success' => 'referral',
                        'warning' => 'facebook',
                        'gray' => 'other',
                    ]),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->sortable(),
                TextColumn::make('salary_expected')
                    ->label('Lương mon muốn')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || (($state['min'] ?? null) === null && ($state['max'] ?? null) === null)) {
                            return '-';
                        }

                        $min = isset($state['min']) && $state['min'] !== null
                            ? number_format((float) $state['min'], 0, ',', '.')
                            : null;
                        $max = isset($state['max']) && $state['max'] !== null
                            ? number_format((float) $state['max'], 0, ',', '.')
                            : null;

                        return match (true) {
                            $min && $max => "{$min} - {$max} VND",
                            $min !== null => "Từ {$min} VND",
                            $max !== null => "Đến {$max} VND",
                            default => '-',
                        };
                    }),
                TextColumn::make('applied_at')
                    ->label('Ngày ứng tuyển')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('job', fn (Builder $q) => $q->where('branch_id', $data['value']));
                    })
                    ->visible(fn () => (bool) Auth::user()?->hasRole('super_admin')),
                SelectFilter::make('job_id')
                    ->label('Công việc')
                    ->options(fn () => RecruitmentJob::query()->orderBy('title')->limit(500)->pluck('title', 'id')->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('job_id', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('candidate_id')
                    ->label('Ứng viên')
                    ->options(fn () => Candidate::query()->orderBy('name')->limit(500)->get()->mapWithKeys(fn (Candidate $candidate) => [
                        $candidate->id => "#{$candidate->id} - {$candidate->name}" . ($candidate->email ? " ({$candidate->email})" : ''),
                    ])->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('candidate_id', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('source')
                    ->label('Nguồn')
                    ->options([
                        'website' => 'Website',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'referral' => 'Giới thiệu',
                        'other' => 'Khác',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('source', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options($statusOptions)
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null)
                            ? $query->where('status', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('cv_state')
                    ->label('Tình trạng CV')
                    ->options([
                        'has_cv' => 'Da co CV',
                        'missing_cv' => 'Chua co CV',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'has_cv' => $query->whereNotNull('cv_path')->where('cv_path', '!=', ''),
                            'missing_cv' => $query->where(function (Builder $q): void {
                                $q->whereNull('cv_path')->orWhere('cv_path', '');
                            }),
                            default => $query,
                        };
                    }),
                Filter::make('applied_at_range')
                    ->label('Ngay ung tuyen')
                    ->form([
                        DatePicker::make('applied_from')
                            ->label('Tu ngay')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('applied_until')
                            ->label('Den ngay')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['applied_from'] ?? null),
                                fn (Builder $q) => $q->whereDate('applied_at', '>=', $data['applied_from'])
                            )
                            ->when(
                                filled($data['applied_until'] ?? null),
                                fn (Builder $q) => $q->whereDate('applied_at', '<=', $data['applied_until'])
                            );
                    }),
                TrashedFilter::make()
                    ->label('Ban ghi da xoa'),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->label('Xem')
                    ->modalContent(fn ($record) => view('filament.applications.application-view', ['record' => $record])),
                EditAction::make()
                    ->label('Sửa')
                    ->url(fn ($record): string => ApplicationResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Xoa da chon'),
                    ForceDeleteBulkAction::make()->label('Xoa vinh vien'),
                    RestoreBulkAction::make()->label('Khoi phuc'),
                ]),
            ]);
    }

    protected static function getInterviewFormData(Application $record): array
    {
        $interview = $record->latestInterview()->first();

        return [
            'scheduled_at' => $interview?->scheduled_at,
            'type' => $interview?->type ?? 'online',
            'meeting_link' => $interview?->meeting_link,
            'workplace_id' => $interview?->workplace_id,
            'interviewer_id' => $interview?->interviewer_id,
            'notes' => $interview?->notes,
        ];
    }

    protected static function canManageInterview(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum
            ? $record->status->value
            : $record->status;

        return in_array($status, [
            StatusApplicationEnum::SCREENING->value,
            StatusApplicationEnum::INTERVIEW->value,
        ], true);
    }

    protected static function hasInterviewStatus(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum
            ? $record->status->value
            : $record->status;

        return $status === StatusApplicationEnum::INTERVIEW->value;
    }

    protected static function getWorkplaceOptions(Application $record): array
    {
        $branchId = $record->job?->branch_id;

        if (! $branchId) {
            return [];
        }

        return Workplace::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Workplace $workplace): array => [
                $workplace->id => static::formatWorkplaceLabel($workplace),
            ])
            ->all();
    }

    protected static function getInterviewerOptions(Application $record): array
    {
        $branchId = $record->job?->branch_id;

        if (! $branchId) {
            return [];
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['director', 'pm', 'hr']))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => $user->name . ($user->role ? ' (' . strtoupper($user->role) . ')' : ''),
            ])
            ->all();
    }

    protected static function getInterviewRecipients(Application $record): array
    {
        $recipients = [];

        if (filled($record->candidate?->email)) {
            $recipients[$record->candidate->email] = 'candidate';
        }

        $branchId = $record->job?->branch_id;

        if (! $branchId) {
            return $recipients;
        }

        User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['director', 'pm']))
            ->get()
            ->each(function (User $user) use (&$recipients): void {
                if (filled($user->email)) {
                    $recipients[$user->email] = $user->role ?: 'internal';
                }
            });

        return $recipients;
    }

    protected static function formatWorkplaceLabel(Workplace $workplace): string
    {
        $parts = array_filter([
            $workplace->name,
            $workplace->room ? 'Phong ' . $workplace->room : null,
            $workplace->floor ? 'Tang ' . $workplace->floor : null,
        ]);

        return implode(' - ', $parts);
    }

    protected static function makeInterviewAction(): Action
    {
        return Action::make('interview')
            ->label('Phong van')
            ->icon('heroicon-o-calendar-days')
            ->color(fn (Application $record): string => static::hasInterviewStatus($record) ? 'info' : 'warning')
            ->modalWidth('3xl')
            ->modalHeading(fn (Application $record): string => $record->latestInterview()->exists() ? 'Dieu chinh lich phong van' : 'Tao lich phong van')
            ->modalDescription(fn (Application $record): string => 'Ho so #' . $record->id . ' - ' . ($record->candidate?->name ?? 'Ung vien'))
            ->fillForm(fn (Application $record): array => static::getInterviewFormData($record))
            ->form([
                DateTimePicker::make('scheduled_at')
                    ->label('Thời gian phỏng vấn')
                    ->native(false)
                    ->seconds(false)
                    ->required(),
                Select::make('type')
                    ->label('Hình thức phỏng vấn')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ])
                    ->default('online')
                    ->live()
                    ->required(),
                TextInput::make('meeting_link')
                    ->label('Link phỏng vấn')
                    ->url()
                    ->maxLength(500)
                    ->visible(fn (callable $get): bool => $get('type') === 'online')
                    ->required(fn (callable $get): bool => $get('type') === 'online'),
                Select::make('workplace_id')
                    ->label('Địa điểm phỏng vấn')
                    ->options(fn (Application $record): array => static::getWorkplaceOptions($record))
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => $get('type') === 'offline')
                    ->required(fn (callable $get): bool => $get('type') === 'offline'),
                Select::make('interviewer_id')
                    ->label('Người phỏng vấn')
                    ->options(fn (Application $record): array => static::getInterviewerOptions($record))
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('notes')
                    ->label('Ghi chu')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->action(function (Application $record, array $data): void {
                $existingInterview = $record->latestInterview()->first();
                $nextRound = max(1, $record->interviews()->count() + ($existingInterview ? 0 : 1));

                $interview = $existingInterview ?? new Interview([
                    'application_id' => $record->id,
                    'round_number' => $nextRound,
                    'round_name' => 'Phỏng vấn vòng ' . $nextRound,
                    'duration_minutes' => 60,
                    'result' => 'pending',
                ]);

                $interview->fill([
                    'application_id' => $record->id,
                    'interviewer_id' => $data['interviewer_id'],
                    'scheduled_at' => $data['scheduled_at'],
                    'type' => $data['type'],
                    'meeting_link' => $data['type'] === 'online' ? ($data['meeting_link'] ?? null) : null,
                    'workplace_id' => $data['type'] === 'offline' ? ($data['workplace_id'] ?? null) : null,
                    'notes' => $data['notes'] ?? null,
                ]);
                $interview->save();

                $interview->loadMissing(['application.job.branch', 'application.candidate', 'interviewer', 'workplace']);

                app(InterviewCalendarService::class)->store($interview);

                $record->forceFill([
                    'status' => StatusApplicationEnum::INTERVIEW,
                ])->save();

                $recipients = static::getInterviewRecipients($record->fresh(['job.branch', 'candidate']));
                $sentCount = 0;
                $failedCount = 0;

                foreach ($recipients as $email => $recipientLabel) {
                    try {
                        Mail::to($email)->send(new InterviewScheduledMail($interview, $recipientLabel));
                        $sentCount++;
                    } catch (\Throwable $exception) {
                        $failedCount++;

                        Log::warning('Failed to send interview schedule mail.', [
                            'application_id' => $record->id,
                            'interview_id' => $interview->id,
                            'recipient' => $email,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }

                if ($sentCount > 0) {
                    $interview->forceFill([
                        'invite_sent_at' => now(),
                    ])->save();
                }

                $notification = Notification::make()
                    ->title($existingInterview ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn');

                if ($failedCount > 0) {
                    $notification
                        ->warning()
                        ->body("Lịch phỏng vấn đã được lưu, gửi email thành công {$sentCount} và thất bại {$failedCount}.");
                } elseif ($sentCount === 0) {
                    $notification
                        ->warning()
                        ->body('Lịch phỏng vấn đã được lưu, nhưng không tìm thấy email để gửi thông báo.');
                } else {
                    $notification
                        ->success()
                        ->body("Đã lưu lịch phỏng vấn và gửi {$sentCount} email thông báo.");
                }

                $notification->send();
            })
            ->visible(fn (Application $record): bool => static::canManageInterview($record))
            ->disabled(fn (Application $record): bool => ! static::canManageInterview($record));
    }
}
