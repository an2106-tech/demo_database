<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Mail\OfferApprovalRequestMail;
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
use App\Rules\InterviewMeetingLink;
use App\Services\InterviewCalendarService;
use App\Services\InterviewScorecardTemplateService;
use App\Services\InterviewEvaluationService;
use App\Services\InterviewMeetingLinkValidator;
use App\Services\InterviewScheduleDeliveryService;
use App\Services\ApplicationAiAnalysisService;
use App\Services\ApplicationPipelineService;
use App\Services\ApplicationPreScreeningService;
use App\Services\ApplicationWorkflowGuard;
use App\Services\ApplicationWorkflowSummaryService;
use App\Services\OfferApprovalService;
use App\Services\OfferWorkflowService;
use App\Services\RecruitmentInternalNotificationService;
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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        $statusOptions = collect(StatusApplicationEnum::cases())
            ->mapWithKeys(fn (StatusApplicationEnum $status) => [$status->value => (string) $status->getLabel()])
            ->all();

        return $table
            ->defaultSort('applied_at', 'desc')
            ->searchPlaceholder('Tìm theo ứng viên, vị trí, email...')
            ->columns([
                TextColumn::make('id')
                    ->label('Mã hồ sơ')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('candidate.name')
                    ->label('Ứng viên')
                    ->formatStateUsing(fn (Application $record): string => $record->snapshotCandidateName())
                    ->description(fn (Application $record): ?string => $record->snapshotCandidateEmail())
                    ->searchable(query: fn (Builder $query, string $search): Builder => static::applyCandidateSearch($query, $search))
                    ->sortable(),
                TextColumn::make('job.title')
                    ->label('Vị trí')
                    ->description(fn (Application $record): ?string => static::jobContextDescription($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'job',
                        fn (Builder $jobQuery): Builder => $jobQuery->where('title', 'like', "%{$search}%")
                    ))
                    ->sortable(),
                TextColumn::make('job.branch.name')
                    ->label('Chi nhánh')
                    ->sortable()
                    ->visible(fn (): bool => Auth::user()?->branchScopeId() === null),
                TextColumn::make('cv_path')
                    ->label('CV')
                    ->formatStateUsing(fn (Application $record): string => $record->submittedCvName() ?: '-')
                    ->url(fn (Application $record): ?string => $record->submittedCvUrl())
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('apply_method')
                    ->label('Hình thức nộp')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'profile' => 'Hồ sơ',
                        'cv' => 'CV',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'profile' => 'info',
                        'cv' => 'primary',
                        default => 'gray',
                    }),
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
                    ->color(fn (?string $state): string => match ($state) {
                        'website' => 'primary',
                        'linkedin' => 'info',
                        'referral' => 'success',
                        'facebook' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Tiến độ')
                    ->state(fn (Application $record): string => static::workflowSummary($record)['stage_label'])
                    ->description(fn (Application $record): string => static::workflowSummary($record)['status_label'])
                    ->color(fn (Application $record): string => static::workflowSummary($record)['color'])
                    ->alignCenter()
                    ->badge()
                    ->sortable(),
                TextColumn::make('offer_response')
                    ->label('Phản hồi đề nghị')
                    ->state(fn (Application $record): ?string => static::getOfferResponseLabel($record))
                    ->description(fn (Application $record): ?string => static::getOfferResponseDescription($record))
                    ->badge(fn (?string $state): bool => filled($state))
                    ->color(fn (Application $record): string => static::getOfferResponseColor($record))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('interview_follow_up')
                    ->label('Cần chú ý')
                    ->state(fn (Application $record): ?string => static::getInterviewFollowUpLabel($record))
                    ->badge(fn (?string $state): bool => filled($state))
                    ->color('danger')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Ngày nộp')
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
                SelectFilter::make('work_queue')
                    ->label('Cần xử lý')
                    ->options([
                        'cv_reviewing' => 'Chờ sàng lọc CV',
                        'interview_schedule_needed' => 'Cần tạo lịch phỏng vấn',
                        'interview_invite_unsent' => 'Lịch phỏng vấn chưa gửi',
                        'interview_overdue' => 'Phỏng vấn quá hạn chưa chấm',
                        'offer_needed' => 'Cần tạo đề nghị tuyển dụng',
                        'offer_draft' => 'Đề nghị đang nháp/cần gửi lại',
                        'offer_awaiting_approval' => 'Đề nghị chờ giám đốc duyệt',
                        'offer_expiring' => 'Đề nghị sắp hết hạn phản hồi',
                    ])
                    ->native(false)
                    ->query(fn (Builder $query, array $data): Builder => static::applyWorkQueueFilter($query, $data['value'] ?? null)),
                SelectFilter::make('branch_id')
                    ->label('Chi nhánh')
                    ->options(fn () => Branch::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('job', fn (Builder $q) => $q->where('branch_id', $data['value']));
                    })
                    ->visible(fn () => (bool) Auth::user()?->hasRole('super_admin')),
                SelectFilter::make('pipeline_stage')
                    ->label('Giai đoạn')
                    ->options(StatusApplicationEnum::pipelineStageOptions())
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $stageKey = $data['value'] ?? null;

                        if (blank($stageKey)) {
                            return $query;
                        }

                        $statusValues = StatusApplicationEnum::statusValuesForPipelineStage((string) $stageKey);

                        return $statusValues === [] ? $query : $query->whereIn('status', $statusValues);
                    }),
                SelectFilter::make('status')
                    ->label('Trạng thái chi tiết')
                    ->options($statusOptions)
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('status', $data['value']) : $query;
                    }),
                SelectFilter::make('job_id')
                    ->label('Vị trí')
                    ->options(fn () => static::getJobFilterOptions())
                    ->native(false)
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('job_id', $data['value']) : $query;
                    }),
                Filter::make('applied_at_range')
                    ->label('Ngày nộp')
                    ->form([
                        DatePicker::make('applied_from')->label('Từ ngày')->native(false)->displayFormat('d/m/Y')->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                        DatePicker::make('applied_until')->label('Đến ngày')->native(false)->displayFormat('d/m/Y')->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['applied_from'] ?? null), fn (Builder $q) => $q->whereDate('applied_at', '>=', $data['applied_from']))
                            ->when(filled($data['applied_until'] ?? null), fn (Builder $q) => $q->whereDate('applied_at', '<=', $data['applied_until']));
                    }),
                SelectFilter::make('cv_state')
                    ->label('Tình trạng CV')
                    ->options([
                        'has_cv' => 'Đã có CV',
                        'missing_cv' => 'Chưa có CV',
                    ])
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'has_cv' => $query->where(function (Builder $q): void {
                                $q->whereNotNull('cv_path')->where('cv_path', '!=', '')
                                    ->orWhereNotNull('cv_attachment_id');
                            }),
                            'missing_cv' => $query->where(function (Builder $q): void {
                                $q->where(function (Builder $inner): void {
                                    $inner->whereNull('cv_path')->orWhere('cv_path', '');
                                })->whereNull('cv_attachment_id');
                            }),
                            default => $query,
                        };
                    }),
                SelectFilter::make('candidate_id')
                    ->label('Ứng viên')
                    ->options(fn () => static::getCandidateFilterOptions())
                    ->native(false)
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('candidate_id', $data['value']) : $query;
                    }),
                SelectFilter::make('source')
                    ->label('Nguồn hồ sơ')
                    ->options([
                        'website' => 'Website',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'referral' => 'Giới thiệu',
                        'other' => 'Khác',
                    ])
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('source', $data['value']) : $query;
                    }),
                TrashedFilter::make()->label('Bản ghi đã xóa'),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                static::makePipelineAction(),
                static::makeSendInterviewScheduleAction('primary_send_interview_schedule'),
                static::makeSendOfferAction('primary_send_offer'),
                    Action::make('evaluate_interview')
                        ->label('Chấm phỏng vấn')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('info')
                        ->modalWidth('6xl')
                        ->modalHeading('Ghi nhận đánh giá phỏng vấn')
                        ->modalDescription(fn (Application $record): string => 'Hồ sơ #'.$record->id.' - '.$record->snapshotCandidateName())
                        ->modalSubmitActionLabel('Lưu đánh giá')
                        ->fillForm(function (Application $record): array {
                            $interview = $record->interviews()->latest('id')->first();
                            $scorecard = $interview
                                ? static::getCurrentEvaluatorScorecard($record, $interview)
                                : null;

                            $snapshot = is_array($interview?->scorecard_template_snapshot)
                                ? $interview->scorecard_template_snapshot
                                : [];
                            $criteria = $scorecard?->criteria ?: ($snapshot['criteria'] ?? []);

                            return [
                                'interview_id' => $interview?->id,
                                'template_id' => $scorecard?->template_id ?? $interview?->scorecard_template_id,
                                'criteria' => is_array($criteria) ? $criteria : [],
                                'average_score' => $scorecard?->average_score,
                                'recommended_conclusion' => $scorecard?->recommended_conclusion,
                                'conclusion' => $scorecard?->conclusion ?? ($interview?->result !== 'pending' ? $interview?->result : null),
                                'notes' => $scorecard?->notes,
                                'override_reason' => $scorecard?->override_reason,
                                'rejected_reason' => $record->rejected_reason,
                                'confirm_early_completion' => false,
                            ];
                        })
                        ->form(fn (): array => static::getInterviewEvaluationFormSchema())
                        ->action(function (Application $record, array $data): void {
                            $canFinalize = static::workflowGuard()->canFinalizeInterviewEvaluation(
                                Auth::user(),
                                $record,
                                (bool) ($data['confirm_early_completion'] ?? false),
                            );
                            $result = $canFinalize
                                ? app(InterviewEvaluationService::class)->complete($record, $data, Auth::user())
                                : app(InterviewEvaluationService::class)->saveDraft($record, $data, Auth::user());

                            $completionState = $result['completion_state'] ?? null;
                            $progress = $result['progress'] ?? [];

                            Notification::make()
                                ->success()
                                ->title($completionState === 'submitted'
                                    ? 'Đã gửi phiếu đánh giá'
                                    : ($completionState === 'held'
                                        ? 'Đã ghi nhận kết quả cần xem xét thêm'
                                        : ($canFinalize ? 'Đã hoàn tất đánh giá phỏng vấn' : 'Đã lưu đánh giá tạm')))
                                ->body($completionState === 'submitted'
                                    ? 'Đã nhận '.($progress['submitted'] ?? 0).'/'.($progress['required'] ?? 0).' phiếu. Hồ sơ chưa chuyển giai đoạn.'
                                    : ($completionState === 'held'
                                        ? 'Hồ sơ vẫn ở giai đoạn Phỏng vấn để nội bộ tiếp tục xem xét.'
                                        : ($canFinalize
                                            ? ($result['conclusion'] === 'pass'
                                        ? 'Ứng viên đạt - hồ sơ đã chuyển sang bước đề nghị tuyển dụng.'
                                        : ($result['conclusion'] === 'fail'
                                            ? 'Ứng viên không đạt — hồ sơ đã chuyển sang Từ chối.'
                                            : 'Đánh giá đã hoàn tất — hồ sơ giữ ở Phỏng vấn để xem xét thêm.'))
                                            : 'Điểm và nhận xét đang nhập đã được lưu. Hồ sơ chưa chuyển giai đoạn.')))
                                ->send();
                        })
                        ->visible(fn (Application $record): bool => static::canEvaluateInterview($record)),
                ActionGroup::make([
                    static::makeSecondaryPipelineAction(),
                    static::makeAnalyzeScreeningAiAction(),
                    Action::make('reject_application')
                        ->label('Từ chối hồ sơ')
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
                                'rejected_reason' => $data['rejected_reason'] ?? null,
                            ])->save();

                            if (! static::transitionApplication($record, StatusApplicationEnum::REJECTED, 'Từ chối ứng viên.')) {
                                return;
                            }

                            Notification::make()
                                ->success()
                                ->title('Đã từ chối ứng viên')
                                ->send();
                        })
                        ->visible(fn (Application $record): bool => static::canRejectApplication($record)),
                    Action::make('reopen_offer_response')
                        ->label('Mở lại phản hồi đề nghị')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Mở lại phản hồi đề nghị')
                        ->modalDescription('Đưa đề nghị về trạng thái chờ phản hồi để có thể gửi lại cho ứng viên.')
                        ->action(function (Application $record): void {
                            $offer = $record->offers()->latest('id')->first();

                            if (! $offer) {
                                Notification::make()
                                    ->warning()
                                    ->title('Chưa có thư mời để mở lại')
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
                                ->title('Đã mở lại phản hồi thư mời')
                                ->body('Bạn có thể chỉnh sửa đề nghị tuyển dụng và gửi lại cho ứng viên.')
                                ->send();
                        })
                        ->visible(fn (Application $record): bool => static::canReopenOfferResponse($record)),
                    Action::make('download_offer_pdf')
                        ->label('Tải PDF đề nghị')
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
                                    ->body('Chọn mẫu thư mời và lưu lại, hoặc kiểm tra quyền ghi storage.')
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
                        ->label('Xem chi tiết')
                        ->url(fn ($record): string => ApplicationResource::getUrl('view', ['record' => $record])),
                    EditAction::make()
                        ->label('Chỉnh sửa')
                        ->url(fn ($record): string => ApplicationResource::getUrl('edit', ['record' => $record]))
                        ->visible(fn (Application $record): bool => ApplicationResource::canEdit($record)),
                    DeleteAction::make()
                        ->label('Xóa')
                        ->visible(fn (Application $record): bool => ApplicationResource::canDelete($record)),
                ])
                    ->label('Thao tác khác')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->color('gray')
                    ->button(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('Thao tác')
            ->recordActionsAlignment('center')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa đã chọn')
                        ->visible(fn (): bool => ApplicationResource::canDeleteAny()),
                    ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn')
                        ->visible(fn (): bool => ApplicationResource::canForceDeleteAny()),
                    RestoreBulkAction::make()
                        ->label('Khôi phục')
                        ->visible(fn (): bool => ApplicationResource::canRestoreAny()),
                ]),
            ]);
    }

    protected static function jobContextDescription(Application $record): ?string
    {
        $branchName = $record->job?->branch?->name;
        $departmentName = $record->job?->department?->name;

        if (Auth::user()?->branchScopeId()) {
            return $departmentName ? 'Phòng ban: '.$departmentName : null;
        }

        return collect([
            $branchName ? 'Chi nhánh: '.$branchName : null,
            $departmentName ? 'Phòng ban: '.$departmentName : null,
        ])->filter()->implode(' · ') ?: null;
    }

    protected static function applyCandidateSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $candidateQuery) use ($search): void {
            $candidateQuery
                ->whereHas('candidate', fn (Builder $query): Builder => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"))
                ->orWhere('profile_snapshot', 'like', "%{$search}%");
        });
    }

    protected static function applyWorkQueueFilter(Builder $query, mixed $value): Builder
    {
        $now = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'));

        return match ($value) {
            'cv_reviewing' => $query->where('status', StatusApplicationEnum::CV_REVIEWING->value),
            'interview_schedule_needed' => $query
                ->where('status', StatusApplicationEnum::SCREENING->value)
                ->whereDoesntHave('latestInterview'),
            'interview_invite_unsent' => $query
                ->whereIn('status', [
                    StatusApplicationEnum::SCREENING->value,
                    StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                    StatusApplicationEnum::INTERVIEWING->value,
                ])
                ->whereHas('latestInterview', fn (Builder $interviewQuery): Builder => $interviewQuery->whereNull('invite_sent_at')),
            'interview_overdue' => $query
                ->whereIn('status', [
                    StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                    StatusApplicationEnum::INTERVIEWING->value,
                ])
                ->whereHas('latestInterview', fn (Builder $interviewQuery): Builder => $interviewQuery
                    ->where('result', 'pending')
                    ->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', $now)),
            'offer_needed' => $query
                ->where('status', StatusApplicationEnum::OFFERED->value)
                ->whereDoesntHave('latestOffer'),
            'offer_draft' => $query
                ->where('status', StatusApplicationEnum::OFFERED->value)
                ->whereHas('latestOffer', fn (Builder $offerQuery): Builder => $offerQuery->whereIn('status', ['draft', 'rejected'])),
            'offer_awaiting_approval' => $query
                ->where('status', StatusApplicationEnum::OFFERED->value)
                ->whereHas('latestOffer', fn (Builder $offerQuery): Builder => $offerQuery->where('status', 'awaiting_approval')),
            'offer_expiring' => $query
                ->where('status', StatusApplicationEnum::OFFERED->value)
                ->whereHas('latestOffer', fn (Builder $offerQuery): Builder => $offerQuery
                    ->where('status', 'pending')
                    ->whereNotNull('expires_at')
                    ->whereBetween('expires_at', [$now, $now->copy()->addDays(2)])),
            default => $query,
        };
    }

    protected static function getJobFilterOptions(): array
    {
        $query = RecruitmentJob::query()
            ->with('branch')
            ->orderBy('title')
            ->limit(500);

        if ($branchId = Auth::user()?->branchScopeId()) {
            $query->where('branch_id', $branchId);
        }

        return $query
            ->get()
            ->mapWithKeys(fn (RecruitmentJob $job): array => [
                $job->id => Auth::user()?->branchScopeId()
                    ? $job->title
                    : $job->title.($job->branch?->name ? ' - '.$job->branch->name : ''),
            ])
            ->all();
    }

    protected static function getCandidateFilterOptions(): array
    {
        $query = Candidate::query()
            ->orderBy('name')
            ->limit(500);

        if ($branchId = Auth::user()?->branchScopeId()) {
            $query->whereHas(
                'applications.job',
                fn (Builder $jobQuery) => $jobQuery->where('branch_id', $branchId)
            );
        }

        return $query
            ->get()
            ->mapWithKeys(fn (Candidate $candidate): array => [
                $candidate->id => "#{$candidate->id} - {$candidate->name}".($candidate->email ? " ({$candidate->email})" : ''),
            ])
            ->all();
    }

    protected static function getInterviewFormData(Application $record): array
    {
        $interview = $record->interviews()->latest('id')->first();

        return [
            'scheduled_at' => $interview?->scheduled_at?->format('Y-m-d\TH:i'),
            'round_name' => $interview?->round_name ?? 'Phỏng vấn vòng 1',
            'duration_minutes' => $interview?->duration_minutes ?? 60,
            'type' => $interview?->type ?? 'online',
            'meeting_link' => $interview?->meeting_link,
            'workplace_id' => $interview?->workplace_id,
            'interviewer_id' => $interview?->interviewer_id,
            'scorecard_template_id' => $interview?->scorecard_template_id,
            'notes' => $interview?->notes,
        ];
    }

    protected static function resolveInterviewScheduledAt(mixed $value): \Carbon\CarbonInterface
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->copy()->setTimezone($timezone);
        }

        return \Carbon\Carbon::parse((string) $value, $timezone);
    }

    protected static function validateInterviewSchedule(array $data, ?Interview $existingInterview = null): \Carbon\CarbonInterface
    {
        if (blank($data['scheduled_at'] ?? null)) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Vui lòng chọn thời gian phỏng vấn.',
            ]);
        }

        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $scheduledAt = static::resolveInterviewScheduledAt($data['scheduled_at']);
        $duration = max(15, (int) ($data['duration_minutes'] ?? 60));
        $endAt = $scheduledAt->copy()->addMinutes($duration);

        if ($scheduledAt->lt(now($timezone))) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Thời gian phỏng vấn không được ở quá khứ.',
            ]);
        }

        $interviewerId = (int) ($data['interviewer_id'] ?? 0);

        if ($interviewerId > 0 && static::hasInterviewOverlap(
            $scheduledAt,
            $endAt,
            $existingInterview?->id,
            fn (Builder $query): Builder => $query->where('interviewer_id', $interviewerId),
        )) {
            throw ValidationException::withMessages([
                'interviewer_id' => 'Người phỏng vấn đã có lịch trong khoảng thời gian này.',
            ]);
        }

        $workplaceId = (int) ($data['workplace_id'] ?? 0);

        if (($data['type'] ?? null) === 'offline'
            && $workplaceId > 0
            && static::hasInterviewOverlap(
                $scheduledAt,
                $endAt,
                $existingInterview?->id,
                fn (Builder $query): Builder => $query->where('workplace_id', $workplaceId),
            )
        ) {
            throw ValidationException::withMessages([
                'workplace_id' => 'Địa điểm phỏng vấn đã có lịch trong khoảng thời gian này.',
            ]);
        }

        return $scheduledAt;
    }

    protected static function hasInterviewOverlap(
        \Carbon\CarbonInterface $startAt,
        \Carbon\CarbonInterface $endAt,
        ?int $ignoreInterviewId,
        \Closure $scope,
    ): bool {
        $query = Interview::query()
            ->where('result', 'pending')
            ->where('scheduled_at', '>=', $startAt->copy()->subDay())
            ->where('scheduled_at', '<', $endAt);

        if ($ignoreInterviewId) {
            $query->whereKeyNot($ignoreInterviewId);
        }

        $scope($query);

        return $query
            ->get(['id', 'scheduled_at', 'duration_minutes'])
            ->contains(function (Interview $interview) use ($startAt): bool {
                $interviewEndAt = $interview->scheduled_at
                    ? $interview->scheduled_at->copy()->addMinutes(max(15, (int) ($interview->duration_minutes ?: 60)))
                    : null;

                return $interviewEndAt?->gt($startAt) ?? false;
            });
    }

    protected static function transitionApplication(Application $record, StatusApplicationEnum $targetStatus, ?string $comment = null): bool
    {
        $currentStatus = $record->status instanceof StatusApplicationEnum
            ? $record->status
            : StatusApplicationEnum::tryFrom((string) $record->status);

        if ($currentStatus === $targetStatus) {
            return true;
        }

        try {
            app(ApplicationPipelineService::class)->transition($record, $targetStatus, Auth::user(), $comment);

            return true;
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            Notification::make()
                ->warning()
                ->title('Không thể chuyển trạng thái')
                ->body($errors['status'][0] ?? 'Trạng thái không phù hợp với luồng tuyển dụng hiện tại.')
                ->send();

            return false;
        }
    }

    protected static function currentUserCanOverseeRecruitment(): bool
    {
        return static::workflowGuard()->canOverseeRecruitment(Auth::user());
    }

    protected static function workflowGuard(): ApplicationWorkflowGuard
    {
        return app(ApplicationWorkflowGuard::class);
    }

    protected static function workflowSummary(Application $record): array
    {
        return app(ApplicationWorkflowSummaryService::class)->summarize($record);
    }

    protected static function currentUserIsHr(): bool
    {
        return static::workflowGuard()->isHr(Auth::user());
    }

    protected static function currentUserCanRunHrPipelineActions(): bool
    {
        return static::workflowGuard()->canRunHrPipelineActions(Auth::user());
    }

    protected static function applicationBranchId(Application $record): ?int
    {
        return static::workflowGuard()->applicationBranchId($record);
    }

    protected static function currentUserCanAccessApplicationBranch(Application $record): bool
    {
        return static::workflowGuard()->canAccessApplicationBranch(Auth::user(), $record);
    }

    protected static function currentUserIsAssignedInterviewer(Application $record): bool
    {
        return static::workflowGuard()->isAssignedInterviewer(Auth::user(), $record);
    }

    protected static function canScreenApplication(Application $record): bool
    {
        return static::workflowGuard()->canScreenApplication(Auth::user(), $record);
    }

    protected static function canManageInterview(Application $record): bool
    {
        return static::workflowGuard()->canManageInterview(Auth::user(), $record);
    }

    protected static function canRecordPreScreening(Application $record): bool
    {
        return static::workflowGuard()->canRecordPreScreening(Auth::user(), $record);
    }

    protected static function hasInterviewStatus(Application $record): bool
    {
        return static::workflowGuard()->hasInterviewStatus($record);
    }

    protected static function canEvaluateInterview(Application $record): bool
    {
        if (! static::workflowGuard()->canEvaluateInterview(Auth::user(), $record)) {
            return false;
        }

        $interview = $record->interviews()->latest('id')->first();

        return ! $interview?->scorecards()
            ->where('evaluator_id', Auth::id())
            ->whereNotNull('submitted_at')
            ->exists();
    }

    protected static function canSendInterviewSchedule(Application $record): bool
    {
        return static::workflowGuard()->canSendInterviewSchedule(Auth::user(), $record);
    }

    protected static function canRejectApplication(Application $record): bool
    {
        return static::workflowGuard()->canRejectApplication(Auth::user(), $record);
    }

    protected static function canReopenOfferResponse(Application $record): bool
    {
        return static::workflowGuard()->canReopenOfferResponse(Auth::user(), $record);
    }

    protected static function getInterviewActionLabel(Application $record): ?string
    {
        if (! static::canManageInterview($record)) {
            return null;
        }

        return static::hasInterviewStatus($record) ? 'Cập nhật lịch' : 'Tạo lịch phỏng vấn';
    }

    protected static function getPipelineActionLabel(Application $record): ?string
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

        if ($status === StatusApplicationEnum::NEW && static::canScreenApplication($record)) {
            return 'Sàng lọc CV';
        }

        if (static::canRecordPreScreening($record)) {
            return 'Cập nhật sơ tuyển';
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

        if ($status === StatusApplicationEnum::NEW && static::canScreenApplication($record)) {
            return 'warning';
        }

        if (static::canRecordPreScreening($record)) {
            return 'info';
        }

        if (static::canManageInterview($record)) {
            return static::hasInterviewStatus($record) ? 'info' : 'warning';
        }

        if (static::canManageOffer($record)) {
            return 'primary';
        }

        return 'gray';
    }

    protected static function getPipelineActionIcon(Application $record): string
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

        if ($status === StatusApplicationEnum::NEW && static::canScreenApplication($record)) {
            return 'heroicon-o-document-magnifying-glass';
        }

        if (static::canRecordPreScreening($record)) {
            return 'heroicon-o-phone';
        }

        if (static::canManageInterview($record)) {
            return 'heroicon-o-calendar-days';
        }

        if (static::canManageOffer($record)) {
            return 'heroicon-o-document-text';
        }

        return 'heroicon-o-hand-raised';
    }

    protected static function canManageOffer(Application $record): bool
    {
        return static::workflowGuard()->canManageOffer(Auth::user(), $record);
    }

    protected static function getLatestOffer(Application $record): ?Offer
    {
        return $record->latestOffer ?? $record->offers()->latest('id')->first();
    }

    protected static function canEditOffer(?Offer $offer): bool
    {
        return static::workflowGuard()->canEditOffer($offer);
    }

    protected static function shouldCreateReplacementOffer(?Offer $offer): bool
    {
        return static::workflowGuard()->shouldCreateReplacementOffer($offer);
    }

    protected static function lockedOfferMessage(?Offer $offer): string
    {
        return match ($offer?->status) {
            'awaiting_approval' => 'Đề nghị tuyển dụng đã gửi giám đốc duyệt, không thể chỉnh sửa trực tiếp.',
            'pending' => 'Đề nghị tuyển dụng đã gửi cho ứng viên phản hồi, không thể chỉnh sửa trực tiếp.',
            'accepted' => 'Ứng viên đã đồng ý đề nghị tuyển dụng, không thể chỉnh sửa.',
            'declined' => 'Ứng viên đã từ chối đề nghị tuyển dụng, không thể chỉnh sửa.',
            'expired' => 'Đề nghị tuyển dụng đã hết hạn, không thể chỉnh sửa.',
            default => 'Trạng thái đề nghị tuyển dụng hiện tại không cho phép chỉnh sửa.',
        };
    }

    protected static function getOfferActionLabel(Application $record): ?string
    {
        if (! static::canManageOffer($record)) {
            return null;
        }

        $offer = static::getLatestOffer($record);

        if (static::shouldCreateReplacementOffer($offer)) {
            return 'Tạo đề nghị mới';
        }

        if (! static::canEditOffer($offer)) {
            return null;
        }

        return $offer ? 'Sửa đề nghị tuyển dụng' : 'Tạo đề nghị tuyển dụng';
    }

    protected static function canSendOffer(Application $record): bool
    {
        $offer = static::getLatestOffer($record);

        if (! static::canManageOffer($record) || ! filled($record->snapshotCandidateEmail()) || ! $offer) {
            return false;
        }

        // HR có thể gửi offer trong các trạng thái: awaiting_approval, rejected, pending
        if (Auth::user()?->hasRole('director') === true) {
            // Director không gửi từ đây (xử lý trong OfferResource)
            return false;
        }

        // Chỉ gửi khi đề nghị còn là nháp hoặc đã bị giám đốc từ chối và cần gửi duyệt lại.
        return in_array($offer->status, ['draft', 'rejected'], true);
    }

    protected static function shouldPrioritizeSendInterviewSchedule(Application $record): bool
    {
        return static::canSendInterviewSchedule($record)
            && ! static::canEvaluateInterview($record);
    }

    protected static function shouldPrioritizeSendOffer(Application $record): bool
    {
        return static::canSendOffer($record);
    }

    protected static function shouldShowPrimaryPipelineAction(Application $record): bool
    {
        return static::getPipelineActionLabel($record) !== null
            && ! static::canEvaluateInterview($record)
            && ! static::shouldPrioritizeSendInterviewSchedule($record)
            && ! static::shouldPrioritizeSendOffer($record);
    }

    protected static function shouldShowSecondaryPipelineAction(Application $record): bool
    {
        return static::getPipelineActionLabel($record) !== null
            && ! static::canEvaluateInterview($record)
            && (
                static::shouldPrioritizeSendInterviewSchedule($record)
                || static::shouldPrioritizeSendOffer($record)
            );
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
            ->where(function (Builder $query): void {
                $query
                    ->where('role', 'director')
                    ->orWhereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'director'));
            })
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

        if (filled($record->snapshotCandidateEmail())) {
            $recipients[$record->snapshotCandidateEmail()] = 'candidate';
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

        if (! in_array($status, [StatusApplicationEnum::INTERVIEW_SCHEDULED, StatusApplicationEnum::INTERVIEW], true)
            || ! $interview
            || ! $interview->scheduled_at
        ) {
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
            'draft' => 'Đã lưu nháp đề nghị',
            'awaiting_approval' => 'Chờ duyệt đề nghị',
            'rejected' => 'Giám đốc từ chối',
            'pending' => $record->latestOffer?->sent_at ? 'Chờ ứng viên phản hồi' : 'Chưa gửi thư mời',
            default => null,
        };
    }

    protected static function getOfferResponseDescription(Application $record): ?string
    {
        $offer = $record->latestOffer;

        if ($offer?->status !== 'rejected') {
            return null;
        }

        $notes = trim((string) $offer->approval_notes);

        return $notes !== ''
            ? 'Lý do: '.$notes
            : 'Chưa có lý do từ chối.';
    }

    protected static function getOfferResponseColor(Application $record): string
    {
        return match ($record->latestOffer?->status) {
            'accepted' => 'success',
            'declined', 'rejected' => 'danger',
            'expired' => 'gray',
            'draft', 'awaiting_approval' => 'warning',
            'pending' => 'warning',
            default => 'gray',
        };
    }

    protected static function makeAnalyzeScreeningAiAction(): Action
    {
        return Action::make('analyze_screening_ai')
            ->label(fn (Application $record): string => $record->latestScreeningAiAnalysis?->status === 'completed'
                ? 'Phân tích lại AI'
                : 'Phân tích CV bằng AI')
            ->icon('heroicon-o-sparkles')
            ->color('info')
            ->requiresConfirmation()
            ->action(function (Application $record): void {
                $force = $record->latestScreeningAiAnalysis?->status === 'completed';
                $analysis = app(ApplicationAiAnalysisService::class)
                    ->analyzeScreening($record, Auth::user(), 'admin', $force);

                if ($analysis->status === 'completed') {
                    Notification::make()
                        ->success()
                        ->title('Đã có kết quả phân tích AI')
                        ->body('Mở lại modal Sàng lọc CV để xem điểm phù hợp, tóm tắt và gợi ý ghi chú.')
                        ->send();

                    return;
                }

                Notification::make()
                    ->warning()
                    ->title('Chưa thể phân tích AI')
                    ->body($analysis->error_message ?: 'Vui lòng kiểm tra CV, JD hoặc cấu hình GEMINI_API_KEY.')
                    ->send();
            })
            ->visible(function (Application $record): bool {
                $status = $record->status instanceof StatusApplicationEnum
                    ? $record->status
                    : StatusApplicationEnum::tryFrom((string) $record->status);

                return $status === StatusApplicationEnum::NEW
                    && static::canScreenApplication($record)
                    && filled($record->submittedCvPath());
            });
    }

    public static function renderScreeningAiAnalysis(Application $record): HtmlString
    {
        $analysis = $record->latestScreeningAiAnalysis
            ?? $record->latestScreeningAiAnalysis()->latest('id')->first();

        if (! $analysis) {
            return new HtmlString(
                '<div style="grid-column:1/-1;border:1px dashed rgba(148,163,184,.65);border-radius:12px;padding:12px 14px;background:rgba(148,163,184,.08);font-size:13px;line-height:1.45;color:#64748b;">'
                .'<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:4px;">'
                .'<strong style="color:#334155;">Gợi ý AI</strong>'
                .'<span style="border-radius:999px;background:#e2e8f0;color:#475569;padding:3px 8px;font-size:11px;font-weight:700;white-space:nowrap;">Chưa phân tích</span>'
                .'</div>'
                .'Chạy phân tích AI để so khớp CV với JD, tạo cơ sở tham khảo trước khi HR quyết định.'
                .'</div>'
            );
        }

        if ($analysis->status !== 'completed') {
            $message = e($analysis->error_message ?: 'Kết quả phân tích chưa sẵn sàng.');

            return new HtmlString(
                '<div style="grid-column:1/-1;border:1px solid #f59e0b;border-radius:10px;padding:12px 14px;background:rgba(245,158,11,.10);font-size:13px;line-height:1.45;color:#92400e;">'
                .'<strong style="display:block;margin-bottom:4px;">Phân tích AI chưa hoàn tất</strong>'
                .$message
                .'</div>'
            );
        }

        $score = is_numeric($analysis->score) ? (int) $analysis->score : 0;
        $recommendation = static::aiRecommendationLabel($analysis->recommendation);
        $recommendationColor = static::aiRecommendationColor($analysis->recommendation);
        $resultJson = (array) ($analysis->result_json ?? []);
        $evidenceItems = (array) data_get($resultJson, 'evidence', []);
        $riskItems = (array) data_get($resultJson, 'risks', []);
        $evidence = static::renderAiList($evidenceItems !== [] ? $evidenceItems : $analysis->strengths);
        $gaps = static::renderAiList($analysis->gaps);
        $summary = e($analysis->summary ?: 'AI chưa trả về tóm tắt.');
        $suggestedNote = e($analysis->suggested_note ?: 'Chưa có gợi ý ghi chú.');
        $nextStepHint = trim((string) data_get($resultJson, 'next_step_hint', ''));
        $nextStepHtml = $nextStepHint !== ''
            ? '<div style="margin-top:10px;border-top:1px solid #e2e8f0;padding-top:10px;"><div style="font-size:12px;font-weight:800;color:#475569;margin-bottom:4px;">Gợi ý bước tiếp theo</div><div style="font-size:13px;line-height:1.45;color:#334155;">'.e($nextStepHint).'</div></div>'
            : '';
        $risksHtml = $riskItems !== []
            ? '<div><div style="font-size:12px;font-weight:800;color:#b45309;margin-bottom:4px;">Lưu ý rủi ro</div>'.static::renderAiList($riskItems).'</div>'
            : '';
        $analyzedAt = $analysis->analyzed_at?->format('d/m/Y H:i') ?? '';

        return new HtmlString(<<<HTML
            <div style="grid-column:1/-1;border:1px solid #dbe3ef;border-radius:14px;background:#ffffff;padding:14px;color:#0f172a;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px;">
                    <div>
                        <div style="font-size:14px;font-weight:800;">Gợi ý AI sàng lọc</div>
                        <div style="font-size:12px;color:#64748b;">{$analyzedAt}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                        <span style="border-radius:999px;background:#0f172a;color:#fff;padding:4px 9px;font-size:12px;font-weight:800;white-space:nowrap;">{$score}/100</span>
                        <span style="border-radius:999px;background:{$recommendationColor};color:#fff;padding:4px 9px;font-size:12px;font-weight:700;white-space:nowrap;">{$recommendation}</span>
                    </div>
                </div>
                <div style="border-left:3px solid {$recommendationColor};padding-left:10px;font-size:13px;line-height:1.5;color:#334155;">{$summary}</div>
                <div style="margin-top:12px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:10px;">
                    <div style="display:grid;grid-template-columns:1fr;gap:10px;">
                        <div>
                            <div style="font-size:12px;font-weight:800;color:#166534;margin-bottom:4px;">Căn cứ phù hợp</div>
                            {$evidence}
                        </div>
                        <div>
                            <div style="font-size:12px;font-weight:800;color:#92400e;margin-bottom:4px;">Điểm cần làm rõ</div>
                            {$gaps}
                        </div>
                        {$risksHtml}
                        <div style="border-top:1px solid #e2e8f0;padding-top:10px;">
                            <div style="font-size:12px;font-weight:800;color:#475569;margin-bottom:4px;">Gợi ý ghi chú sàng lọc</div>
                            <div style="font-size:13px;line-height:1.45;color:#334155;">{$suggestedNote}</div>
                        </div>
                        {$nextStepHtml}
                    </div>
                </div>
            </div>
        HTML);
    }

    protected static function renderAiList(?array $items): string
    {
        $items = static::normalizeAiListItems($items ?? []);

        if ($items === []) {
            return '<div style="font-size:13px;color:#64748b;">Chưa có dữ liệu.</div>';
        }

        $html = '<div style="display:grid;gap:6px;font-size:13px;line-height:1.45;color:#334155;">';
        foreach (array_slice($items, 0, 3) as $item) {
            $html .= '<div style="display:grid;grid-template-columns:7px minmax(0,1fr);gap:8px;align-items:start;">'
                .'<span style="width:5px;height:5px;border-radius:999px;background:#94a3b8;margin-top:7px;"></span>'
                .'<span>'.e((string) $item).'</span>'
                .'</div>';
        }

        return $html.'</div>';
    }

    protected static function normalizeAiListItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $text = trim((string) $item);

            if ($text === '') {
                continue;
            }

            $parts = preg_split('/(?:\r?\n|\s*-\s+|\s*•\s*|\s*\d+[\.)]\s+|;\s*)/u', $text) ?: [$text];

            foreach ($parts as $part) {
                $part = trim($part);

                if ($part !== '') {
                    $normalized[] = $part;
                }
            }
        }

        return array_values(array_unique($normalized));
    }

    protected static function aiRecommendationLabel(?string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => 'Phù hợp',
            'reject' => 'Chưa phù hợp',
            default => 'Cần xem thêm',
        };
    }

    protected static function aiRecommendationColor(?string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => '#16a34a',
            'reject' => '#dc2626',
            default => '#f59e0b',
        };
    }

    protected static function getLatestCompletedAiAnalysis(Application $record, string $type): mixed
    {
        $relationName = match ($type) {
            'screening' => 'latestScreeningAiAnalysis',
            'interview_questions' => 'latestInterviewQuestionAiAnalysis',
            default => null,
        };

        $loaded = $relationName && $record->relationLoaded($relationName)
            ? $record->getRelation($relationName)
            : null;

        if ($loaded?->status === 'completed') {
            return $loaded;
        }

        return $record->aiAnalyses()
            ->where('analysis_type', $type)
            ->where('status', 'completed')
            ->latest('id')
            ->first();
    }

    public static function renderInterviewPreparation(Application $record): HtmlString
    {
        $screening = static::getLatestCompletedAiAnalysis($record, 'screening');
        $questionAnalysis = static::getLatestCompletedAiAnalysis($record, 'interview_questions');

        if (! $screening || $screening->status !== 'completed') {
            return new HtmlString(
                '<div style="border:1px dashed #cbd5e1;border-radius:12px;padding:12px 14px;background:rgba(148,163,184,.08);font-size:13px;line-height:1.45;color:#64748b;">'
                .'<strong style="display:block;color:#334155;margin-bottom:4px;">Gợi ý chuẩn bị phỏng vấn</strong>'
                .'Chưa có dữ liệu sàng lọc AI để gợi ý trọng tâm phỏng vấn.'
                .'</div>'
            );
        }

        $gaps = static::renderAiList($screening->gaps);
        $questions = static::renderInterviewQuestions((array) data_get($questionAnalysis?->result_json, 'questions', []));

        return new HtmlString(<<<HTML
            <div style="border:1px solid #dbe3ef;border-radius:14px;background:#ffffff;padding:14px;color:#0f172a;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                <div style="font-size:14px;font-weight:800;margin-bottom:10px;">Gợi ý chuẩn bị phỏng vấn</div>
                <div>
                    <div style="font-size:12px;font-weight:800;color:#475569;margin-bottom:6px;">Cần làm rõ</div>
                    {$gaps}
                </div>
                <div style="margin-top:12px;border-top:1px solid #e2e8f0;padding-top:10px;">
                    <div style="font-size:12px;font-weight:800;color:#475569;margin-bottom:6px;">Câu hỏi gợi ý</div>
                    {$questions}
                </div>
            </div>
        HTML);
    }

    protected static function renderInterviewQuestions(array $questions): string
    {
        $questions = array_values(array_filter($questions, fn ($question): bool => is_array($question) && filled($question['question'] ?? null)));

        if ($questions === []) {
            return '<div style="font-size:13px;line-height:1.45;color:#64748b;">Chưa có câu hỏi gợi ý. Có thể tạo từ điểm cần làm rõ và tiêu chí scorecard hiện tại.</div>';
        }

        $html = '<div style="display:grid;gap:7px;">';
        foreach (array_slice($questions, 0, 4) as $index => $question) {
            $number = $index + 1;
            $criterion = trim((string) ($question['criterion'] ?? ''));
            $type = trim((string) ($question['type'] ?? ''));
            $purpose = trim((string) ($question['purpose'] ?? ''));
            $expectedSignal = trim((string) ($question['expected_signal'] ?? ''));
            $typeLabel = match ($type) {
                'project_deep_dive' => 'Đào sâu dự án',
                'gap_validation' => 'Làm rõ gap',
                'scenario' => 'Tình huống',
                'risk_check' => 'Kiểm tra rủi ro',
                default => '',
            };
            $metaParts = array_filter([$criterion, $typeLabel]);
            $meta = $metaParts !== []
                ? '<div style="font-size:11px;font-weight:700;color:#0369a1;margin-bottom:3px;">'.e(implode(' · ', $metaParts)).'</div>'
                : '';
            $purposeHtml = $purpose !== ''
                ? '<div style="font-size:12px;line-height:1.45;color:#64748b;margin-top:4px;">Mục đích: '.e($purpose).'</div>'
                : '';
            $expectedHtml = $expectedSignal !== ''
                ? '<div style="font-size:12px;line-height:1.45;color:#475569;margin-top:4px;">Tín hiệu tốt: '.e($expectedSignal).'</div>'
                : '';

            $html .= '<div style="display:grid;grid-template-columns:24px 1fr;gap:8px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;padding:9px 10px;">'
                .'<div style="width:22px;height:22px;border-radius:999px;background:#e0f2fe;color:#075985;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;">'.$number.'</div>'
                .'<div>'
                .$meta
                .'<div style="font-size:13px;line-height:1.45;color:#334155;font-weight:650;">'.e((string) $question['question']).'</div>'
                .$purposeHtml
                .$expectedHtml
                .'</div>'
                .'</div>';
        }

        return $html.'</div>';
    }
    protected static function getCvScreeningFormSchema(): array
    {
        return [
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Section::make('Thông tin & quyết định')
                        ->description('Đối chiếu nhanh hồ sơ trước khi chuyển bước.')
                        ->columns(2)
                        ->schema([
                            Placeholder::make('candidate_name_display')
                                ->label('Ứng viên')
                                ->content(fn (Application $record): string => $record->snapshotCandidateName() ?: '-'),
                            Placeholder::make('job_title_display')
                                ->label('Vị trí')
                                ->content(fn (Application $record): string => $record->job?->title ?? '-'),
                            Placeholder::make('candidate_email_display')
                                ->label('Email')
                                ->content(fn (Application $record): string => $record->snapshotCandidateEmail() ?: '-'),
                            Placeholder::make('candidate_phone_display')
                                ->label('Số điện thoại')
                                ->content(fn (Application $record): string => $record->snapshotCandidatePhone() ?: '-'),
                            Placeholder::make('candidate_experience_display')
                                ->label('Kinh nghiệm')
                                ->content(function (Application $record): string {
                                    $experience = $record->snapshotCandidateExperienceYears();

                                    return is_numeric($experience) ? $experience.' năm' : '-';
                                }),
                            Placeholder::make('profile_title_display')
                                ->label('Tiêu đề hồ sơ')
                                ->content(fn (Application $record): string => $record->snapshotProfileTitle() ?: '-'),
                            Html::make(fn (Application $record): HtmlString => static::renderScreeningAiAnalysis($record))
                                ->columnSpanFull(),
                            Select::make('screening_decision')
                                ->label('Kết quả sàng lọc CV')
                                ->options([
                                    'pass' => 'Đạt sơ tuyển',
                                    'reject' => 'Từ chối',
                                ])
                                ->placeholder('Chọn kết quả sàng lọc')
                                ->live()
                                ->required()
                                ->columnSpanFull(),
                            Textarea::make('screening_note')
                                ->label('Ghi chú sàng lọc')
                                ->helperText('Ghi rõ kinh nghiệm/kỹ năng phù hợp hoặc điểm cần xác minh ở vòng sau.')
                                ->rows(4)
                                ->visible(fn (callable $get): bool => $get('screening_decision') === 'pass')
                                ->required(fn (callable $get): bool => $get('screening_decision') === 'pass')
                                ->columnSpanFull(),
                            Textarea::make('rejected_reason')
                                ->label('Lý do từ chối')
                                ->helperText('Ghi rõ lý do hồ sơ chưa phù hợp với yêu cầu tuyển dụng.')
                                ->rows(3)
                                ->visible(fn (callable $get): bool => $get('screening_decision') === 'reject')
                                ->required(fn (callable $get): bool => $get('screening_decision') === 'reject')
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 4]),
                    Section::make('CV ứng tuyển')
                        ->description('CV được lưu theo snapshot tại thời điểm ứng viên nộp hồ sơ.')
                        ->schema([
                            Html::make(function (Application $record): HtmlString {
                                $url = $record->submittedCvUrl();
                                $name = $record->submittedCvName() ?: 'CV ứng tuyển';

                                if (! $url) {
                                    return new HtmlString('<div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Không có CV đính kèm.</div>');
                                }

                                return new HtmlString(
                                    '<div class="space-y-3">'
                                    .'<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">'
                                    .'<div class="min-w-0 text-sm font-medium text-gray-700 dark:text-gray-200">'.e($name).'</div>'
                                    .'<a class="inline-flex shrink-0 items-center justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500" href="'.e($url).'" target="_blank" rel="noopener">Mở CV</a>'
                                    .'</div>'
                                    .'<iframe src="'.e($url).'#toolbar=1&navpanes=0" class="w-full rounded-lg border border-gray-200 bg-white dark:border-gray-700" style="height: min(76vh, 820px); min-height: 640px;" title="CV ứng tuyển"></iframe>'
                                    .'</div>'
                                );
                            }),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 8]),
                ]),
        ];
    }

    protected static function getPreScreeningFormSchema(): array
    {
        return [
            Section::make('Kết quả sơ tuyển')
                ->description('Ghi nhận lần liên hệ với ứng viên trước khi tạo lịch phỏng vấn.')
                ->columns(2)
                ->schema([
                    Placeholder::make('pre_screening_candidate')
                        ->label('Ứng viên')
                        ->content(fn (Application $record): string => $record->snapshotCandidateName()),
                    Placeholder::make('pre_screening_contact')
                        ->label('Liên hệ')
                        ->content(fn (Application $record): string => trim(implode(' · ', array_filter([
                            $record->snapshotCandidatePhone(),
                            $record->snapshotCandidateEmail(),
                        ]))) ?: '-'),
                    Select::make('pre_screening_channel')
                        ->label('Hình thức liên hệ')
                        ->options(app(ApplicationPreScreeningService::class)->contactMethodOptions())
                        ->live()
                        ->required(),
                    TextInput::make('pre_screening_channel_detail')
                        ->label('Hình thức liên hệ khác')
                        ->maxLength(120)
                        ->visible(fn (Get $get): bool => $get('pre_screening_channel') === 'other')
                        ->required(fn (Get $get): bool => $get('pre_screening_channel') === 'other'),
                    DateTimePicker::make('pre_screening_contacted_at')
                        ->label('Thời điểm liên hệ')
                        ->seconds(false)
                        ->maxDate(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))
                        ->required(),
                    Select::make('pre_screening_outcome')
                        ->label('Kết quả sơ tuyển')
                        ->options([
                            'passed' => 'Đạt sơ tuyển',
                            'follow_up' => 'Hẹn liên hệ lại',
                            'rejected' => 'Từ chối hồ sơ',
                        ])
                        ->live()
                        ->required()
                        ->columnSpanFull(),
                    DateTimePicker::make('pre_screening_follow_up_at')
                        ->label('Hẹn liên hệ lại')
                        ->seconds(false)
                        ->minDate(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))
                        ->visible(fn (Get $get): bool => $get('pre_screening_outcome') === 'follow_up')
                        ->required(fn (Get $get): bool => $get('pre_screening_outcome') === 'follow_up'),
                    Textarea::make('pre_screening_note')
                        ->label('Ghi chú trao đổi')
                        ->placeholder('Tóm tắt mức độ quan tâm, điều kiện làm việc hoặc nội dung cần lưu ý.')
                        ->rows(3)
                        ->visible(fn (Get $get): bool => in_array($get('pre_screening_outcome'), ['passed', 'follow_up'], true))
                        ->required(fn (Get $get): bool => in_array($get('pre_screening_outcome'), ['passed', 'follow_up'], true))
                        ->columnSpanFull(),
                    Select::make('pre_screening_rejection_reason_code')
                        ->label('Phân loại lý do')
                        ->options(app(ApplicationPreScreeningService::class)->rejectionReasonOptions())
                        ->live()
                        ->visible(fn (Get $get): bool => $get('pre_screening_outcome') === 'rejected')
                        ->required(fn (Get $get): bool => $get('pre_screening_outcome') === 'rejected'),
                    Textarea::make('pre_screening_rejection_reason')
                        ->label('Lý do từ chối')
                        ->placeholder('Nêu lý do phù hợp để lưu lịch sử và phản hồi ứng viên.')
                        ->rows(3)
                        ->visible(fn (Get $get): bool => $get('pre_screening_outcome') === 'rejected' && $get('pre_screening_rejection_reason_code') === 'other')
                        ->required(fn (Get $get): bool => $get('pre_screening_outcome') === 'rejected' && $get('pre_screening_rejection_reason_code') === 'other')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected static function getInterviewSchedulingFormSchema(): array
    {
        return [
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Section::make('Thông tin & căn cứ')
                        ->description('Đối chiếu hồ sơ trước khi tạo lịch phỏng vấn.')
                        ->columns(2)
                        ->schema([
                            Placeholder::make('candidate_name_display')
                                ->label('Ứng viên')
                                ->content(fn (Application $record): string => $record->snapshotCandidateName() ?: '-'),
                            Placeholder::make('job_title_display')
                                ->label('Vị trí')
                                ->content(fn (Application $record): string => $record->job?->title ?? '-'),
                            Placeholder::make('candidate_email_display')
                                ->label('Email')
                                ->content(fn (Application $record): string => $record->snapshotCandidateEmail() ?: '-'),
                            Placeholder::make('candidate_phone_display')
                                ->label('Số điện thoại')
                                ->content(fn (Application $record): string => $record->snapshotCandidatePhone() ?: '-'),
                            Placeholder::make('screening_comment_display')
                                ->label('Ghi chú sàng lọc')
                                ->content(fn (Application $record): string => static::getLatestScreeningComment($record) ?: '-')
                                ->columnSpanFull(),
                            Placeholder::make('submitted_cv_display')
                                ->label('CV ứng tuyển')
                                ->content(function (Application $record): HtmlString|string {
                                    $url = $record->submittedCvUrl();
                                    $name = $record->submittedCvName() ?: 'Mở CV';

                                    if (! $url) {
                                        return 'Không có CV đính kèm';
                                    }

                                    return new HtmlString('<a class="text-primary-600 hover:underline" href="'.e($url).'" target="_blank" rel="noopener">'.e($name).'</a>');
                                })
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 4]),
                    Section::make('Lịch phỏng vấn')
                        ->description('Thiết lập thời gian, hình thức và người phụ trách phỏng vấn.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('round_name')
                                ->label('Tên vòng phỏng vấn')
                                ->placeholder('Phỏng vấn vòng 1')
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('scheduled_at')
                                ->label('Thời gian phỏng vấn')
                                ->type('datetime-local')
                                ->placeholder('2026-07-26T13:10')
                                ->extraInputAttributes(fn (): array => [
                                    'min' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('Y-m-d\TH:i'),
                                    'step' => 60,
                                ])
                                ->helperText('Chọn giờ hiện tại hoặc tương lai, theo múi giờ Việt Nam.')
                                ->required(),
                            Select::make('duration_minutes')
                                ->label('Thời lượng')
                                ->options([
                                    30 => '30 phút',
                                    45 => '45 phút',
                                    60 => '60 phút',
                                    90 => '90 phút',
                                ])
                                ->default(60)
                                ->required(),
                            Select::make('interviewer_id')
                                ->label('Người phỏng vấn')
                                ->options(fn (Application $record): array => static::getInterviewerOptions($record))
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('scorecard_template_id')
                                ->label('Mẫu đánh giá')
                                ->options(fn (): array => app(InterviewScorecardTemplateService::class)->options())
                                ->helperText('Tiêu chí được giữ cố định cho buổi phỏng vấn này.')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('type')
                                ->label('Hình thức phỏng vấn')
                                ->options(['online' => 'Online', 'offline' => 'Offline'])
                                ->default('online')
                                ->live()
                                ->required(),
                            TextInput::make('meeting_link')
                                ->label('Link phỏng vấn')
                                ->placeholder('https://meet.google.com/...')
                                ->helperText('Dán link Google Meet, Zoom, Teams hoặc nền tảng họp trực tuyến hợp lệ. Link này sẽ được gửi cho ứng viên.')
                                ->url()
                                ->rules([new InterviewMeetingLink(app(InterviewMeetingLinkValidator::class))])
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
                            Textarea::make('notes')
                                ->label('Ghi chú gửi kèm lịch phỏng vấn')
                                ->helperText('Nội dung này sẽ được gửi trong email lịch phỏng vấn cho ứng viên và người phỏng vấn.')
                                ->rows(4)
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 8]),
                ]),
        ];
    }

    protected static function getLatestScreeningComment(Application $record): ?string
    {
        $history = $record->statusHistories()
            ->where('to_status', StatusApplicationEnum::SCREENING->value)
            ->latest('id')
            ->first();

        return $history?->comment;
    }

    protected static function buildInterviewScheduleComment(Interview $interview, bool $isUpdate): string
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $scheduledAt = $interview->scheduled_at
            ? $interview->scheduled_at->copy()->setTimezone($timezone)->format('H:i, d/m/Y')
            : '-';
        $type = $interview->type === 'offline' ? 'Offline' : 'Online';
        $duration = (int) ($interview->duration_minutes ?: 60);
        $location = app(InterviewCalendarService::class)->resolveLocation($interview);
        $prefix = $isUpdate ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn';

        return sprintf(
            '%s: %s, %s, %s, %d phút, %s.',
            $prefix,
            $interview->round_name ?: 'Phỏng vấn',
            $scheduledAt,
            $type,
            $duration,
            $location ?: 'Chưa có địa điểm/link'
        );
    }

    protected static function getInterviewEvaluationFormSchema(): array
    {
        return [
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Section::make('Thông tin & căn cứ')
                        ->description('Đối chiếu lại lịch phỏng vấn và các ghi chú trước khi chấm điểm.')
                        ->columns(2)
                        ->schema([
                            Placeholder::make('candidate_name_display')
                                ->label('Ứng viên')
                                ->content(fn (Application $record): string => $record->snapshotCandidateName() ?: '-'),
                            Placeholder::make('job_title_display')
                                ->label('Vị trí')
                                ->content(fn (Application $record): string => $record->job?->title ?? '-'),
                            Placeholder::make('interview_schedule_display')
                                ->label('Lịch phỏng vấn')
                                ->content(function (Application $record): string {
                                    $interview = $record->interviews()->latest('id')->first();
                                    $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');

                                    return $interview?->scheduled_at
                                        ? $interview->scheduled_at->copy()->setTimezone($timezone)->format('H:i, d/m/Y')
                                        : '-';
                                }),
                            Placeholder::make('interviewer_display')
                                ->label('Người phỏng vấn')
                                ->content(fn (Application $record): string => $record->interviews()->latest('id')->first()?->interviewer?->name ?? '-'),
                            Placeholder::make('screening_comment_display')
                                ->label('Ghi chú sàng lọc')
                                ->content(fn (Application $record): string => static::getLatestScreeningComment($record) ?: '-')
                                ->columnSpanFull(),
                            Html::make(fn (Application $record): HtmlString => static::renderInterviewPreparation($record))
                                ->columnSpanFull(),
                            SchemaActions::make([
                                Action::make('generate_interview_questions')
                                    ->label(fn (Application $record): string => static::getLatestCompletedAiAnalysis($record, 'interview_questions')
                                        ? 'Tạo lại câu hỏi gợi ý'
                                        : 'Tạo câu hỏi gợi ý')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('info')
                                    ->requiresConfirmation()
                                    ->modalHeading('Tạo câu hỏi gợi ý bằng AI?')
                                    ->modalDescription('AI sẽ dùng kết quả sàng lọc và tiêu chí scorecard hiện tại để tạo danh sách câu hỏi tham khảo cho buổi phỏng vấn.')
                                    ->action(function (Application $record, Get $get): void {
                                        $existingQuestions = static::getLatestCompletedAiAnalysis($record, 'interview_questions');
                                        $analysis = app(ApplicationAiAnalysisService::class)
                                            ->generateInterviewQuestions(
                                                $record,
                                                (array) ($get('criteria') ?? []),
                                                Auth::user(),
                                                'admin',
                                                filled($existingQuestions),
                                            );

                                        if ($analysis->status === 'completed') {
                                            Notification::make()
                                                ->success()
                                                ->title('Đã tạo câu hỏi gợi ý')
                                                ->body('Danh sách câu hỏi đã được lưu cho hồ sơ phỏng vấn này.')
                                                ->send();

                                            return;
                                        }

                                        Notification::make()
                                            ->warning()
                                            ->title('Chưa thể tạo câu hỏi')
                                            ->body($analysis->error_message ?: 'Vui lòng kiểm tra kết quả sàng lọc AI hoặc cấu hình AI.')
                                            ->send();
                                    })
                                    ->visible(fn (Application $record): bool => filled(static::getLatestCompletedAiAnalysis($record, 'screening'))),
                            ])
                                ->columnSpanFull(),
                            Placeholder::make('interview_note_display')
                                ->label('Ghi chú lịch phỏng vấn')
                                ->content(fn (Application $record): string => $record->interviews()->latest('id')->first()?->notes ?: '-')
                                ->columnSpanFull(),
                            Placeholder::make('recorded_scorecards_display')
                                ->label('Đánh giá đã ghi nhận')
                                ->content(fn (Application $record): HtmlString => static::renderInterviewScorecardsSummary($record))
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 4]),
                    Section::make('Scorecard phỏng vấn')
                        ->description('Chấm điểm theo tiêu chí và chọn kết luận để quyết định bước tiếp theo.')
                        ->schema([
                            Select::make('template_id')
                                ->label('Mẫu scorecard')
                                ->options(fn (): array => ScorecardTemplate::query()
                                    ->orderByDesc('is_default')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->disabled(fn (Application $record): bool => filled($record->interviews()->latest('id')->first()?->scorecard_template_id))
                                ->dehydrated()
                                ->helperText(fn (Application $record): string => filled($record->interviews()->latest('id')->first()?->scorecard_template_id)
                                    ? 'Mẫu đã được gắn khi tạo lịch phỏng vấn.'
                                    : 'Chọn mẫu để hiển thị bộ tiêu chí thống nhất cho buổi phỏng vấn.')
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    if (blank($state)) {
                                        return;
                                    }

                                    $criteria = ScorecardTemplate::query()->find($state)?->criteria;
                                    if (is_array($criteria) && $criteria !== []) {
                                        $set('criteria', $criteria);
                                    }
                                }),
                            Hidden::make('interview_id'),
                            Repeater::make('criteria')
                                ->label('Tiêu chí chấm điểm')
                                ->live()
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Tiêu chí')
                                        ->disabled()
                                        ->dehydrated(),
                                    Select::make('score')
                                        ->label('Điểm')
                                        ->options(static::interviewScoreOptions())
                                        ->native(false)
                                        ->searchable(false),
                                    Textarea::make('note')
                                        ->label('Nhận xét tiêu chí')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->columns(2)
                                ->columnSpanFull(),
                            Checkbox::make('confirm_early_completion')
                                ->label('Xác nhận buổi phỏng vấn đã kết thúc để hoàn tất đánh giá sớm')
                                ->live()
                                ->visible(fn (Application $record): bool => ! static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record))
                                ->columnSpanFull(),
                            Placeholder::make('score_recommendation_display')
                                ->label('Khuyến nghị từ điểm số')
                                ->content(function (callable $get): string {
                                    if (static::hasInvalidInterviewScore($get('criteria') ?? [])) {
                                        return 'Điểm từng tiêu chí phải nằm trong khoảng 0-10.';
                                    }

                                    $average = static::calculateInterviewAverage($get('criteria') ?? []);
                                    $recommendedConclusion = static::recommendedInterviewConclusion($average);

                                    if ($average === null || $recommendedConclusion === null) {
                                        return 'Chưa đủ điểm để tính khuyến nghị.';
                                    }

                                    return 'Điểm trung bình: '
                                        .number_format($average, 2, ',', '.')
                                        .'/10 - Khuyến nghị: '
                                        .static::interviewConclusionLabel($recommendedConclusion);
                                })
                                ->columnSpanFull()
                                ->visible(fn (Application $record, callable $get): bool => static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record, (bool) $get('confirm_early_completion'))),
                            Select::make('conclusion')
                                ->label('Kết luận phỏng vấn')
                                ->options([
                                    'pass' => 'Đạt - chuyển sang đề nghị tuyển dụng',
                                    'hold' => 'Cân nhắc thêm - giữ ở phỏng vấn',
                                    'fail' => 'Không đạt - từ chối',
                                ])
                                ->live()
                                ->required()
                                ->visible(fn (Application $record, callable $get): bool => static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record, (bool) $get('confirm_early_completion'))),
                            Textarea::make('override_reason')
                                ->label('Lý do quyết định khác khuyến nghị')
                                ->helperText('Bắt buộc khi kết luận cuối khác khuyến nghị từ điểm trung bình.')
                                ->rows(3)
                                ->visible(function (Application $record, callable $get): bool {
                                    if (! static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record, (bool) $get('confirm_early_completion'))) {
                                        return false;
                                    }

                                    $recommendedConclusion = static::recommendedInterviewConclusion(
                                        static::calculateInterviewAverage($get('criteria') ?? [])
                                    );

                                    return static::isInterviewConclusionOverride($get('conclusion'), $recommendedConclusion);
                                })
                                ->required(function (Application $record, callable $get): bool {
                                    if (! static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record, (bool) $get('confirm_early_completion'))) {
                                        return false;
                                    }

                                    $recommendedConclusion = static::recommendedInterviewConclusion(
                                        static::calculateInterviewAverage($get('criteria') ?? [])
                                    );

                                    return static::isInterviewConclusionOverride($get('conclusion'), $recommendedConclusion);
                                })
                                ->columnSpanFull(),
                            Textarea::make('notes')
                                ->label('Nhận xét nội bộ sau phỏng vấn')
                                ->helperText('Chỉ phục vụ HR và người quản lý trong quá trình xem xét hồ sơ.')
                                ->rows(5)
                                ->columnSpanFull(),
                            Textarea::make('rejected_reason')
                                ->label('Thông tin phản hồi ứng viên khi từ chối')
                                ->helperText('Bắt buộc khi không đạt; nội dung này được dùng để phản hồi ứng viên.')
                                ->rows(3)
                                ->visible(fn (Application $record, callable $get): bool => static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record, (bool) $get('confirm_early_completion')) && $get('conclusion') === 'fail')
                                ->required(fn (Application $record, callable $get): bool => static::workflowGuard()->canFinalizeInterviewEvaluation(Auth::user(), $record, (bool) $get('confirm_early_completion')) && $get('conclusion') === 'fail')
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 8]),
                ]),
        ];
    }

    protected static function getCurrentEvaluatorScorecard(Application $record, Interview $interview): ?Scorecard
    {
        return $record->scorecards()
            ->where('interview_id', $interview->id)
            ->where('evaluator_id', Auth::id())
            ->latest('id')
            ->first();
    }

    protected static function getInterviewScorecards(Application $record): \Illuminate\Support\Collection
    {
        $interview = $record->interviews()->latest('id')->first();

        if (! $interview) {
            return collect();
        }

        return $record->scorecards()
            ->with(['evaluator', 'interview', 'template'])
            ->where('interview_id', $interview->id)
            ->latest('updated_at')
            ->get();
    }

    public static function renderInterviewScorecardsSummary(Application $record): HtmlString
    {
        $scorecards = static::getInterviewScorecards($record);

        if ($scorecards->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-500 dark:text-gray-400">Chưa có đánh giá nào được ghi nhận.</span>');
        }

        $items = $scorecards->map(function (Scorecard $scorecard): string {
            $evaluator = e($scorecard->evaluator?->name ?? 'Người đánh giá');
            $role = e(static::formatUserRole($scorecard->evaluator?->role));
            $roundName = e(
                $scorecard->interview?->round_name
                    ?: ($scorecard->interview?->round_number ? 'Vòng '.$scorecard->interview->round_number : 'Vòng phỏng vấn')
            );
            $templateName = filled($scorecard->template?->name)
                ? '<span class="text-gray-500 dark:text-gray-400">Mẫu:</span> <span class="font-medium text-gray-900 dark:text-gray-100">'.e($scorecard->template->name).'</span>'
                : '';
            $updatedAt = $scorecard->updated_at
                ? e($scorecard->updated_at->copy()->setTimezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y'))
                : '-';
            $average = $scorecard->average_score !== null
                ? e(number_format((float) $scorecard->average_score, 2, ',', '.').'/10')
                : '-';
            $recommendation = e(static::interviewConclusionLabel($scorecard->recommended_conclusion));
            $conclusion = e(static::interviewConclusionLabel($scorecard->conclusion));
            $overrideReason = filled($scorecard->override_reason)
                ? '<div class="mt-2 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">Lý do quyết định khác khuyến nghị: '.e($scorecard->override_reason).'</div>'
                : '';
            $notes = filled($scorecard->notes)
                ? '<div class="mt-2 text-xs leading-5 text-gray-600 dark:text-gray-300"><span class="font-medium text-gray-800 dark:text-gray-100">Nhận xét tổng quan:</span> '.e($scorecard->notes).'</div>'
                : '';
            $criteria = is_array($scorecard->criteria) ? $scorecard->criteria : [];
            $criteriaRows = collect($criteria)
                ->filter(fn ($criterion): bool => is_array($criterion))
                ->map(function (array $criterion, int $index): string {
                    $name = e((string) ($criterion['name'] ?? 'Tiêu chí '.($index + 1)));
                    $score = isset($criterion['score']) && $criterion['score'] !== ''
                        ? e(number_format((float) $criterion['score'], 1, ',', '.').'/10')
                        : '-';
                    $note = filled($criterion['note'] ?? null)
                        ? e((string) $criterion['note'])
                        : '<span class="text-gray-400 dark:text-gray-500">Chưa có nhận xét riêng</span>';

                    return <<<HTML
                        <div class="grid gap-2 border-t border-gray-100 px-3 py-2 text-xs first:border-t-0 dark:border-gray-800 md:grid-cols-[minmax(0,1fr)_5rem_minmax(0,1.25fr)]">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{$name}</div>
                            <div class="font-semibold tabular-nums text-gray-900 dark:text-gray-100">{$score}</div>
                            <div class="leading-5 text-gray-600 dark:text-gray-300">{$note}</div>
                        </div>
                    HTML;
                })
                ->implode('');

            $criteriaDetail = $criteriaRows !== ''
                ? <<<HTML
                    <details class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                        <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">
                            Xem chi tiết điểm theo tiêu chí
                        </summary>
                        <div class="border-t border-gray-100 dark:border-gray-800">
                            <div class="hidden grid-cols-[minmax(0,1fr)_5rem_minmax(0,1.25fr)] gap-2 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400 md:grid">
                                <div>Tiêu chí</div>
                                <div>Điểm</div>
                                <div>Nhận xét</div>
                            </div>
                            {$criteriaRows}
                        </div>
                    </details>
                HTML
                : '<div class="mt-3 rounded-lg border border-dashed border-gray-200 px-3 py-2 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">Chưa có chi tiết tiêu chí chấm điểm.</div>';

            return <<<HTML
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{$evaluator}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{$role} · {$roundName}</div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{$updatedAt}</div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                        {$templateName}
                    </div>
                    <div class="mt-2 grid grid-cols-1 gap-2 text-xs md:grid-cols-3">
                        <div><span class="text-gray-500 dark:text-gray-400">Điểm trung bình:</span> <span class="font-semibold text-gray-900 dark:text-gray-100">{$average}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Khuyến nghị:</span> <span class="font-semibold text-gray-900 dark:text-gray-100">{$recommendation}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Kết luận:</span> <span class="font-semibold text-gray-900 dark:text-gray-100">{$conclusion}</span></div>
                    </div>
                    {$overrideReason}
                    {$notes}
                    {$criteriaDetail}
                </div>
            HTML;
        })->implode('');

        return new HtmlString('<div class="space-y-2">'.$items.'</div>');
    }

    protected static function getOfferFormSchema(): array
    {
        return [
            Grid::make(['default' => 1, 'xl' => 12])
                ->schema([
                    Html::make(fn (Application $record): HtmlString => static::renderRejectedOfferNotice($record))
                        ->visible(fn (Application $record): bool => static::getLatestOffer($record)?->status === 'rejected')
                        ->columnSpanFull(),
                    Section::make('Căn cứ đề nghị')
                        ->description('Đối chiếu kết quả phỏng vấn và thông tin ứng viên trước khi lập đề nghị tuyển dụng.')
                        ->columns(2)
                        ->schema([
                            Placeholder::make('candidate_name_display')
                                ->label('Ứng viên')
                                ->content(fn (Application $record): string => $record->snapshotCandidateName() ?: '-'),
                            Placeholder::make('candidate_email_display')
                                ->label('Email nhận đề nghị')
                                ->content(fn (Application $record): string => $record->snapshotCandidateEmail() ?: '-'),
                            Placeholder::make('job_title_display')
                                ->label('Vị trí')
                                ->content(fn (Application $record): string => $record->job?->title ?? '-'),
                            Placeholder::make('branch_display')
                                ->label('Chi nhánh/đơn vị')
                                ->content(fn (Application $record): string => $record->job?->branch?->name ?? '-'),
                            Placeholder::make('scorecards_summary_display')
                                ->label('Đánh giá phỏng vấn')
                                ->content(fn (Application $record): HtmlString => static::renderInterviewScorecardsSummary($record))
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 4]),
                    Section::make('Thông tin đề nghị tuyển dụng')
                        ->description('Thông tin này sẽ được dùng để tạo PDF và gửi duyệt trước khi gửi cho ứng viên.')
                        ->columns(2)
                        ->schema([
                            Select::make('offer_letter_template_id')
                                ->label('Mẫu thư mời đính kèm (PDF)')
                                ->placeholder('Không dùng mẫu - soạn toàn bộ trong nội dung bổ sung')
                                ->options(fn (): array => OfferLetterTemplate::query()
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->helperText('Mẫu này dùng để tạo PDF đính kèm email gửi ứng viên.')
                                ->columnSpanFull(),
                            TextInput::make('salary_offered')
                                ->label('Mức lương đề nghị')
                                ->numeric()
                                ->step(1000)
                                ->minValue(1)
                                ->rules(['integer', 'min:1'])
                                ->required()
                                ->suffix('VND'),
                            Placeholder::make('published_salary_range')
                                ->label('Khung lương tin tuyển dụng')
                                ->content(function (Application $record): string {
                                    $range = $record->job?->salary_range;
                                    $currency = is_array($range) && filled($range['currency'] ?? null)
                                        ? strtoupper((string) $range['currency'])
                                        : 'VND';
                                    $min = is_array($range) && isset($range['min']) && is_numeric($range['min'])
                                        ? number_format((float) $range['min'], 0, ',', '.').' '.$currency
                                        : null;
                                    $max = is_array($range) && isset($range['max']) && is_numeric($range['max'])
                                        ? number_format((float) $range['max'], 0, ',', '.').' '.$currency
                                        : null;

                                    return $min && $max ? $min.' - '.$max : ($min ? 'Từ '.$min : ($max ? 'Đến '.$max : 'Thỏa thuận'));
                                })
                                ->helperText('Dùng để đối chiếu nội bộ; thư mời chỉ hiển thị mức đề nghị cuối cùng.'),
                            Textarea::make('salary_adjustment_reason')
                                ->label('Lý do điều chỉnh lương')
                                ->helperText('Bắt buộc khi mức đề nghị nằm ngoài khung lương đã công khai.')
                                ->maxLength(1000)
                                ->rows(3)
                                ->columnSpanFull(),
                            Select::make('probation_months')
                                ->options([
                                    0 => 'Không thử việc',
                                    1 => '1 tháng',
                                    2 => '2 tháng',
                                    3 => '3 tháng',
                                    4 => '4 tháng',
                                    5 => '5 tháng',
                                    6 => '6 tháng',
                                ])
                                ->native(false)
                                ->label('Thời gian thử việc')
                                ->default(2)
                                ->required()
                                ->helperText('Chọn theo chính sách áp dụng cho vị trí này.'),
                            Select::make('start_date_preset')
                                ->label('Gợi ý ngày nhận việc')
                                ->placeholder('Không dùng chọn nhanh')
                                ->options([
                                    '7_days' => 'Sau 7 ngày',
                                    '14_days' => 'Sau 14 ngày',
                                    'next_month' => 'Đầu tháng sau',
                                ])
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
                                    $date = match ($state) {
                                        '7_days' => now($timezone)->addDays(7),
                                        '14_days' => now($timezone)->addDays(14),
                                        'next_month' => now($timezone)->addMonthNoOverflow()->startOfMonth(),
                                        default => null,
                                    };

                                    if ($date) {
                                        $set('start_date', $date->toDateString());
                                    }
                                }),
                            DatePicker::make('start_date')
                                ->label('Ngày nhận việc dự kiến')
                                ->helperText('Đây là ngày sẽ lưu vào đề nghị. Có thể chọn nhanh bên cạnh hoặc chọn trực tiếp tại đây.')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                                ->minDate(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->startOfDay())
                                ->required(),
                            Select::make('expires_at_preset')
                                ->label('Gợi ý hạn phản hồi')
                                ->placeholder('Không dùng chọn nhanh')
                                ->options([
                                    '24_hours' => '24 giờ',
                                    '3_days' => '3 ngày',
                                    '5_days' => '5 ngày',
                                    '7_days' => '7 ngày',
                                ])
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
                                    $dateTime = match ($state) {
                                        '24_hours' => now($timezone)->addDay(),
                                        '3_days' => now($timezone)->addDays(3),
                                        '5_days' => now($timezone)->addDays(5),
                                        '7_days' => now($timezone)->addDays(7),
                                        default => null,
                                    };

                                    if ($dateTime) {
                                        $set('expires_at', $dateTime);
                                    }
                                }),
                            DateTimePicker::make('expires_at')
                                ->label('Hạn ứng viên phản hồi')
                                ->helperText('Đây là hạn phản hồi chính thức dùng cho link đồng ý/từ chối trong email.')
                                ->native(false)
                                ->seconds(false)
                                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                                ->default(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDays(3))
                                ->minDate(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))
                                ->required(),
                            Textarea::make('content')
                                ->label('Nội dung bổ sung')
                                ->helperText('Nội dung này sẽ được đưa vào email và cuối PDF. Bắt buộc nếu không chọn mẫu.')
                                ->rows(7)
                                ->required(fn (callable $get): bool => blank($get('offer_letter_template_id')))
                                ->columnSpanFull(),
                        ])
                        ->columnSpan(['default' => 'full', 'xl' => 8]),
                ]),
        ];
    }

    protected static function renderRejectedOfferNotice(Application $record): HtmlString
    {
        $offer = static::getLatestOffer($record);
        $notes = trim((string) ($offer?->approval_notes ?? ''));

        $reason = $notes !== ''
            ? e($notes)
            : 'Giám đốc chưa nhập lý do cụ thể. Vui lòng rà soát lại đề nghị trước khi gửi duyệt lại.';

        return new HtmlString(<<<HTML
            <div style="border:1px solid #f59e0b;border-radius:10px;background:#451a03;padding:14px 16px;color:#fffbeb;">
                <div style="font-size:14px;font-weight:700;">Đề nghị đã bị giám đốc từ chối</div>
                <div style="margin-top:6px;font-size:13px;line-height:1.5;color:#fde68a;">{$reason}</div>
            </div>
        HTML);
    }

    protected static function defaultInterviewCriteria(): array
    {
        return [
            ['name' => 'Kinh nghiệm phù hợp vị trí', 'score' => null, 'note' => null],
            ['name' => 'Kỹ năng chuyên môn', 'score' => null, 'note' => null],
            ['name' => 'Tư duy giải quyết vấn đề', 'score' => null, 'note' => null],
            ['name' => 'Kỹ năng giao tiếp', 'score' => null, 'note' => null],
            ['name' => 'Thái độ và mức độ phù hợp văn hóa', 'score' => null, 'note' => null],
        ];
    }

    protected static function interviewScoreOptions(): array
    {
        return collect(range(0, 10))
            ->mapWithKeys(fn (int $score): array => [$score => (string) $score])
            ->all();
    }

    protected static function interviewConclusionLabel(?string $conclusion): string
    {
        return match ($conclusion) {
            'pass' => 'Đạt phỏng vấn',
            'fail' => 'Không đạt phỏng vấn',
            'hold' => 'Cân nhắc thêm',
            default => 'Chưa kết luận',
        };
    }

    protected static function validateInterviewCriteria(array $criteria): array
    {
        if ($criteria === []) {
            throw ValidationException::withMessages([
                'criteria' => 'Vui lòng nhập ít nhất một tiêu chí chấm điểm.',
            ]);
        }

        return collect($criteria)
            ->values()
            ->map(function ($row, int $index): array {
                $position = $index + 1;

                if (! is_array($row)) {
                    throw ValidationException::withMessages([
                        'criteria' => "Tiêu chí #{$position} không hợp lệ.",
                    ]);
                }

                $name = trim((string) ($row['name'] ?? ''));
                $score = $row['score'] ?? null;

                if ($name === '') {
                    throw ValidationException::withMessages([
                        'criteria' => "Vui lòng nhập tên cho tiêu chí #{$position}.",
                    ]);
                }

                if ($score === null || $score === '' || ! is_numeric($score)) {
                    throw ValidationException::withMessages([
                        'criteria' => "Vui lòng nhập điểm hợp lệ cho tiêu chí #{$position}.",
                    ]);
                }

                $score = (float) $score;

                if ($score < 0 || $score > 10) {
                    throw ValidationException::withMessages([
                        'criteria' => "Điểm của tiêu chí #{$position} phải nằm trong khoảng 0-10.",
                    ]);
                }

                return [
                    'name' => $name,
                    'score' => $score,
                    'note' => $row['note'] ?? null,
                ];
            })
            ->all();
    }

    protected static function hasInvalidInterviewScore(array $criteria): bool
    {
        foreach ($criteria as $row) {
            if (! is_array($row)) {
                return true;
            }

            $score = $row['score'] ?? null;

            if ($score === null || $score === '') {
                continue;
            }

            if (! is_numeric($score)) {
                return true;
            }

            $score = (float) $score;

            if ($score < 0 || $score > 10) {
                return true;
            }
        }

        return false;
    }

    protected static function validateInterviewConclusion(?string $conclusion): string
    {
        if (! in_array($conclusion, ['pass', 'hold', 'fail'], true)) {
            throw ValidationException::withMessages([
                'conclusion' => 'Vui lòng chọn kết luận phỏng vấn hợp lệ.',
            ]);
        }

        return $conclusion;
    }

    protected static function calculateInterviewAverage(array $criteria): ?float
    {
        $scores = collect($criteria)
            ->map(fn ($row) => is_array($row) ? ($row['score'] ?? null) : null)
            ->filter(fn ($score) => $score !== null && $score !== '')
            ->map(fn ($score) => (float) $score);

        return $scores->count() > 0 ? round($scores->avg(), 2) : null;
    }

    protected static function recommendedInterviewConclusion(?float $average): ?string
    {
        if ($average === null) {
            return null;
        }

        if ($average >= 7) {
            return 'pass';
        }

        if ($average >= 5) {
            return 'hold';
        }

        return 'fail';
    }

    protected static function isInterviewConclusionOverride(?string $conclusion, ?string $recommendedConclusion): bool
    {
        return filled($conclusion)
            && filled($recommendedConclusion)
            && $conclusion !== $recommendedConclusion;
    }

    protected static function buildInterviewEvaluationComment(
        string $conclusion,
        ?float $average,
        ?string $notes = null,
        ?string $recommendedConclusion = null,
        ?string $overrideReason = null,
    ): string
    {
        $scoreText = $average !== null ? ' Điểm trung bình: '.number_format($average, 2, ',', '.').'/10.' : '';
        $recommendationText = $recommendedConclusion !== null
            ? ' Khuyến nghị: '.static::interviewConclusionLabel($recommendedConclusion).'.'
            : '';
        $overrideText = static::isInterviewConclusionOverride($conclusion, $recommendedConclusion) && filled($overrideReason)
            ? ' Lý do quyết định khác khuyến nghị: '.trim((string) $overrideReason).'.'
            : '';
        $noteText = filled($notes) ? ' Nhận xét: '.trim((string) $notes) : '';

        return 'Đánh giá phỏng vấn: '.static::interviewConclusionLabel($conclusion).'.'.$scoreText.$recommendationText.$overrideText.$noteText;
    }

    protected static function makeSendOfferAction(string $name = 'send_offer'): Action
    {
        return Action::make($name)
            ->label(fn (Application $record): string => match(static::getLatestOffer($record)?->status) {
                'draft' => 'Gửi duyệt đề nghị',
                'awaiting_approval' => 'Gửi duyệt đề nghị',
                'rejected' => 'Gửi duyệt lại đề nghị',
                default => static::getLatestOffer($record)?->sent_at ? 'Gửi lại thư mời' : 'Gửi thư mời',
            })
            ->icon('heroicon-o-envelope')
            ->color(fn (Application $record): string => match(static::getLatestOffer($record)?->status) {
                'draft', 'awaiting_approval', 'rejected' => 'warning',
                default => 'primary',
            })
            ->requiresConfirmation()
            ->modalHeading(function (Application $record): string {
                return match(static::getLatestOffer($record)?->status) {
                    'draft' => 'Gửi đề nghị tuyển dụng cho giám đốc duyệt',
                    'awaiting_approval' => 'Gửi đề nghị tuyển dụng cho giám đốc duyệt',
                    'rejected' => 'Gửi lại đề nghị tuyển dụng cho giám đốc duyệt',
                    default => 'Gửi thư mời nhận việc cho ứng viên',
                };
            })
            ->modalDescription(function (Application $record): string {
                return match(static::getLatestOffer($record)?->status) {
                    'draft' => 'Đề nghị tuyển dụng sẽ chuyển từ bản nháp sang chờ giám đốc chi nhánh duyệt.',
                    'awaiting_approval' => 'Đề nghị tuyển dụng sẽ được gửi để giám đốc chi nhánh duyệt trước khi gửi cho ứng viên.',
                    'rejected' => 'Đề nghị tuyển dụng sau khi chỉnh sửa sẽ được gửi cho giám đốc chi nhánh duyệt lại.',
                    default => 'Thư mời nhận việc được gửi tới '.($record->snapshotCandidateEmail() ?: 'email ứng viên').'.',
                };
            })
            ->action(function (Application $record): void {
                $offer = static::getLatestOffer($record);
                $candidate = $record->candidate;
                $job = $record->job;

                if (! $offer || ! $record->snapshotCandidateEmail() || ! $candidate || ! $job) {
                    Notification::make()
                        ->warning()
                        ->title('Chưa thể gửi đề nghị')
                        ->body('Vui lòng tạo đề nghị tuyển dụng và kiểm tra email ứng viên trước khi gửi.')
                        ->send();

                    return;
                }

                if (in_array($offer->status, ['draft', 'rejected'], true)) {
                    static::sendOfferForApproval($record, $offer);

                    return;
                }

                Notification::make()
                    ->warning()
                    ->title('Đề nghị chưa thể gửi')
                    ->body('Đề nghị này không còn ở trạng thái cho phép gửi duyệt. Vui lòng tải lại hồ sơ để kiểm tra trạng thái mới nhất.')
                    ->send();
            })
            ->visible(fn (Application $record): bool => static::shouldPrioritizeSendOffer($record));
    }

    protected static function makeSendInterviewScheduleAction(string $name = 'send_interview_schedule'): Action
    {
        return Action::make($name)
            ->label(function (Application $record): string {
                $interview = $record->latestInterview ?? $record->interviews()->latest('id')->first();

                return $interview?->invite_sent_at ? 'Gửi cập nhật lịch' : 'Gửi lịch phỏng vấn';
            })
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading(function (Application $record): string {
                $interview = $record->latestInterview ?? $record->interviews()->latest('id')->first();

                return $interview?->invite_sent_at ? 'Gửi cập nhật lịch phỏng vấn' : 'Gửi lịch phỏng vấn';
            })
            ->modalDescription(function (Application $record): string {
                $interview = $record->latestInterview ?? $record->interviews()->latest('id')->first();
                $recipientCount = count(app(InterviewScheduleDeliveryService::class)->recipients($record->fresh(['job.branch', 'candidate'])));

                return ($interview?->invite_sent_at
                    ? 'Email cập nhật lịch sẽ được gửi lại cho ứng viên và người liên quan.'
                    : 'Email lịch phỏng vấn sẽ được gửi cho ứng viên và người liên quan.')
                    .' Số email dự kiến: '.$recipientCount.'.';
            })
            ->action(function (Application $record): void {
                static::sendInterviewSchedule($record);
            })
            ->visible(fn (Application $record): bool => static::shouldPrioritizeSendInterviewSchedule($record));
    }

    protected static function makeSecondaryPipelineAction(): Action
    {
        return static::makePipelineAction('secondary_pipeline')
            ->label(function (Application $record): string {
                if (static::shouldPrioritizeSendInterviewSchedule($record)) {
                    return 'Cập nhật lịch phỏng vấn';
                }

                if (static::shouldPrioritizeSendOffer($record)) {
                    return 'Sửa đề nghị tuyển dụng';
                }

                return static::getPipelineActionLabel($record) ?? 'Cập nhật hồ sơ';
            })
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->visible(fn (Application $record): bool => static::shouldShowSecondaryPipelineAction($record));
    }

    protected static function makePipelineAction(string $name = 'pipeline'): Action
    {
        return Action::make($name)
            ->label(fn (Application $record): string => static::getPipelineActionLabel($record) ?? 'Xử lý')
            ->icon(fn (Application $record): string => static::getPipelineActionIcon($record))
            ->color(fn (Application $record): string => static::getPipelineActionColor($record))
            ->modalWidth(function (Application $record): string {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return '7xl';
                }

                if (static::canRecordPreScreening($record)) {
                    return '4xl';
                }

                return static::canManageInterview($record) ? '6xl' : '4xl';
            })
            ->modalHeading(function (Application $record): string {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return 'Sàng lọc CV';
                }

                if (static::canRecordPreScreening($record)) {
                    return 'Cập nhật sơ tuyển';
                }

                if (static::canManageInterview($record)) {
                    return $record->interviews()->exists() ? 'Điều chỉnh lịch phỏng vấn' : 'Tạo lịch phỏng vấn';
                }

                if (static::canManageOffer($record)) {
                    return $record->latestOffer ? 'Chỉnh sửa đề nghị tuyển dụng' : 'Tạo đề nghị tuyển dụng';
                }

                return 'Xử lý hồ sơ';
            })
            ->modalDescription(fn (Application $record): string => 'Hồ sơ #'.$record->id.' - '.$record->snapshotCandidateName())
            ->modalSubmitActionLabel(function (Application $record): string {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return 'Lưu kết quả sàng lọc';
                }

                if (static::canRecordPreScreening($record)) {
                    return 'Lưu kết quả sơ tuyển';
                }

                if (static::canManageInterview($record)) {
                    return static::hasInterviewStatus($record) ? 'Lưu thay đổi lịch' : 'Lưu lịch phỏng vấn';
                }

                if (static::canManageOffer($record)) {
                    return 'Lưu đề nghị tuyển dụng';
                }

                return 'Xác nhận';
            })
            ->modalCancelActionLabel('Hủy')
            ->fillForm(function (Application $record): array {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return [
                        'screening_decision' => null,
                    ];
                }

                if (static::canRecordPreScreening($record)) {
                    return [
                        'pre_screening_channel' => null,
                        'pre_screening_channel_detail' => null,
                        'pre_screening_contacted_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')),
                        'pre_screening_outcome' => null,
                        'pre_screening_follow_up_at' => null,
                        'pre_screening_note' => null,
                        'pre_screening_rejection_reason_code' => null,
                        'pre_screening_rejection_reason' => null,
                    ];
                }

                if (static::canManageInterview($record)) {
                    return static::getInterviewFormData($record);
                }

                $offer = $record->offers()->latest('id')->first();

                return [
                    'offer_letter_template_id' => $offer?->offer_letter_template_id,
                    'salary_offered' => $offer?->salary_offered,
                    'salary_adjustment_reason' => $offer?->salary_adjustment_reason,
                    'start_date_preset' => null,
                    'start_date' => $offer?->start_date,
                    'probation_months' => $offer?->probation_months ?? 2,
                    'expires_at_preset' => null,
                    'expires_at' => $offer?->expires_at ?? now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDays(3),
                    'content' => $offer?->content,
                ];
            })
            ->form(function (Application $record): array {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return static::getCvScreeningFormSchema();
                }

                if (static::canRecordPreScreening($record)) {
                    return static::getPreScreeningFormSchema();
                }

                if (static::canManageInterview($record)) {
                    return static::getInterviewSchedulingFormSchema();
                }

                return static::getOfferFormSchema();
            })
            ->action(function (Application $record, array $data): void {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    $decision = $data['screening_decision'] ?? null;
                    $note = trim((string) ($data['screening_note'] ?? ''));

                    if (! in_array($decision, ['pass', 'reject'], true)) {
                        throw ValidationException::withMessages([
                            'screening_decision' => 'Vui lòng chọn kết quả sàng lọc.',
                        ]);
                    }

                    if ($decision === 'reject') {
                        $reason = trim((string) ($data['rejected_reason'] ?? ''));

                        $record->forceFill([
                            'rejected_stage' => 'screening',
                            'rejected_reason' => $reason,
                        ])->save();

                        $comment = trim('Sàng lọc CV: Không đạt.'.($reason !== '' ? ' Lý do: '.$reason : ''));

                        if (! static::transitionApplication($record, StatusApplicationEnum::REJECTED, $comment)) {
                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title('Đã từ chối sau sàng lọc CV')
                            ->send();

                        return;
                    }

                    $record->forceFill([
                        'rejected_stage' => null,
                        'rejected_reason' => null,
                    ])->save();

                    $comment = trim('Sàng lọc CV: Đạt. '.$note);

                    if (! static::transitionApplication($record, StatusApplicationEnum::SCREENING, $comment)) {
                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Đã chuyển sang sơ tuyển')
                        ->send();

                    return;
                }

                if (static::canRecordPreScreening($record)) {
                    $outcome = (string) ($data['pre_screening_outcome'] ?? '');
                    $note = trim((string) ($data['pre_screening_note'] ?? ''));
                    $reasonCode = (string) ($data['pre_screening_rejection_reason_code'] ?? '');
                    $reason = trim((string) ($data['pre_screening_rejection_reason'] ?? ''));
                    $channel = (string) ($data['pre_screening_channel'] ?? '');
                    $channelDetail = trim((string) ($data['pre_screening_channel_detail'] ?? ''));
                    $contactedAt = Carbon::parse($data['pre_screening_contacted_at'], config('app.interview_timezone', 'Asia/Ho_Chi_Minh'));
                    $followUpAt = filled($data['pre_screening_follow_up_at'] ?? null)
                        ? Carbon::parse($data['pre_screening_follow_up_at'], config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                        : null;

                    if ($contactedAt->gt(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))) {
                        throw ValidationException::withMessages([
                            'pre_screening_contacted_at' => 'Chọn thời điểm liên hệ là hiện tại hoặc trước đó.',
                        ]);
                    }

                    if ($channel === 'other' && $channelDetail === '') {
                        throw ValidationException::withMessages([
                            'pre_screening_channel_detail' => 'Vui lòng ghi rõ hình thức liên hệ.',
                        ]);
                    }

                    if ($outcome === 'follow_up' && (! $followUpAt || $followUpAt->lte($contactedAt) || $followUpAt->lte(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))))) {
                        throw ValidationException::withMessages([
                            'pre_screening_follow_up_at' => 'Hẹn liên hệ lại phải sau lần liên hệ này và ở thời điểm tương lai.',
                        ]);
                    }

                    $preScreeningService = app(ApplicationPreScreeningService::class);
                    if ($outcome === 'rejected' && ! array_key_exists($reasonCode, $preScreeningService->rejectionReasonOptions())) {
                        throw ValidationException::withMessages([
                            'pre_screening_rejection_reason_code' => 'Vui lòng chọn lý do từ chối.',
                        ]);
                    }

                    if ($outcome === 'rejected' && $reasonCode === 'other' && $reason === '') {
                        throw ValidationException::withMessages([
                            'pre_screening_rejection_reason' => 'Vui lòng mô tả lý do từ chối.',
                        ]);
                    }

                    if ($outcome === 'rejected' && $reason === '') {
                        $reason = $preScreeningService->rejectionReasonLabel($reasonCode);
                    }

                    $preScreeningService->record(
                        $record,
                        Auth::user(),
                        $channel,
                        $contactedAt,
                        $outcome,
                        $outcome === 'follow_up' ? $followUpAt : null,
                        $note !== '' ? $note : null,
                        $reason !== '' ? $reason : null,
                        $channel === 'other' ? $channelDetail : null,
                        $outcome === 'rejected' ? $reasonCode : null,
                    );

                    if ($outcome === 'rejected') {
                        $record->forceFill([
                            'rejected_stage' => 'pre_screening',
                            'rejected_reason' => $reason,
                        ])->save();
                        static::transitionApplication($record, StatusApplicationEnum::REJECTED, 'Sơ tuyển: Từ chối hồ sơ. Lý do: '.$reason);
                    } else {
                        $label = $outcome === 'passed' ? 'Đạt sơ tuyển' : 'Hẹn liên hệ lại';
                        $comment = 'Sơ tuyển: '.$label.'. Ghi chú: '.$note;
                        if ($followUpAt) {
                            $comment .= ' Hẹn lại: '.$followUpAt->format('H:i, d/m/Y').'.';
                        }
                        $record->recordStatusHistory(StatusApplicationEnum::SCREENING->value, StatusApplicationEnum::SCREENING->value, $comment);
                    }

                    Notification::make()
                        ->success()
                        ->title($outcome === 'rejected' ? 'Đã từ chối hồ sơ' : 'Đã ghi nhận sơ tuyển')
                        ->send();

                    return;
                }

                if (static::canManageInterview($record)) {
                    $existingInterview = $record->interviews()->latest('id')->first();
                    $roundNumber = (int) ($existingInterview?->round_number ?: 1);
                    $scheduledAt = static::validateInterviewSchedule($data, $existingInterview);

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
                        'scorecard_template_id' => $data['scorecard_template_id'],
                        'scorecard_template_snapshot' => app(InterviewScorecardTemplateService::class)
                            ->snapshot((int) $data['scorecard_template_id']),
                        'round_name' => $data['round_name'] ?? ('Phỏng vấn vòng '.$roundNumber),
                        'duration_minutes' => (int) ($data['duration_minutes'] ?? 60),
                        'scheduled_at' => $scheduledAt,
                        'type' => $data['type'],
                        'meeting_link' => $data['type'] === 'online' ? ($data['meeting_link'] ?? null) : null,
                        'workplace_id' => $data['type'] === 'offline' ? ($data['workplace_id'] ?? null) : null,
                        'notes' => $data['notes'] ?? null,
                    ]);
                    $interview->save();

                    $interview->loadMissing(['application.job.branch', 'application.candidate', 'interviewer', 'workplace']);
                    app(InterviewCalendarService::class)->store($interview);

                    if ($status === StatusApplicationEnum::SCREENING
                        && ! static::transitionApplication($record, StatusApplicationEnum::INTERVIEW_SCHEDULED, static::buildInterviewScheduleComment($interview, false))
                    ) {
                        return;
                    }

                    if ($status !== StatusApplicationEnum::SCREENING) {
                        $record->recordStatusHistory(
                            $status?->value,
                            $status?->value ?? StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
                            static::buildInterviewScheduleComment($interview, true),
                        );
                    }

                    $notification = Notification::make()->title($existingInterview ? 'Đã cập nhật lịch phỏng vấn' : 'Đã tạo lịch phỏng vấn');

                    $notification
                        ->success()
                        ->body('Lịch phỏng vấn đã được lưu. Bước tiếp theo: bấm "Gửi lịch phỏng vấn" để gửi email và file lịch cho ứng viên/người liên quan.');

                    $notification->send();

                    return;
                }

                $existingOffer = $record->offers()->latest('id')->first();
                $offer = app(OfferWorkflowService::class)->saveDraft($record, $data, Auth::user());

                if (! static::transitionApplication($record, StatusApplicationEnum::OFFER, 'Đã tạo đề nghị tuyển dụng cho ứng viên.')) {
                    return;
                }

                $pdfHint = filled($offer->pdf_path)
                    ? ' PDF đã tạo - có thể tải từ cột thao tác hoặc file đính kèm khi gửi email.'
                    : '';

                Notification::make()
                    ->success()
                    ->title($existingOffer ? 'Đã lưu nháp đề nghị tuyển dụng' : 'Đã tạo nháp đề nghị tuyển dụng')
                    ->body('Đề nghị tuyển dụng đã được lưu nháp.'.$pdfHint.' Bước tiếp theo: bấm "Gửi duyệt đề nghị" để chuyển cho giám đốc chi nhánh.')
                    ->send();
            })
            ->visible(fn (Application $record): bool => static::shouldShowPrimaryPipelineAction($record))
            ->disabled(fn (Application $record): bool => static::getPipelineActionLabel($record) === null);
    }

    protected static function sendOfferForApproval(Application $record, Offer $offer): void
    {
        try {
            $result = app(OfferWorkflowService::class)->submitForApproval($record, Auth::user());
            $actionText = $result['resubmitted'] ? 'gửi lại' : 'gửi';
            $notification = Notification::make()
                ->title('Đã '.$actionText.' đề nghị tuyển dụng');

            if ($result['sent'] === 0) {
                $notification
                    ->warning()
                    ->body('Đề nghị đã chuyển sang chờ duyệt. Email thông báo chưa thể đưa vào hàng đợi gửi; giám đốc vẫn có thể xem trong màn Duyệt đề nghị.');
            } elseif ($result['failed'] > 0) {
                $notification
                    ->success()
                    ->body('Đề nghị đã chuyển sang chờ giám đốc duyệt. Một số email chưa thể đưa vào hàng đợi gửi.');
            } else {
                $notification
                    ->success()
                    ->body('Đề nghị đã chuyển sang chờ duyệt. Email thông báo sẽ được gửi trong giây lát.');
            }

            $notification->send();
        } catch (ValidationException $exception) {
            Notification::make()
                ->warning()
                ->title('Chưa thể gửi đề nghị')
                ->body((string) collect($exception->errors())->flatten()->first())
                ->send();
        } catch (\Throwable $exception) {
            Log::warning('Failed to prepare offer for approval.', [
                'application_id' => $record->id,
                'offer_id' => $offer->id,
                'error' => $exception->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Gửi đề nghị thất bại')
                ->body('Có lỗi khi chuyển đề nghị sang chờ duyệt. Vui lòng kiểm tra lại.')
                ->send();
        }
    }

    protected static function sendInterviewSchedule(Application $record): void
    {
        $result = app(InterviewScheduleDeliveryService::class)->deliver($record);

        if (! $result['has_interview']) {
            Notification::make()
                ->warning()
                ->title('Chưa có lịch phỏng vấn')
                ->body('Vui lòng tạo lịch phỏng vấn trước khi gửi email.')
                ->send();

            return;
        }
        $notification = Notification::make()->title($result['is_update'] ? 'Đã gửi cập nhật lịch phỏng vấn' : 'Đã gửi lịch phỏng vấn');

        if (! $result['candidate_sent']) {
            $notification->warning()->body('Chưa thể đưa lịch vào hàng đợi gửi. Lịch vẫn ở trạng thái chưa gửi để có thể kiểm tra và gửi lại.');
        } elseif ($result['failed'] > 0) {
            $notification->warning()->body($result['is_update']
                ? "Đã đưa cập nhật lịch vào hàng đợi gửi, nhưng một số email chưa thể xử lý."
                : "Đã đưa lịch vào hàng đợi gửi, nhưng một số email chưa thể xử lý.");
        } elseif ($result['sent'] === 0) {
            $notification->warning()->body('Không tìm thấy email ứng viên hoặc người liên quan để gửi lịch phỏng vấn.');
        } else {
            $notification->success()->body($result['is_update']
                ? 'Email cập nhật lịch kèm file lịch đang được gửi.'
                : 'Email lịch phỏng vấn kèm file lịch đang được gửi.');
        }

        $notification->send();
    }

}
