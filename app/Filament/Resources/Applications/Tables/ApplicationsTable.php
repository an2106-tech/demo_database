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
use App\Services\ApplicationAiAnalysisService;
use App\Services\ApplicationPipelineService;
use App\Services\OfferApprovalService;
use App\Services\OfferPdfService;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
            ->poll('10s')
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
                    ->sortable(),
                TextColumn::make('job.title')
                    ->label('Vị trí')
                    ->description(fn (Application $record): ?string => static::jobContextDescription($record))
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
                    ->label('Cách nộp')
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
                    ->label('Trạng thái tuyển dụng')
                    ->state(fn (Application $record): string => $record->status instanceof StatusApplicationEnum
                        ? $record->status->getPipelineStageLabel()
                        : (StatusApplicationEnum::tryFrom((string) $record->status)?->getPipelineStageLabel() ?? '-'))
                    ->description(function (Application $record): ?string {
                        $status = $record->status instanceof StatusApplicationEnum
                            ? $record->status
                            : StatusApplicationEnum::tryFrom((string) $record->status);

                        if (! $status || $status->getLabel() === $status->getPipelineStageLabel()) {
                            return null;
                        }

                        return (string) $status->getLabel();
                    })
                    ->color(fn (Application $record): string => $record->status instanceof StatusApplicationEnum
                        ? $record->status->getPipelineStageColor()
                        : (StatusApplicationEnum::tryFrom((string) $record->status)?->getPipelineStageColor() ?? 'gray'))
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
                SelectFilter::make('pipeline_stage')
                    ->label('Giai đoạn tuyển dụng')
                    ->options(StatusApplicationEnum::pipelineStageOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $stageKey = $data['value'] ?? null;

                        if (blank($stageKey)) {
                            return $query;
                        }

                        $statusValues = StatusApplicationEnum::statusValuesForPipelineStage((string) $stageKey);

                        return $statusValues === [] ? $query : $query->whereIn('status', $statusValues);
                    }),
                SelectFilter::make('status')
                    ->label('Trạng thái xử lý')
                    ->options($statusOptions)
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('status', $data['value']) : $query;
                    }),
                SelectFilter::make('job_id')
                    ->label('Vị trí')
                    ->options(fn () => static::getJobFilterOptions())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('job_id', $data['value']) : $query;
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
                SelectFilter::make('cv_state')
                    ->label('Tình trạng CV')
                    ->options([
                        'has_cv' => 'Đã có CV',
                        'missing_cv' => 'Chưa có CV',
                    ])
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
                    ->query(function (Builder $query, array $data): Builder {
                        return filled($data['value'] ?? null) ? $query->where('source', $data['value']) : $query;
                    }),
                TrashedFilter::make()->label('Bản ghi đã xóa'),
            ])
            ->filtersFormColumns(3)
            ->recordActions([
                static::makePipelineAction(),
                    Action::make('evaluate_interview')
                        ->label('Chấm phỏng vấn')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('info')
                        ->modalWidth('6xl')
                        ->modalHeading('Đánh giá phỏng vấn')
                        ->modalDescription(fn (Application $record): string => 'Hồ sơ #'.$record->id.' - '.$record->snapshotCandidateName())
                        ->modalSubmitActionLabel('Lưu đánh giá phỏng vấn')
                        ->fillForm(function (Application $record): array {
                            $interview = $record->interviews()->latest('id')->first();
                            $scorecard = $interview
                                ? static::getCurrentEvaluatorScorecard($record, $interview)
                                : null;

                            $defaultTemplate = ScorecardTemplate::query()
                                ->where('is_default', true)
                                ->latest('id')
                                ->first();

                            $criteria = $scorecard?->criteria;
                            if (! is_array($criteria) || $criteria === []) {
                                $criteria = $defaultTemplate?->criteria;
                            }

                            if (! is_array($criteria) || $criteria === []) {
                                $criteria = static::defaultInterviewCriteria();
                            }

                            return [
                                'interview_id' => $interview?->id,
                                'template_id' => $scorecard?->template_id ?? $defaultTemplate?->id,
                                'criteria' => $criteria,
                                'average_score' => $scorecard?->average_score,
                                'recommended_conclusion' => $scorecard?->recommended_conclusion,
                                'conclusion' => $scorecard?->conclusion ?? ($interview?->result !== 'pending' ? $interview?->result : null),
                                'notes' => $scorecard?->notes,
                                'override_reason' => $scorecard?->override_reason,
                                'rejected_reason' => $record->rejected_reason,
                            ];
                        })
                        ->form(fn (): array => static::getInterviewEvaluationFormSchema())
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

                            $criteria = static::validateInterviewCriteria($data['criteria'] ?? []);
                            $average = static::calculateInterviewAverage($criteria);
                            $recommendedConclusion = static::recommendedInterviewConclusion($average);
                            $conclusion = static::validateInterviewConclusion($data['conclusion'] ?? null);
                            $overrideReason = trim((string) ($data['override_reason'] ?? ''));

                            if (static::isInterviewConclusionOverride($conclusion, $recommendedConclusion)
                                && $overrideReason === ''
                            ) {
                                throw ValidationException::withMessages([
                                    'override_reason' => 'Vui lòng nhập lý do khi kết luận cuối khác khuyến nghị từ điểm số.',
                                ]);
                            }

                            if ($conclusion === 'fail' && blank($data['rejected_reason'] ?? null)) {
                                throw ValidationException::withMessages([
                                    'rejected_reason' => 'Vui lòng nhập lý do từ chối khi kết luận không đạt.',
                                ]);
                            }

                            $scorecard = Scorecard::withTrashed()->firstOrNew([
                                'interview_id' => $interview->id,
                                'evaluator_id' => (int) Auth::id(),
                            ]);

                            if ($scorecard->trashed()) {
                                $scorecard->restore();
                            }

                            $scorecard->fill([
                                'application_id' => $record->id,
                                'interview_id' => $interview->id,
                                'template_id' => $data['template_id'] ?? null,
                                'evaluator_id' => (int) Auth::id(),
                                'criteria' => $criteria,
                                'average_score' => $average,
                                'recommended_conclusion' => $recommendedConclusion,
                                'notes' => $data['notes'] ?? null,
                                'override_reason' => static::isInterviewConclusionOverride($conclusion, $recommendedConclusion)
                                    ? $overrideReason
                                    : null,
                                'conclusion' => $conclusion,
                            ]);
                            $scorecard->save();

                            $interviewResult = $conclusion === 'hold' ? 'pending' : $conclusion;
                            $interview->forceFill(['result' => $interviewResult])->save();

                            $currentStatus = $record->status instanceof StatusApplicationEnum
                                ? $record->status
                                : StatusApplicationEnum::tryFrom((string) $record->status);

                            $evaluationComment = static::buildInterviewEvaluationComment(
                                $conclusion,
                                $average,
                                $data['notes'] ?? null,
                                $recommendedConclusion,
                                $overrideReason
                            );
                            $alreadyRecordedEvaluation = false;

                            if ($currentStatus === StatusApplicationEnum::INTERVIEW_SCHEDULED) {
                                $comment = $conclusion === 'hold'
                                    ? $evaluationComment
                                    : 'Đã ghi nhận đánh giá phỏng vấn trước khi chuyển bước tiếp theo.';

                                if (! static::transitionApplication($record, StatusApplicationEnum::INTERVIEW, $comment)) {
                                    return;
                                }

                                $record->refresh();
                                $alreadyRecordedEvaluation = $conclusion === 'hold';
                            }

                            if ($conclusion === 'pass') {
                                $record->forceFill([
                                    'rejected_reason' => null,
                                ])->save();
                                if (! static::transitionApplication($record, StatusApplicationEnum::OFFER, $evaluationComment)) {
                                    return;
                                }
                            } elseif ($conclusion === 'fail') {
                                $rejectedReason = trim((string) ($data['rejected_reason'] ?? $record->rejected_reason ?? ''));
                                $rejectedComment = $evaluationComment.($rejectedReason !== '' ? ' Lý do từ chối: '.$rejectedReason : '');

                                $record->forceFill([
                                    'rejected_reason' => $rejectedReason !== '' ? $rejectedReason : $record->rejected_reason,
                                ])->save();
                                if (! static::transitionApplication($record, StatusApplicationEnum::REJECTED, $rejectedComment)) {
                                    return;
                                }
                            } else {
                                if (! $alreadyRecordedEvaluation) {
                                    $record->recordStatusHistory(
                                        StatusApplicationEnum::INTERVIEW->value,
                                        StatusApplicationEnum::INTERVIEW->value,
                                        $evaluationComment
                                    );
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Đã lưu đánh giá phỏng vấn')
                                ->body($conclusion === 'pass'
                                    ? 'Ứng viên đạt - hồ sơ đã chuyển sang bước đề nghị tuyển dụng.'
                                    : ($conclusion === 'fail'
                                        ? 'Ứng viên không đạt — hồ sơ đã chuyển sang Từ chối.'
                                        : 'Đã lưu đánh giá — hồ sơ giữ ở Phỏng vấn.'))
                                ->send();
                        })
                        ->visible(fn (Application $record): bool => static::canEvaluateInterview($record)),
                ActionGroup::make([
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
                    static::makeSendInterviewScheduleAction(),
                    static::makeSendOfferAction(),
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
                        ->url(fn ($record): string => ApplicationResource::getUrl('edit', ['record' => $record])),
                    DeleteAction::make()
                        ->label('Xóa')
                        ->visible(fn (): bool => Auth::user()?->hasRole('super_admin') === true),
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
                    DeleteBulkAction::make()->label('Xóa đã chọn'),
                    ForceDeleteBulkAction::make()->label('Xóa vĩnh viễn'),
                    RestoreBulkAction::make()->label('Khôi phục'),
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
            'scheduled_at' => $interview?->scheduled_at,
            'round_name' => $interview?->round_name ?? 'Phỏng vấn vòng 1',
            'duration_minutes' => $interview?->duration_minutes ?? 60,
            'type' => $interview?->type ?? 'online',
            'meeting_link' => $interview?->meeting_link,
            'workplace_id' => $interview?->workplace_id,
            'interviewer_id' => $interview?->interviewer_id,
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

    protected static function canManageInterview(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status->value : $record->status;

        return in_array($status, [
            StatusApplicationEnum::SCREENING->value,
            StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
            StatusApplicationEnum::INTERVIEW->value,
        ], true);
    }

    protected static function hasInterviewStatus(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status->value : $record->status;

        return in_array($status, [
            StatusApplicationEnum::INTERVIEW_SCHEDULED->value,
            StatusApplicationEnum::INTERVIEW->value,
        ], true);
    }

    protected static function canEvaluateInterview(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum
            ? $record->status
            : StatusApplicationEnum::tryFrom((string) $record->status);

        if (! in_array($status, [StatusApplicationEnum::INTERVIEW_SCHEDULED, StatusApplicationEnum::INTERVIEW], true)) {
            return false;
        }

        $interview = $record->latestInterview ?? $record->interviews()->latest('id')->first();

        if (! $interview || $interview->result !== 'pending') {
            return false;
        }

        if ($status === StatusApplicationEnum::INTERVIEW) {
            return true;
        }

        return $interview->scheduled_at?->lte(now()) ?? false;
    }

    protected static function canSendInterviewSchedule(Application $record): bool
    {
        if (! static::canManageInterview($record)) {
            return false;
        }

        $interview = $record->latestInterview ?? $record->interviews()->latest('id')->first();

        return (bool) $interview && blank($interview->invite_sent_at);
    }

    protected static function canRejectApplication(Application $record): bool
    {
        $status = $record->status instanceof StatusApplicationEnum
            ? $record->status
            : StatusApplicationEnum::tryFrom((string) $record->status);

        return in_array($status, [
            StatusApplicationEnum::NEW,
            StatusApplicationEnum::SCREENING,
            StatusApplicationEnum::INTERVIEW_SCHEDULED,
            StatusApplicationEnum::INTERVIEW,
            StatusApplicationEnum::OFFER,
        ], true);
    }

    protected static function canReopenOfferResponse(Application $record): bool
    {
        if (Auth::user()?->hasRole('super_admin') !== true) {
            return false;
        }

        return in_array($record->latestOffer?->status, ['accepted', 'declined', 'expired'], true);
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

        if ($status === StatusApplicationEnum::NEW) {
            return 'Sàng lọc CV';
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

    protected static function getPipelineActionIcon(Application $record): string
    {
        $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

        if ($status === StatusApplicationEnum::NEW) {
            return 'heroicon-o-document-magnifying-glass';
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
        $status = $record->status instanceof StatusApplicationEnum ? $record->status->value : $record->status;

        return $status === StatusApplicationEnum::OFFER->value;
    }

    protected static function getLatestOffer(Application $record): ?Offer
    {
        return $record->latestOffer ?? $record->offers()->latest('id')->first();
    }

    protected static function canEditOffer(?Offer $offer): bool
    {
        return ! $offer || in_array($offer->status, ['draft', 'rejected'], true);
    }

    protected static function shouldCreateReplacementOffer(?Offer $offer): bool
    {
        return $offer && in_array($offer->status, ['declined', 'expired'], true);
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
                            DateTimePicker::make('scheduled_at')
                                ->label('Thời gian phỏng vấn')
                                ->native(false)
                                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                                ->seconds(false)
                                ->helperText('Thời gian theo múi giờ Việt Nam.')
                                ->minDate(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh')))
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
                                ->helperText('Chọn mẫu phù hợp vị trí; có thể bổ sung tiêu chí nếu cần.')
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
                                        ->required()
                                        ->maxLength(120),
                                    Select::make('score')
                                        ->label('Điểm')
                                        ->options(static::interviewScoreOptions())
                                        ->native(false)
                                        ->searchable(false)
                                        ->required(),
                                    Textarea::make('note')
                                        ->label('Nhận xét tiêu chí')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->minItems(1)
                                ->defaultItems(5)
                                ->columns(2)
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
                                ->columnSpanFull(),
                            Select::make('conclusion')
                                ->label('Kết luận phỏng vấn')
                                ->options([
                                    'pass' => 'Đạt - chuyển sang đề nghị tuyển dụng',
                                    'hold' => 'Cân nhắc thêm - giữ ở phỏng vấn',
                                    'fail' => 'Không đạt - từ chối',
                                ])
                                ->live()
                                ->required(),
                            Textarea::make('override_reason')
                                ->label('Lý do quyết định khác khuyến nghị')
                                ->helperText('Bắt buộc khi kết luận cuối khác khuyến nghị từ điểm trung bình.')
                                ->rows(3)
                                ->visible(function (callable $get): bool {
                                    $recommendedConclusion = static::recommendedInterviewConclusion(
                                        static::calculateInterviewAverage($get('criteria') ?? [])
                                    );

                                    return static::isInterviewConclusionOverride($get('conclusion'), $recommendedConclusion);
                                })
                                ->required(function (callable $get): bool {
                                    $recommendedConclusion = static::recommendedInterviewConclusion(
                                        static::calculateInterviewAverage($get('criteria') ?? [])
                                    );

                                    return static::isInterviewConclusionOverride($get('conclusion'), $recommendedConclusion);
                                })
                                ->columnSpanFull(),
                            Textarea::make('notes')
                                ->label('Nhận xét tổng quan')
                                ->helperText('Tóm tắt điểm mạnh, điểm cần cân nhắc và khuyến nghị của người phỏng vấn.')
                                ->rows(5)
                                ->columnSpanFull(),
                            Textarea::make('rejected_reason')
                                ->label('Lý do từ chối')
                                ->helperText('Bắt buộc khi kết luận không đạt. Nội dung này dùng làm căn cứ từ chối hồ sơ.')
                                ->rows(3)
                                ->visible(fn (callable $get): bool => $get('conclusion') === 'fail')
                                ->required(fn (callable $get): bool => $get('conclusion') === 'fail')
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
                                ->label('Mẫu đề nghị tuyển dụng')
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
                                ->helperText('Chọn mẫu để hệ thống sinh PDF đề nghị tuyển dụng chuyên nghiệp.')
                                ->columnSpanFull(),
                            TextInput::make('salary_offered')
                                ->label('Mức lương đề nghị')
                                ->numeric()
                                ->minValue(1)
                                ->rules(['numeric', 'min:1'])
                                ->required()
                                ->suffix('VND'),
                            TextInput::make('probation_months')
                                ->label('Thời gian thử việc')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(6)
                                ->rules(['integer', 'min:0', 'max:6'])
                                ->default(2)
                                ->required()
                                ->suffix('tháng'),
                            Select::make('start_date_preset')
                                ->label('Chọn nhanh ngày bắt đầu')
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
                                ->label('Ngày bắt đầu dự kiến')
                                ->helperText('Đây là ngày sẽ lưu vào đề nghị. Có thể chọn nhanh bên cạnh hoặc chọn trực tiếp tại đây.')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                                ->minDate(now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->startOfDay())
                                ->required(),
                            Select::make('expires_at_preset')
                                ->label('Chọn nhanh hạn phản hồi')
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
                                ->label('Hạn phản hồi')
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

    protected static function validateOfferData(array $data): array
    {
        $timezone = config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
        $salary = $data['salary_offered'] ?? null;
        $probationMonths = $data['probation_months'] ?? null;
        $startDate = $data['start_date'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        if (! is_numeric($salary) || (float) $salary <= 0) {
            throw ValidationException::withMessages([
                'salary_offered' => 'Mức lương đề nghị phải lớn hơn 0.',
            ]);
        }

        if (filter_var($probationMonths, FILTER_VALIDATE_INT) === false
            || (int) $probationMonths < 0
            || (int) $probationMonths > 6
        ) {
            throw ValidationException::withMessages([
                'probation_months' => 'Thời gian thử việc phải nằm trong khoảng 0-6 tháng.',
            ]);
        }

        try {
            $parsedStartDate = \Carbon\Carbon::parse($startDate, $timezone)->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'start_date' => 'Ngày bắt đầu dự kiến không hợp lệ.',
            ]);
        }

        if ($parsedStartDate->lt(now($timezone)->startOfDay())) {
            throw ValidationException::withMessages([
                'start_date' => 'Ngày bắt đầu dự kiến không được ở quá khứ.',
            ]);
        }

        try {
            $parsedExpiresAt = $expiresAt instanceof \Carbon\CarbonInterface
                ? $expiresAt->copy()->setTimezone($timezone)
                : \Carbon\Carbon::parse($expiresAt, $timezone);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'expires_at' => 'Hạn phản hồi đề nghị không hợp lệ.',
            ]);
        }

        if ($parsedExpiresAt->lte(now($timezone))) {
            throw ValidationException::withMessages([
                'expires_at' => 'Hạn phản hồi đề nghị phải ở tương lai.',
            ]);
        }

        if (blank($data['offer_letter_template_id'] ?? null) && blank($data['content'] ?? null)) {
            throw ValidationException::withMessages([
                'content' => 'Vui lòng chọn mẫu đề nghị hoặc nhập nội dung bổ sung.',
            ]);
        }

        $data['salary_offered'] = (float) $salary;
        $data['probation_months'] = (int) $probationMonths;
        $data['start_date'] = $parsedStartDate->toDateString();
        $data['expires_at'] = $parsedExpiresAt;

        return $data;
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

    protected static function makeSendOfferAction(): Action
    {
        return Action::make('send_offer')
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

                static::sendOfferToCandidate($record, $offer, $candidate, $job);
            })
            ->visible(fn (Application $record): bool => static::canSendOffer($record));
    }

    protected static function makeSendInterviewScheduleAction(): Action
    {
        return Action::make('send_interview_schedule')
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
                $recipientCount = count(static::getInterviewRecipients($record->fresh(['job.branch', 'candidate'])));

                return ($interview?->invite_sent_at
                    ? 'Email cập nhật lịch sẽ được gửi lại cho ứng viên và người liên quan.'
                    : 'Email lịch phỏng vấn sẽ được gửi cho ứng viên và người liên quan.')
                    .' Số email dự kiến: '.$recipientCount.'.';
            })
            ->action(function (Application $record): void {
                static::sendInterviewSchedule($record);
            })
            ->visible(fn (Application $record): bool => static::canSendInterviewSchedule($record));
    }

    protected static function makePipelineAction(): Action
    {
        return Action::make('pipeline')
            ->label(fn (Application $record): string => static::getPipelineActionLabel($record) ?? 'Xử lý')
            ->icon(fn (Application $record): string => static::getPipelineActionIcon($record))
            ->color(fn (Application $record): string => static::getPipelineActionColor($record))
            ->modalWidth(function (Application $record): string {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return '7xl';
                }

                return static::canManageInterview($record) ? '6xl' : '4xl';
            })
            ->modalHeading(function (Application $record): string {
                $status = $record->status instanceof StatusApplicationEnum ? $record->status : StatusApplicationEnum::tryFrom((string) $record->status);

                if ($status === StatusApplicationEnum::NEW) {
                    return 'Sàng lọc CV';
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

                if (static::canManageInterview($record)) {
                    return static::getInterviewFormData($record);
                }

                $offer = $record->offers()->latest('id')->first();

                return [
                    'offer_letter_template_id' => $offer?->offer_letter_template_id,
                    'salary_offered' => $offer?->salary_offered,
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

                    $comment = trim('Sàng lọc CV: Đạt sơ tuyển. '.$note);

                    if (! static::transitionApplication($record, StatusApplicationEnum::SCREENING, $comment)) {
                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Đã chuyển sang sơ tuyển')
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

                    if ($existingInterview) {
                        $interview->forceFill(['invite_sent_at' => null])->save();
                    }

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

                $data = static::validateOfferData($data);
                $existingOffer = $record->offers()->latest('id')->first();
                $shouldCreateReplacementOffer = static::shouldCreateReplacementOffer($existingOffer);

                if (! $shouldCreateReplacementOffer && ! static::canEditOffer($existingOffer)) {
                    throw ValidationException::withMessages([
                        'offer' => static::lockedOfferMessage($existingOffer),
                    ]);
                }

                $offer = $shouldCreateReplacementOffer || ! $existingOffer ? new Offer([
                    'application_id' => $record->id,
                    'status' => 'draft',
                ]) : $existingOffer;

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
                    'status' => 'draft',
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
            ->visible(fn (Application $record): bool => static::getPipelineActionLabel($record) !== null && ! static::canEvaluateInterview($record))
            ->disabled(fn (Application $record): bool => static::getPipelineActionLabel($record) === null);
    }

    protected static function sendOfferForApproval(Application $record, Offer $offer): void
    {
        try {
            $wasRequestedBefore = filled($offer->approval_requested_at);
            $directors = static::getBranchDirectorEmails($record);

            if (empty($directors)) {
                Notification::make()
                    ->warning()
                    ->title('Không có giám đốc chi nhánh')
                    ->body('Không tìm thấy giám đốc chi nhánh để gửi đề nghị tuyển dụng cho duyệt.')
                    ->send();

                return;
            }

            app(OfferPdfService::class)->refreshForOffer($offer);
            $offer->refresh();

            $offer->forceFill([
                'status' => 'awaiting_approval',
                'approval_requested_at' => now(),
            ])->save();
            $offer->refresh();

            app(RecruitmentInternalNotificationService::class)->notifyOfferSubmittedForApproval($offer);

            $sentCount = 0;
            $failedCount = 0;

            foreach ($directors as $email) {
                $director = User::where('email', $email)->where('is_active', true)->first();

                if (! $director) {
                    continue;
                }

                try {
                    Mail::to($email)->send(new OfferApprovalRequestMail($offer, $record, $record->job, $director));
                    $sentCount++;
                } catch (\Throwable $mailException) {
                    $failedCount++;

                    Log::warning('Failed to send offer approval request mail.', [
                        'application_id' => $record->id,
                        'offer_id' => $offer->id,
                        'recipient' => $email,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            }

            $actionText = $wasRequestedBefore ? 'gửi lại' : 'gửi';
            $notification = Notification::make()
                ->title('Đã '.$actionText.' đề nghị tuyển dụng');

            if ($sentCount === 0) {
                $notification
                    ->warning()
                    ->body('Đề nghị đã chuyển sang chờ duyệt. Email thông báo chưa gửi được, giám đốc vẫn có thể xem trong màn Duyệt đề nghị.');
            } elseif ($failedCount > 0) {
                $notification
                    ->success()
                    ->body('Đề nghị đã chuyển sang chờ giám đốc duyệt. Một số email thông báo chưa gửi được.');
            } else {
                $notification
                    ->success()
                    ->body('Đề nghị tuyển dụng đã được gửi cho giám đốc chi nhánh duyệt.');
            }

            $notification->send();
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
        $interview = $record->interviews()->latest('id')->first();

        if (! $interview) {
            Notification::make()
                ->warning()
                ->title('Chưa có lịch phỏng vấn')
                ->body('Vui lòng tạo lịch phỏng vấn trước khi gửi email.')
                ->send();

            return;
        }

        $interview->loadMissing(['application.job.branch', 'application.candidate', 'interviewer', 'workplace']);
        app(InterviewCalendarService::class)->store($interview);

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

            $record->recordStatusHistory(
                $record->status instanceof StatusApplicationEnum ? $record->status->value : (string) $record->status,
                $record->status instanceof StatusApplicationEnum ? $record->status->value : (string) $record->status,
                "Đã gửi lịch phỏng vấn tới {$sentCount} email."
            );
        }

        $notification = Notification::make()->title('Đã gửi lịch phỏng vấn');

        if ($failedCount > 0) {
            $notification->warning()->body("Gửi email thành công {$sentCount}, thất bại {$failedCount}. Vui lòng kiểm tra lại log/mail cấu hình.");
        } elseif ($sentCount === 0) {
            $notification->warning()->body('Không tìm thấy email ứng viên hoặc người liên quan để gửi lịch phỏng vấn.');
        } else {
            $notification->success()->body("Đã gửi {$sentCount} email kèm file lịch phỏng vấn.");
        }

        $notification->send();
    }

    protected static function sendOfferToCandidate(Application $record, Offer $offer, Candidate $candidate, RecruitmentJob $job): void
    {
        try {
            app(OfferPdfService::class)->refreshForOffer($offer);
            $offer->refresh();

            $offer->forceFill([
                'sent_at' => now(),
            ])->save();
            $offer->refresh();

            Mail::to($record->snapshotCandidateEmail())->send(new CandidateOfferMail($candidate, $record, $job, $offer));

            Notification::make()
                ->success()
                ->title('Đã gửi thư mời nhận việc')
                ->body('Thư mời nhận việc đã được gửi tới ứng viên.')
                ->send();
        } catch (\Throwable $exception) {
            Log::warning('Failed to send offer mail.', [
                'application_id' => $record->id,
                'offer_id' => $offer->id,
                'recipient' => $record->snapshotCandidateEmail(),
                'error' => $exception->getMessage(),
            ]);

            Notification::make()
                ->warning()
                ->title('Gửi thư mời thất bại')
                ->body('Đề nghị tuyển dụng đã được lưu nhưng chưa gửi được email. Vui lòng kiểm tra và gửi lại.')
                ->send();
        }
    }
}
