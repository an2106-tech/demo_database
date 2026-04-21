<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Mail\CandidateOfferMail;
use App\Mail\InterviewScheduledMail;
use App\Mail\OfferApprovalRequestMail;
use App\Mail\OfferApprovedNotificationMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\OfferLetterTemplate;
use App\Models\RecruitmentJob;
use App\Models\Scorecard;
use App\Models\ScorecardTemplate;
use App\Models\User;
use App\Models\Workplace;
use App\Services\InterviewCalendarService;
use App\Services\OfferApprovalService;
use App\Services\OfferPdfService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
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
            ->poll('10s')
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
                TextColumn::make('offer_response')
                    ->label('Phản hồi offer')
                    ->state(fn (Application $record): ?string => static::getOfferResponseLabel($record))
                    ->badge(fn (?string $state): bool => filled($state))
                    ->color(fn (Application $record): string => static::getOfferResponseColor($record))
                    ->placeholder('-'),
                TextColumn::make('interview_follow_up')
                    ->label('Nhắc việc')
                    ->state(fn (Application $record): ?string => static::getInterviewFollowUpLabel($record))
                    ->badge(fn (?string $state): bool => filled($state))
                    ->color('danger')
                    ->placeholder('-'),
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
                static::makePipelineAction(),
                ActionGroup::make([
                    Action::make('evaluate_interview')
                        ->label('Đánh giá PV')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('info')
                        ->modalWidth('4xl')
                        ->modalHeading('Đánh giá phỏng vấn')
                        ->modalDescription(fn (Application $record): string => 'Hồ sơ #'.$record->id.' - '.($record->candidate?->name ?? 'Ứng viên'))
                        ->fillForm(function (Application $record): array {
                            $interview = $record->interviews()->latest('id')->first();
                            $scorecard = $record->scorecards()->latest('id')->first();

                            $defaultTemplate = ScorecardTemplate::query()
                                ->where('is_default', true)
                                ->latest('id')
                                ->first();

                            $criteria = $scorecard?->criteria;
                            if (! is_array($criteria) || $criteria === []) {
                                $criteria = $defaultTemplate?->criteria;
                            }

                            if (! is_array($criteria) || $criteria === []) {
                                $criteria = [
                                    ['name' => 'Kỹ thuật', 'score' => null],
                                    ['name' => 'Tư duy / giải quyết vấn đề', 'score' => null],
                                    ['name' => 'Giao tiếp', 'score' => null],
                                    ['name' => 'Phù hợp văn hoá', 'score' => null],
                                ];
                            }

                            return [
                                'interview_id' => $interview?->id,
                                'template_id' => $scorecard?->template_id ?? $defaultTemplate?->id,
                                'criteria' => $criteria,
                                'average_score' => $scorecard?->average_score,
                                'conclusion' => $scorecard?->conclusion ?? ($interview?->result !== 'pending' ? $interview?->result : null),
                                'notes' => $scorecard?->notes,
                                'rejected_reason' => $record->rejected_reason,
                            ];
                        })
                        ->form([
                            Select::make('template_id')
                                ->label('Mẫu đánh giá')
                                ->options(fn (): array => ScorecardTemplate::query()
                                    ->orderByDesc('is_default')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->helperText('Chọn mẫu để gợi ý tiêu chí chấm điểm (nếu có).')
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    if (blank($state)) {
                                        return;
                                    }

                                    $criteria = ScorecardTemplate::query()->whereKey($state)->value('criteria');
                                    if (is_array($criteria) && $criteria !== []) {
                                        $set('criteria', $criteria);
                                    }
                                }),
                            Hidden::make('interview_id'),
                            Repeater::make('criteria')
                                ->label('Tiêu chí chấm điểm')
                                ->schema([
                                    TextInput::make('name')->label('Tiêu chí')->required()->maxLength(120),
                                    TextInput::make('score')->label('Điểm')->numeric()->minValue(0)->maxValue(10)->required(),
                                ])
                                ->minItems(1)
                                ->defaultItems(4)
                                ->columns(2)
                                ->columnSpanFull(),
                            Select::make('conclusion')
                                ->label('Kết luận')
                                ->options([
                                    'pass' => 'Đạt (tiếp tục Offer)',
                                    'hold' => 'Giữ lại / cân nhắc',
                                    'fail' => 'Không đạt (từ chối)',
                                ])
                                ->required(),
                            Textarea::make('notes')
                                ->label('Nhận xét')
                                ->rows(5)
                                ->columnSpanFull(),
                            Textarea::make('rejected_reason')
                                ->label('Lý do từ chối (nếu Fail)')
                                ->rows(3)
                                ->visible(fn (callable $get): bool => $get('conclusion') === 'fail')
                                ->required(fn (callable $get): bool => $get('conclusion') === 'fail')
                                ->columnSpanFull(),
                        ])
                        ->action(function (Application $record, array $data): void {
                            $interview = $record->interviews()->latest('id')->first();
                            if (! $interview) {
                                Notification::make()
                                    ->warning()
                                    ->title('Chưa có lịch phỏng vấn')
                                    ->body('Vui lòng tạo lịch phỏng vấn trước khi chấm điểm.')
                                    ->send();

                                return;
                            }

                            $criteria = $data['criteria'] ?? [];
                            $scores = collect($criteria)
                                ->map(fn ($row) => is_array($row) ? ($row['score'] ?? null) : null)
                                ->filter(fn ($score) => $score !== null && $score !== '')
                                ->map(fn ($score) => (float) $score);

                            $average = $scores->count() > 0 ? round($scores->avg(), 2) : null;

                            $scorecard = new Scorecard();
                            $scorecard->fill([
                                'application_id' => $record->id,
                                'interview_id' => $interview->id,
                                'template_id' => $data['template_id'] ?? null,
                                'evaluator_id' => (int) Auth::id(),
                                'criteria' => $criteria,
                                'average_score' => $average,
                                'notes' => $data['notes'] ?? null,
                                'conclusion' => $data['conclusion'],
                            ]);
                            $scorecard->save();

                            $conclusion = $data['conclusion'];
                            $interviewResult = $conclusion === 'hold' ? 'pending' : $conclusion;
                            $interview->forceFill(['result' => $interviewResult])->save();

                            if ($conclusion === 'pass') {
                                $record->forceFill([
                                    'status' => StatusApplicationEnum::OFFER,
                                    'rejected_reason' => null,
                                ])->save();
                            } elseif ($conclusion === 'fail') {
                                $record->forceFill([
                                    'status' => StatusApplicationEnum::REJECTED,
                                    'rejected_reason' => $data['rejected_reason'] ?? $record->rejected_reason,
                                ])->save();
                            } else {
                                $record->forceFill(['status' => StatusApplicationEnum::INTERVIEW])->save();
                            }

                            Notification::make()
                                ->success()
                                ->title('Đã lưu đánh giá phỏng vấn')
                                ->body($conclusion === 'pass'
                                    ? 'Ứng viên đạt — hồ sơ đã chuyển sang Offer.'
                                    : ($conclusion === 'fail'
                                        ? 'Ứng viên không đạt — hồ sơ đã chuyển sang Từ chối.'
                                        : 'Đã lưu đánh giá — hồ sơ giữ ở Phỏng vấn.'))
                                ->send();
                        })
                        ->visible(function (Application $record): bool {
                            $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                            return $status === StatusApplicationEnum::INTERVIEW;
                        }),
                    Action::make('reject_application')
                        ->label('Từ chối')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->modalHeading('Từ chối ứng viên')
                        ->form([
                            Textarea::make('rejected_reason')
                                ->label('Lý do từ chối')
                                ->rows(4)
                                ->required(),
                        ])
                        ->action(function (Application $record, array $data): void {
                            $record->forceFill([
                                'status' => StatusApplicationEnum::REJECTED,
                                'rejected_reason' => $data['rejected_reason'] ?? null,
                            ])->save();

                            Notification::make()
                                ->success()
                                ->title('Đã từ chối ứng viên')
                                ->send();
                        })
                        ->visible(function (Application $record): bool {
                            $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                            return in_array($status, [
                                StatusApplicationEnum::NEW,
                                StatusApplicationEnum::SCREENING,
                                StatusApplicationEnum::INTERVIEW,
                                StatusApplicationEnum::OFFER,
                            ], true);
                        }),
                    Action::make('send_offer')
                        ->label(fn (Application $record): string => match($record->latestOffer?->status) {
                            'awaiting_approval' => 'Gửi duyệt',
                            'rejected' => 'Gửi duyệt lại',
                            default => $record->latestOffer?->sent_at ? 'Gửi lại offer' : 'Gửi offer',
                        })
                        ->icon('heroicon-o-envelope')
                        ->color(fn (Application $record): string => match($record->latestOffer?->status) {
                            'awaiting_approval', 'rejected' => 'warning',
                            default => 'primary',
                        })
                        ->requiresConfirmation()
                        ->modalHeading(function (Application $record): string {
                            return match($record->latestOffer?->status) {
                                'awaiting_approval' => 'Gửi offer cho giám đốc duyệt',
                                'rejected' => 'Gửi lại offer cho giám đốc duyệt',
                                default => 'Gửi offer cho ứng viên',
                            };
                        })
                        ->modalDescription(function (Application $record): string {
                            return match($record->latestOffer?->status) {
                                'awaiting_approval' => 'Offer sẽ được gửi để giám đốc chi nhánh duyệt trước khi gửi tới ứng viên',
                                'rejected' => 'Offer sau khi chỉnh sửa sẽ được gửi cho giám đốc chi nhánh duyệt lại',
                                default => 'Thư mời nhận việc được gửi tới '.($record->candidate?->email ?: 'email ứng viên'),
                            };
                        })
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

                            // Nếu offer chờ duyệt hoặc bị từ chối, gửi email yêu cầu duyệt cho giám đốc
                            if (in_array($offer->status, ['awaiting_approval', 'rejected'], true)) {
                                static::sendOfferForApproval($record, $offer);
                            } else {
                                // Nếu đã được duyệt (pending), gửi trực tiếp cho ứng viên
                                static::sendOfferToCandidate($record, $offer, $candidate, $job);
                            }
                        })
                        ->visible(fn (Application $record): bool => static::canSendOffer($record)),
                    Action::make('reopen_offer_response')
                        ->label('Mở lại phản hồi')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Mở lại phản hồi offer')
                        ->modalDescription('Đưa offer về trạng thái chờ phản hồi để có thể gửi lại cho ứng viên.')
                        ->action(function (Application $record): void {
                            $offer = $record->offers()->latest('id')->first();

                            if (! $offer) {
                                Notification::make()
                                    ->warning()
                                    ->title('Chưa có offer để mở lại')
                                    ->send();

                                return;
                            }

                            $offer->forceFill([
                                'status' => 'pending',
                                'response_at' => null,
                                'accepted_at' => null,
                                'declined_reason' => null,
                                'sent_at' => null,
                                'expires_at' => now()->addDays(3),
                            ])->save();

                            $record->forceFill([
                                'status' => StatusApplicationEnum::OFFER,
                                'rejected_reason' => null,
                            ])->save();

                            Notification::make()
                                ->success()
                                ->title('Đã mở lại phản hồi offer')
                                ->body('Bạn có thể chỉnh sửa và gửi lại offer mới cho ứng viên.')
                                ->send();
                        })
                        ->visible(fn (Application $record): bool => in_array($record->latestOffer?->status, ['accepted', 'declined', 'expired'], true)),
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
                    ->label('Khác')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->color('gray')
                    ->button(),
            ])            ->toolbarActions([
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
        $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

        if ($status === StatusApplicationEnum::NEW) {
            return 'Sàng lọc';
        }

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
        $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

        if ($status === StatusApplicationEnum::NEW) {
            return 'warning';
        }

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
        $offer = $record->offers()->latest('id')->first();

        if (! static::canManageOffer($record) || ! filled($record->candidate?->email) || ! $offer) {
            return false;
        }

        // HR có thể gửi offer trong các trạng thái: awaiting_approval, rejected, pending
        if (Auth::user()?->hasRole('director') === true) {
            // Director không gửi từ đây (xử lý trong OfferResource)
            return false;
        }

        // HR có thể gửi khi offer chờ duyệt hoặc bị từ chối hoặc chờ phản hồi từ ứng viên
        return in_array($offer->status, ['awaiting_approval', 'rejected'], true);
    }

    protected static function getBranchDirectorEmails(Application $record): array
    {
        $branchId = $record->job?->branch_id;

        if (! $branchId) {
            return [];
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'director'))
            ->pluck('email')
            ->filter()
            ->all();
    }

    protected static function getOfferNotificationRecipients(Application $record): array
    {
        $branchId = $record->job?->branch_id;

        if (! $branchId) {
            return [];
        }

        return User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['director', 'hr', 'pm']))
            ->get()
            ->filter(fn (User $user) => filled($user->email))
            ->mapWithKeys(fn (User $user) => [$user->email => $user->role])
            ->all();
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

    public static function isPendingInterviewEvaluation(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);
        $interview = $record->latestInterview;

        if ($status !== StatusApplicationEnum::INTERVIEW || ! $interview || ! $interview->scheduled_at) {
            return false;
        }

        return $interview->scheduled_at->lte(now()) && $interview->result === 'pending';
    }

    protected static function getInterviewFollowUpLabel(Application $record): ?string
    {
        return static::isPendingInterviewEvaluation($record) ? 'Chờ chấm phỏng vấn' : null;
    }

    protected static function getOfferResponseLabel(Application $record): ?string
    {
        return match ($record->latestOffer?->status) {
            'accepted' => 'Đã đồng ý',
            'declined' => 'Đã từ chối',
            'expired' => 'Đã hết hạn',
            'awaiting_approval' => 'Chờ duyệt offer',
            'pending' => $record->latestOffer?->sent_at ? 'Chờ phản hồi' : 'Chưa gửi',
            default => null,
        };
    }

    protected static function getOfferResponseColor(Application $record): string
    {
        return match ($record->latestOffer?->status) {
            'accepted' => 'success',
            'declined' => 'danger',
            'expired' => 'gray',
            'awaiting_approval' => 'warning',
            'pending' => 'warning',
            default => 'gray',
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
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return 'Chuyển sang sàng lọc';
                }
                if (static::canManageInterview($record)) {
                    return $record->interviews()->exists() ? 'Điều chỉnh lịch phỏng vấn' : 'Tạo lịch phỏng vấn';
                }

                if (static::canManageOffer($record)) {
                    return $record->offers()->exists() ? 'Chỉnh sửa offer' : 'Tạo offer';
                }

                return 'Xử lý hồ sơ';
            })
            ->modalDescription(fn (Application $record): string => 'Hồ sơ #'.$record->id.' - '.($record->candidate?->name ?? 'Ứng viên'))
            ->fillForm(function (Application $record): array {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return [];
                }

                if (static::canManageInterview($record)) {
                    return static::getInterviewFormData($record);
                }

                $offer = $record->offers()->latest('id')->first();

                return [
                    'offer_letter_template_id' => $offer?->offer_letter_template_id,
                    'salary_offered' => $offer?->salary_offered,
                    'start_date' => $offer?->start_date,
                    'probation_months' => $offer?->probation_months ?? 2,
                    'expires_at' => $offer?->expires_at,
                    'content' => $offer?->content,
                ];
            })
            ->form(function (Application $record): array {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return [];
                }

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
                        ->placeholder('Không dùng mẫu - soạn toàn bộ trong ô nội dung bên dưới')
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
                    DateTimePicker::make('expires_at')
                        ->label('Hạn phản hồi offer')
                        ->native(false)
                        ->seconds(false)
                        ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                        ->default(now()->addDays(3))
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
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    $record->forceFill(['status' => StatusApplicationEnum::SCREENING])->save();

                    Notification::make()
                        ->success()
                        ->title('Đã chuyển sang sàng lọc')
                        ->send();

                    return;
                }

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
                    'status' => 'awaiting_approval', // Offer tạo mới chờ duyệt từ giám đốc
                ]);

                $offer->fill([
                    'application_id' => $record->id,
                    'offer_letter_template_id' => $data['offer_letter_template_id'] ?? null,
                    'salary_offered' => $data['salary_offered'],
                    'start_date' => $data['start_date'],
                    'probation_months' => $data['probation_months'],
                    'expires_at' => $data['expires_at'] ?? now()->addDays(3),
                    'content' => $data['content'] ?? '',
                ]);
                $offer->forceFill([
                    'status' => 'awaiting_approval',
                    'approval_requested_at' => null,
                    'approved_by_user_id' => null,
                    'approved_at' => null,
                    'approval_notes' => null,
                    'sent_at' => null,
                    'response_at' => null,
                    'accepted_at' => null,
                    'declined_reason' => null,
                ]);
                $offer->save();

                app(OfferPdfService::class)->refreshForOffer($offer);
                $offer->refresh();

                $record->forceFill(['status' => StatusApplicationEnum::OFFER])->save();

                $pdfHint = filled($offer->pdf_path)
                    ? ' PDF đã tạo - có thể tải từ cột thao tác hoặc file đính kèm khi gửi email.'
                    : '';

                Notification::make()
                    ->success()
                    ->title($existingOffer ? 'Đã lưu offer' : 'Đã tạo offer')
                    ->body('Offer đã được lưu.'.$pdfHint.' Có thể gửi email offer cho ứng viên.')
                    ->send();
            })
            ->visible(fn (Application $record): bool => static::getPipelineActionLabel($record) !== null)
            ->disabled(fn (Application $record): bool => static::getPipelineActionLabel($record) === null);
    }

    protected static function sendOfferForApproval(Application $record, Offer $offer): void
    {
        try {
            $directors = static::getBranchDirectorEmails($record);

            if (empty($directors)) {
                Notification::make()
                    ->warning()
                    ->title('Không có giám đốc chi nhánh')
                    ->body('Không tìm thấy giám đốc chi nhánh để gửi offer cho duyệt.')
                    ->send();

                return;
            }

            // Refresh PDF
            if ($offer->offer_letter_template_id) {
                app(OfferPdfService::class)->refreshForOffer($offer);
                $offer->refresh();
            }

            // Gửi email cho tất cả giám đốc chi nhánh
            foreach ($directors as $email) {
                $director = User::where('email', $email)->where('is_active', true)->first();
                if ($director) {
                    Mail::to($email)->send(new OfferApprovalRequestMail($offer, $record, $record->job, $director));
                }
            }

            // Update status
            $offer->forceFill([
                'status' => 'awaiting_approval',
                'approval_requested_at' => now(),
            ])->save();

            $actionText = $offer->approval_requested_at ? 'gửi lại' : 'gửi';

            Notification::make()
                ->success()
                ->title('Đã ' . $actionText . ' offer cho duyệt')
                ->body('Offer đã được gửi cho giám đốc chi nhánh duyệt.')
                ->send();
        } catch (\Throwable $exception) {
            Log::warning('Failed to send offer for approval.', [
                'application_id' => $record->id,
                'offer_id' => $offer->id,
                'error' => $exception->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Gửi offer thất bại')
                ->body('Có lỗi khi gửi offer. Vui lòng kiểm tra lại.')
                ->send();
        }
    }

    protected static function sendOfferToCandidate(Application $record, Offer $offer, Candidate $candidate, RecruitmentJob $job): void
    {
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
    }
}
