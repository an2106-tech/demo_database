<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Mail\CandidateOfferMail;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\OfferLetterTemplate;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\Workplace;
use App\Services\InterviewCalendarService;
use App\Services\OfferPdfService;
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
use Illuminate\Support\Facades\Storage;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        $statusOptions = collect(StatusApplicationEnum::cases())
            ->mapWithKeys(fn (StatusApplicationEnum $status) => [$status->value => (string) $status->getLabel()])
            ->all();

        return $table
            ->defaultSort('applied_at', 'desc')
            ->searchPlaceholder('Tìm theo ID, công việc, ứng viên...')
            ->columns([
                TextColumn::make('pipeline_action')
                    ->label('')
                    ->state(fn (Application $record): ?string => static::getPipelineActionLabel($record))
                    ->badge(fn (?string $state): bool => filled($state))
                    ->color(fn (Application $record): string => static::getPipelineActionColor($record))
                    ->alignCenter()
                    ->action(static::makePipelineAction())
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
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Mã CV' : '-')
                    ->url(fn ($record) => $record->cv_path ? asset('storage/'.ltrim($record->cv_path, '/')) : null)
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
                    ->label('Lương mong muốn')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || (($state['min'] ?? null) === null && ($state['max'] ?? null) === null)) {
                            return '-';
                        }

                        $min = isset($state['min']) && $state['min'] !== null ? number_format((float) $state['min'], 0, ',', '.') : null;
                        $max = isset($state['max']) && $state['max'] !== null ? number_format((float) $state['max'], 0, ',', '.') : null;

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
                    ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
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
                        return filled($data['value'] ?? null) ? $query->where('job_id', $data['value']) : $query;
                    }),
                SelectFilter::make('candidate_id')
                    ->label('Ứng viên')
                    ->options(fn () => Candidate::query()->orderBy('name')->limit(500)->get()->mapWithKeys(fn (Candidate $candidate) => [
                        $candidate->id => "#{$candidate->id} - {$candidate->name}".($candidate->email ? " ({$candidate->email})" : ''),
                    ])->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('candidate_id', $data['value']) : $query;
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
                        return filled($data['value'] ?? null) ? $query->where('source', $data['value']) : $query;
                    }),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options($statusOptions)
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('status', $data['value']) : $query;
                    }),
                SelectFilter::make('cv_state')
                    ->label('Tình trạng CV')
                    ->options([
                        'has_cv' => 'Đã có CV',
                        'missing_cv' => 'Chưa có CV',
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
                    ->label('Ngày ứng tuyển')
                    ->form([
                        DatePicker::make('applied_from')->label('Từ ngày')->native(false)->displayFormat('d/m/Y')->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                        DatePicker::make('applied_until')->label('Đến ngày')->native(false)->displayFormat('d/m/Y')->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['applied_from'] ?? null), fn (Builder $q) => $q->whereDate('applied_at', '>=', $data['applied_from']))
                            ->when(filled($data['applied_until'] ?? null), fn (Builder $q) => $q->whereDate('applied_at', '<=', $data['applied_until']));
                    }),
                TrashedFilter::make()->label('Bản ghi đã xóa'),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                Action::make('send_offer')
                    ->label(fn (Application $record): string => $record->latestOffer?->sent_at ? 'Gửi lại offer' : 'Gửi offer')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Gửi offer cho ứng viên')
                    ->modalDescription(fn (Application $record): string => 'Thư mời nhận việc được gửi tới '.($record->candidate?->email ?: 'email ứng viên'))
                    ->action(function (Application $record): void {
                        $offer = $record->offers()->latest('id')->first();
                        $candidate = $record->candidate;
                        $job = $record->job;

                        if (! $offer || ! $candidate?->email || ! $job) {
                            Notification::make()
                                ->warning()
                                ->title('Chưa thể gửi offer')
                                ->body('Vui lòng tạo offer và kiểm tra email ứng viên trước khi gửi.')
                                ->send();

                            return;
                        }

                        try {
                            if ($offer->offer_letter_template_id) {
                                app(OfferPdfService::class)->refreshForOffer($offer);
                                $offer->refresh();
                            }

                            Mail::to($candidate->email)->send(new CandidateOfferMail($candidate, $record, $job, $offer));

                            $offer->forceFill([
                                'sent_at' => now(),
                            ])->save();

                            Notification::make()
                                ->success()
                                ->title('Đã gửi offer')
                                ->body('Thư mời nhận việc đã được gửi tới ứng viên.')
                                ->send();
                        } catch (\Throwable $exception) {
                            Log::warning('Failed to send offer mail.', [
                                'application_id' => $record->id,
                                'offer_id' => $offer->id,
                                'recipient' => $candidate->email,
                                'error' => $exception->getMessage(),
                            ]);

                            Notification::make()
                                ->warning()
                                ->title('Gửi offer thất bại')
                                ->body('Offer đã được lưu nhưng chưa gửi được email. Vui lòng kiểm tra và gửi lại.')
                                ->send();
                        }
                    })
                    ->visible(fn (Application $record): bool => static::canSendOffer($record)),
                Action::make('download_offer_pdf')
                    ->label('Tải PDF offer')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (Application $record): bool => filled($record->latestOffer?->pdf_path))
                    ->action(function (Application $record) {
                        $offer = $record->offers()->latest('id')->first();
                        $disk = Storage::disk('local');

                        if (! $offer || ! $offer->pdf_path || ! $disk->exists($offer->pdf_path)) {
                            Notification::make()
                                ->warning()
                                ->title('Chưa có file PDF')
                                ->body('Chọn mẫu thư offer và lưu lại, hoặc kiểm tra quyền ghi storage.')
                                ->send();

                            return null;
                        }

                        return response()->download(
                            $disk->path($offer->pdf_path),
                            'thu-moi-nhan-viec-'.$offer->id.'.pdf',
                            ['Content-Type' => 'application/pdf'],
                        );
                    }),
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->label('Xem')
                    ->modalContent(fn ($record) => view('filament.applications.application-view', ['record' => $record])),
                EditAction::make()
                    ->label('Sửa')
                    ->url(fn ($record): string => ApplicationResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make()->label('Xóa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Xóa đã chọn'),
                    ForceDeleteBulkAction::make()->label('Xóa vĩnh viễn'),
                    RestoreBulkAction::make()->label('Khôi phục'),
                ]),
            ]);
    }

    protected static function getInterviewFormData(Application $record): array
    {
        $interview = $record->interviews()->latest('id')->first();

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
        $status = $record->status instanceof StatusApplicationEnum ? $record->status->value : $record->status;

        return in_array($status, [StatusApplicationEnum::SCREENING->value, StatusApplicationEnum::INTERVIEW->value], true);
    }

    protected static function hasInterviewStatus(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status->value : $record->status;

        return $status === StatusApplicationEnum::INTERVIEW->value;
    }

    protected static function getInterviewActionLabel(Application $record): ?string
    {
        if (! static::canManageInterview($record)) {
            return null;
        }

        return static::hasInterviewStatus($record) ? 'Cập nhật phỏng vấn' : 'Tạo lịch phỏng vấn';
    }

    protected static function getPipelineActionLabel(Application $record): ?string
    {
        if (static::canManageInterview($record)) {
            return static::getInterviewActionLabel($record);
        }

        if (static::canManageOffer($record)) {
            return static::getOfferActionLabel($record);
        }

        return null;
    }

    protected static function getPipelineActionColor(Application $record): string
    {
        if (static::canManageInterview($record)) {
            return static::hasInterviewStatus($record) ? 'info' : 'warning';
        }

        if (static::canManageOffer($record)) {
            return 'primary';
        }

        return 'gray';
    }

    protected static function canManageOffer(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status->value : $record->status;

        return $status === StatusApplicationEnum::OFFER->value;
    }

    protected static function getOfferActionLabel(Application $record): ?string
    {
        if (! static::canManageOffer($record)) {
            return null;
        }

        return $record->offers()->exists() ? 'Sửa offer' : 'Tạo offer';
    }

    protected static function canSendOffer(Application $record): bool
    {
        return static::canManageOffer($record)
            && filled($record->candidate?->email)
            && $record->offers()->exists();
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
            ->mapWithKeys(fn (Workplace $workplace): array => [$workplace->id => static::formatWorkplaceLabel($workplace)])
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
            ->with(['branch', 'roles'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => static::formatInterviewerOptionLabel($user),
            ])
            ->all();
    }

    protected static function formatInterviewerOptionLabel(User $user): string
    {
        $roleKey = $user->role;

        if (! filled($roleKey)) {
            $allowed = ['director', 'pm', 'hr'];
            $roleKey = $user->roles->first(fn ($r) => in_array($r->name, $allowed, true))?->name;
        }

        $nameWithRole = $user->name;

        if (filled($roleKey)) {
            $nameWithRole .= ' ('.static::formatUserRole($roleKey).')';
        }

        $branchName = $user->branch?->name;

        return trim(implode(' - ', array_filter([$nameWithRole, $branchName])));
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
            $workplace->room ? 'Phòng '.$workplace->room : null,
            $workplace->floor ? 'Tầng '.$workplace->floor : null,
        ]);

        return implode(' - ', $parts);
    }

    protected static function formatUserRole(?string $role): string
    {
        return match ($role) {
            'director' => 'Giám đốc',
            'pm' => 'PM',
            'hr' => 'HR',
            default => $role ? strtoupper($role) : '',
        };
    }

    protected static function makePipelineAction(): Action
    {
        return Action::make('pipeline')
            ->label('Xử lý')
            ->icon(fn (Application $record): string => static::canManageInterview($record)
                ? 'heroicon-o-calendar-days'
                : 'heroicon-o-hand-raised')
            ->color(fn (Application $record): string => static::getPipelineActionColor($record))
            ->modalWidth(fn (Application $record): string => static::canManageInterview($record) ? '3xl' : '4xl')
            ->modalHeading(function (Application $record): string {
                if (static::canManageInterview($record)) {
                    return $record->interviews()->exists() ? 'Điều chỉnh lịch phỏng vấn' : 'Tạo lịch phỏng vấn';
                }

                if (static::canManageOffer($record)) {
                    return $record->offers()->exists() ? 'Chỉnh sửa offer' : 'Tạo offer';
                }

                return 'Xử lý hồ sơ';
            })
            ->modalDescription(fn (Application $record): string => 'Hồ sơ #'.$record->id.' - '.($record->candidate?->name ?? '?ng viên'))
            ->fillForm(function (Application $record): array {
                if (static::canManageInterview($record)) {
                    return static::getInterviewFormData($record);
                }

                $offer = $record->offers()->latest('id')->first();

                return [
                    'offer_letter_template_id' => $offer?->offer_letter_template_id,
                    'salary_offered' => $offer?->salary_offered,
                    'start_date' => $offer?->start_date,
                    'probation_months' => $offer?->probation_months ?? 2,
                    'content' => $offer?->content,
                ];
            })
            ->form(function (Application $record): array {
                if (static::canManageInterview($record)) {
                    return [
                        DateTimePicker::make('scheduled_at')
                            ->label('Thời gian phỏng vấn')
                            ->native(false)
                            ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                            ->seconds(false)
                            ->required(),
                        Select::make('type')
                            ->label('Hình thức phỏng vấn')
                            ->options(['online' => 'Online', 'offline' => 'Offline'])
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
                        Textarea::make('notes')->label('Ghi chú')->rows(4)->columnSpanFull(),
                    ];
                }

                return [
                    Select::make('offer_letter_template_id')
                        ->label('Mẫu thư offer (PDF)')
                        ->placeholder('Không dùng mẫu — soạn toàn bộ trong ô nội dung bên dưới')
                        ->options(fn (): array => OfferLetterTemplate::query()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->columnSpanFull(),
                    TextInput::make('salary_offered')->label('Mức lương đề nghị')->numeric()->minValue(0)->required()->suffix('VND'),
                    DatePicker::make('start_date')
                        ->label('Ngày bắt đầu dự kiến')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                        ->required(),
                    TextInput::make('probation_months')->label('Thời gian thử việc')->numeric()->minValue(0)->default(2)->required()->suffix('tháng'),
                    Textarea::make('content')
                        ->label('Nội dung bổ sung (email + cuối PDF)')
                        ->rows(8)
                        ->required(fn (callable $get): bool => blank($get('offer_letter_template_id')))
                        ->columnSpanFull(),
                ];
            })
            ->action(function (Application $record, array $data): void {
                if (static::canManageInterview($record)) {
                    $existingInterview = $record->interviews()->latest('id')->first();
                    $roundNumber = (int) ($existingInterview?->round_number ?: 1);

                    $interview = $existingInterview ?? new Interview([
                        'application_id' => $record->id,
                        'round_number' => $roundNumber,
                        'round_name' => 'Phỏng vấn vòng '.$roundNumber,
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

                    $record->forceFill(['status' => StatusApplicationEnum::INTERVIEW])->save();

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
                        $interview->forceFill(['invite_sent_at' => now()])->save();
                    }

                    $notification = Notification::make()->title($existingInterview ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn');

                    if ($failedCount > 0) {
                        $notification->warning()->body("Lịch phỏng vấn đã được lưu, gửi email thành công {$sentCount} và thất bại {$failedCount}.");
                    } elseif ($sentCount === 0) {
                        $notification->warning()->body('Lịch phỏng vấn đã được lưu nhưng không tìm thấy email để gửi thông báo.');
                    } else {
                        $notification->success()->body("Đã lưu lịch phỏng vấn và gửi {$sentCount} email thông báo.");
                    }

                    $notification->send();

                    return;
                }

                $existingOffer = $record->offers()->latest('id')->first();
                $offer = $existingOffer ?? new Offer([
                    'application_id' => $record->id,
                    'status' => 'pending',
                ]);

                $offer->fill([
                    'application_id' => $record->id,
                    'offer_letter_template_id' => $data['offer_letter_template_id'] ?? null,
                    'salary_offered' => $data['salary_offered'],
                    'start_date' => $data['start_date'],
                    'probation_months' => $data['probation_months'],
                    'content' => $data['content'] ?? '',
                ]);
                $offer->save();

                app(OfferPdfService::class)->refreshForOffer($offer);
                $offer->refresh();

                $record->forceFill(['status' => StatusApplicationEnum::OFFER])->save();

                $pdfHint = filled($offer->pdf_path)
                    ? ' PDF đã tạo — có thể tải từ cột thao tác hoặc file đính kèm khi gửi email.'
                    : '';

                Notification::make()
                    ->success()
                    ->title($existingOffer ? 'Ðã lưu offer' : 'Ðã tạo offer')
                    ->body('Offer đã được lưu.'.$pdfHint.' Có thể gửi email offer cho ứng viên.')
                    ->send();
            })
            ->visible(fn (Application $record): bool => static::getPipelineActionLabel($record) !== null)
            ->disabled(fn (Application $record): bool => static::getPipelineActionLabel($record) === null);
    }
}

