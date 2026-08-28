<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OfferResource;
use App\Models\Application;
use App\Models\ApplicationAiAnalysis;
use App\Models\Scorecard;
use App\Services\OfferApprovalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class EditOffer extends EditRecord
{
    protected static string $resource = OfferResource::class;

    protected static ?string $title = 'Duyệt đề nghị tuyển dụng';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Đề nghị tuyển dụng chờ duyệt')
                    ->compact()
                    ->schema([
                        Html::make(fn (): HtmlString => $this->renderOfferOverview())
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Grid::make(['default' => 1, 'xl' => 12])
                    ->schema([
                        Section::make('Thông tin đánh giá')
                            ->schema([
                                Html::make(fn (): HtmlString => $this->renderInterviewEvidence())
                                    ->columnSpanFull(),
                                Html::make(fn (): HtmlString => $this->renderCandidateSummary())
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(['default' => 'full', 'xl' => 4]),
                        Section::make('CV ứng tuyển')
                            ->description(fn (): string => $this->record->application?->submittedCvName() ?: 'Không có CV đính kèm')
                            ->headerActions([
                                Action::make('open_candidate_cv')
                                    ->label('Mở CV')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->color('gray')
                                    ->url(fn (): ?string => $this->record->application?->submittedCvUrl())
                                    ->openUrlInNewTab()
                                    ->visible(fn (): bool => filled($this->record->application?->submittedCvUrl())),
                            ])
                            ->schema([
                                Html::make(fn (): HtmlString => $this->renderCvPanel())
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(['default' => 'full', 'xl' => 8]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing([
            'application.candidate',
            'application.latestScreeningAiAnalysis',
            'application.branch',
            'application.cvAttachment',
            'application.assignedHr',
            'application.job.branch',
            'application.job.department',
            'application.job.workplace',
            'application.job.creator',
            'letterTemplate',
        ]);

        $application = $this->record->application;
        $job = $application?->job;

        $data['offer_code'] = $this->formatOfferCode((int) $this->record->id);
        $data['status_display'] = $this->formatOfferStatus((string) $this->record->status);
        $data['created_at_display'] = $this->formatDateTime($this->record->created_at);
        $data['approval_requested_at_display'] = $this->formatDateTime($this->record->approval_requested_at, 'Chưa gửi duyệt');
        $data['template_name'] = $this->record->letterTemplate?->name ?? 'Không dùng mẫu';
        $data['pdf_status'] = filled($this->record->pdf_path) ? 'Đã tạo PDF đề nghị' : 'Chưa có PDF';
        $data['request_owner_name'] = $application?->assignedHr?->name
            ?? $job?->creator?->name
            ?? 'Chưa xác định';

        $data['candidate_name'] = $application?->snapshotCandidateName() ?? '';
        $data['candidate_email'] = $application?->snapshotCandidateEmail() ?? '';
        $data['candidate_phone'] = $application?->snapshotCandidatePhone() ?? '';
        $data['job_title'] = $job?->title ?? '';
        $data['branch_name'] = $job?->branch?->name ?? $application?->branch?->name ?? '';
        $data['department_name'] = $job?->department?->name ?? '';
        $data['workplace_name'] = $job?->workplace?->name ?? '';
        $data['profile_title'] = $application?->snapshotProfileTitle() ?: 'Chưa có tiêu đề hồ sơ';
        $data['experience_years'] = filled($application?->snapshotCandidateExperienceYears())
            ? $application?->snapshotCandidateExperienceYears().' năm'
            : 'Chưa cập nhật';
        $data['cv_name'] = $application?->submittedCvName() ?: 'Chưa có CV';

        $data['salary_display'] = number_format((float) $this->record->salary_offered, 0, ',', '.').' VND';
        $data['start_date_display'] = $this->formatDate($this->record->start_date);
        $data['probation_display'] = ((int) $this->record->probation_months).' tháng';
        $data['expires_at_display'] = $this->formatDateTime($this->record->expires_at, 'Chưa đặt hạn phản hồi');
        $data['content_preview'] = filled($this->record->content)
            ? (string) $this->record->content
            : 'Không có nội dung bổ sung.';

        return $data;
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        $offer = $this->record;

        return [
            Action::make('download_pdf')
                ->label('Tải PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => filled($offer->pdf_path) && Storage::disk('local')->exists($offer->pdf_path))
                ->action(fn () => response()->download(
                    Storage::disk('local')->path($offer->pdf_path),
                    $this->formatOfferCode((int) $offer->id).'.pdf'
                )),

            Action::make('approve')
                ->label('Duyệt')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $offer->status === 'awaiting_approval')
                ->requiresConfirmation()
                ->modalHeading('Gửi thư mời nhận việc?')
                ->modalDescription(fn () => sprintf(
                    'Thư mời sẽ được gửi đến %s ngay sau khi xác nhận. Hạn phản hồi: %s.',
                    $this->record->application?->snapshotCandidateName() ?: 'ứng viên',
                    $this->formatDateTime($this->record->expires_at, 'chưa đặt')
                ))
                ->action(function () use ($offer) {
                    $user = Auth::user();

                    if (! $user) {
                        Notification::make()
                            ->danger()
                            ->title('Lỗi')
                            ->body('Không tìm thấy thông tin người dùng.')
                            ->send();

                        return;
                    }

                    $service = app(OfferApprovalService::class);

                    if ($service->approve($offer, $user)) {
                        Notification::make()
                            ->success()
                            ->title('Đã duyệt đề nghị tuyển dụng')
                            ->body('Thư mời nhận việc đang được gửi tới ứng viên.')
                            ->send();

                        $this->redirect(OfferResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Chưa thể duyệt đề nghị')
                            ->body($service->lastError() ?: 'Vui lòng kiểm tra lại thông tin đề nghị và thử lại.')
                            ->send();
                    }
                }),

            Action::make('reject')
                ->label('Yêu cầu chỉnh sửa')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $offer->status === 'awaiting_approval')
                ->requiresConfirmation()
                ->modalHeading('Yêu cầu chỉnh sửa đề nghị')
                ->modalDescription('Ghi rõ nội dung HR cần cập nhật trước khi gửi duyệt lại.')
                ->form([
                    Textarea::make('approval_notes')
                        ->label('Nội dung cần chỉnh sửa')
                        ->helperText('Tối thiểu 10 ký tự.')
                        ->rows(4)
                        ->minLength(10)
                        ->required(),
                ])
                ->action(function (array $data) use ($offer) {
                    $user = Auth::user();

                    if (! $user) {
                        Notification::make()
                            ->danger()
                            ->title('Lỗi')
                            ->body('Không tìm thấy thông tin người dùng.')
                            ->send();

                        return;
                    }

                    $service = app(OfferApprovalService::class);

                    if ($service->reject($offer, $user, trim((string) ($data['approval_notes'] ?? '')))) {
                        Notification::make()
                            ->warning()
                            ->title('Đã gửi lại cho HR')
                            ->body('HR sẽ xem ghi chú và cập nhật đề nghị.')
                            ->send();

                        $this->redirect(OfferResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Lỗi')
                            ->body('Có lỗi xảy ra.')
                            ->send();
                    }
                }),

            Action::make('back')
                ->label('Quay lại')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(OfferResource::getUrl('index')),
        ];
    }

    private function formatOfferCode(int $id): string
    {
        return 'OFF-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    private function formatOfferStatus(string $status): string
    {
        return match ($status) {
            'awaiting_approval' => 'Chờ giám đốc duyệt',
            'pending' => 'Đã duyệt, chờ ứng viên phản hồi',
            'accepted' => 'Ứng viên đã đồng ý',
            'rejected' => 'Cần chỉnh sửa',
            'declined' => 'Ứng viên đã từ chối',
            'draft' => 'Bản nháp',
            default => $status,
        };
    }

    private function renderOfferOverview(): HtmlString
    {
        $application = $this->record->application;
        $job = $application?->job;
        $candidateName = e($application?->snapshotCandidateName() ?: 'Ứng viên');
        $jobTitle = e($job?->title ?: 'Chưa xác định vị trí');
        $branchName = e($job?->branch?->name ?: $application?->branch?->name ?: 'Chưa xác định chi nhánh');
        $ownerName = e($application?->assignedHr?->name ?? $job?->creator?->name ?? 'Chưa xác định');
        $offerCode = e($this->formatOfferCode((int) $this->record->id));
        $submittedAt = e($this->formatDateTime($this->record->approval_requested_at, 'Chưa gửi duyệt'));
        $content = trim((string) $this->record->content);
        $contentPanel = $content !== ''
            ? '<details style="margin-top:14px;"><summary style="cursor:pointer;font-size:13px;font-weight:700;color:#475569;">Xem nội dung thư mời</summary><div style="margin-top:8px;border-top:1px solid #e5e7eb;padding-top:10px;font-size:13px;line-height:1.6;color:#374151;">'.nl2br(e($content)).'</div></details>'
            : '';
        $salaryRange = $job?->salary_range;
        $salaryMin = is_array($salaryRange) && isset($salaryRange['min']) && is_numeric($salaryRange['min'])
            ? (int) $salaryRange['min']
            : null;
        $salaryMax = is_array($salaryRange) && isset($salaryRange['max']) && is_numeric($salaryRange['max'])
            ? (int) $salaryRange['max']
            : null;
        $salaryCurrency = is_array($salaryRange) && filled($salaryRange['currency'] ?? null)
            ? strtoupper((string) $salaryRange['currency'])
            : 'VND';
        $outsidePublishedRange = $salaryCurrency === 'VND'
            && (($salaryMin !== null && $this->record->salary_offered < $salaryMin)
                || ($salaryMax !== null && $this->record->salary_offered > $salaryMax));
        $publishedSalary = match (true) {
            $salaryMin !== null && $salaryMax !== null => number_format($salaryMin, 0, ',', '.').' '.$salaryCurrency.' - '.number_format($salaryMax, 0, ',', '.').' '.$salaryCurrency,
            $salaryMin !== null => 'Từ '.number_format($salaryMin, 0, ',', '.').' '.$salaryCurrency,
            $salaryMax !== null => 'Đến '.number_format($salaryMax, 0, ',', '.').' '.$salaryCurrency,
            default => 'Thỏa thuận',
        };
        $adjustmentReason = trim((string) $this->record->salary_adjustment_reason);
        $salaryContextPanel = '<div style="border:1px solid '.($outsidePublishedRange ? '#fed7aa' : '#e5e7eb').';border-radius:10px;background:'.($outsidePublishedRange ? '#fff7ed' : '#f8fafc').';padding:10px 12px;font-size:13px;line-height:1.5;color:#475569;"><strong style="color:#334155;">Khung lương tin tuyển dụng:</strong> '.e($publishedSalary)
            .($outsidePublishedRange
                ? '<div style="margin-top:6px;"><strong style="color:#9a3412;">Lý do điều chỉnh:</strong> '.e($adjustmentReason !== '' ? $adjustmentReason : 'Chưa ghi nhận.').'</div>'
                : '')
            .'</div>';

        return new HtmlString(<<<HTML
            <div style="display:grid;gap:16px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:18px;">
                    <div style="min-width:0;">
                        <div style="font-size:18px;font-weight:800;color:#111827;">{$candidateName}</div>
                        <div style="margin-top:3px;font-size:14px;line-height:1.5;color:#475569;">{$jobTitle} · {$branchName}</div>
                    </div>
                    <div style="flex:0 0 auto;border-radius:999px;background:#fff7ed;padding:6px 10px;font-size:12px;font-weight:800;color:#c2410c;white-space:nowrap;">Chờ duyệt</div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;">
                    <div style="border-radius:10px;background:#f8fafc;padding:10px 12px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Mức lương đề xuất</div><div style="margin-top:4px;font-size:15px;font-weight:800;color:#111827;">{$this->formatMoney($this->record->salary_offered)}</div></div>
                    <div style="border-radius:10px;background:#f8fafc;padding:10px 12px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Ngày bắt đầu</div><div style="margin-top:4px;font-size:15px;font-weight:800;color:#111827;">{$this->formatDate($this->record->start_date)}</div></div>
                    <div style="border-radius:10px;background:#f8fafc;padding:10px 12px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Thử việc</div><div style="margin-top:4px;font-size:15px;font-weight:800;color:#111827;">{$this->record->probation_months} tháng</div></div>
                    <div style="border-radius:10px;background:#fff7ed;padding:10px 12px;"><div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#9a3412;">Hạn phản hồi</div><div style="margin-top:4px;font-size:15px;font-weight:800;color:#9a3412;">{$this->formatDateTime($this->record->expires_at, 'Chưa đặt')}</div></div>
                </div>

                {$salaryContextPanel}

                <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:12px;color:#64748b;">
                    <span>HR gửi đề nghị: <strong style="color:#334155;">{$ownerName}</strong></span><span>·</span><span>Gửi duyệt: {$submittedAt}</span><span>·</span><span>{$offerCode}</span>
                </div>
                {$contentPanel}
            </div>
        HTML);
    }

    private function renderInterviewEvidence(): HtmlString
    {
        return $this->record->application
            ? $this->renderDirectorScorecardSummary($this->record->application)
            : new HtmlString('<span style="font-size:14px;color:#6b7280;">Chưa có đánh giá phỏng vấn để đối chiếu.</span>');
    }

    private function renderCvPanel(): HtmlString
    {
        $application = $this->record->application;
        $url = $application?->submittedCvUrl();

        if (! $url) {
            return new HtmlString('<div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-500 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-gray-400">Chưa có file CV để đối chiếu.</div>');
        }

        $safeUrl = e($url);
        $isPdf = str_ends_with(strtolower((string) $application?->submittedCvName()), '.pdf')
            || str_contains(strtolower($safeUrl), '.pdf');

        $preview = $isPdf
            ? '<iframe src="'.$safeUrl.'#toolbar=1&navpanes=0&view=FitH" style="display:block;width:100%;height:620px;border:0;background:#f3f4f6;"></iframe>'
            : '<div style="height:220px;display:flex;align-items:center;justify-content:center;background:#f9fafb;padding:24px;text-align:center;font-size:14px;line-height:1.6;color:#6b7280;">Định dạng này không hỗ trợ xem trước trực tiếp. Vui lòng mở CV để đối chiếu.</div>';

        return new HtmlString($preview);
    }

    private function renderDirectorScorecardSummary(Application $application): HtmlString
    {
        $scorecards = $application->scorecards()
            ->with(['evaluator', 'interview', 'template'])
            ->whereNotNull('conclusion')
            ->latest('updated_at')
            ->get();

        if ($scorecards->isEmpty()) {
            return new HtmlString('<div style="border:1px dashed #d1d5db;border-radius:12px;padding:12px 14px;font-size:14px;color:#6b7280;">Chưa có đánh giá phỏng vấn.</div>');
        }

        $items = $scorecards->map(function (Scorecard $scorecard): string {
            $evaluator = e($scorecard->evaluator?->name ?? 'Người đánh giá');
            $roundName = e(
                $scorecard->interview?->round_name
                    ?: ($scorecard->interview?->round_number ? 'Vòng '.$scorecard->interview->round_number : 'Vòng phỏng vấn')
            );
            $average = $scorecard->average_score !== null
                ? e(number_format((float) $scorecard->average_score, 2, ',', '.').'/10')
                : '-';
            $recommendation = e($this->interviewConclusionLabel($scorecard->recommended_conclusion));
            $conclusion = e($this->interviewConclusionLabel($scorecard->conclusion));
            $notes = filled($scorecard->notes)
                ? '<div style="margin-top:10px;font-size:13px;line-height:1.55;color:#374151;"><span style="font-weight:700;color:#111827;">Nhận xét:</span> '.e($scorecard->notes).'</div>'
                : '';
            $criteria = $this->renderScorecardCriteria($scorecard);

            return <<<HTML
                <div style="display:grid;gap:6px;">
                    <div style="border:1px solid #e5e7eb;border-radius:12px;background:#ffffff;padding:12px 14px;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                            <div style="min-width:0;">
                                <div style="font-size:14px;font-weight:800;color:#111827;">{$evaluator}</div>
                                <div style="margin-top:2px;font-size:12px;color:#6b7280;">{$roundName}</div>
                            </div>
                            <div style="font-size:13px;font-weight:800;color:#111827;white-space:nowrap;">{$average}</div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:10px;">
                            <div style="border-radius:10px;background:#f9fafb;padding:9px 10px;">
                                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#6b7280;">Đề xuất người phỏng vấn</div>
                                <div style="margin-top:3px;font-size:13px;font-weight:800;color:#111827;">{$recommendation}</div>
                            </div>
                            <div style="border-radius:10px;background:#f9fafb;padding:9px 10px;">
                                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#6b7280;">Kết quả đánh giá</div>
                                <div style="margin-top:3px;font-size:13px;font-weight:800;color:#111827;">{$conclusion}</div>
                            </div>
                        </div>

                        {$notes}
                        {$criteria}
                    </div>
                </div>
            HTML;
        })->implode('');

        return new HtmlString('<div style="display:grid;gap:10px;">'.$items.'</div>');
    }

    private function renderScorecardCriteria(Scorecard $scorecard): string
    {
        $criteria = is_array($scorecard->criteria) ? $scorecard->criteria : [];

        $rows = collect($criteria)
            ->filter(fn ($criterion): bool => is_array($criterion))
            ->map(function (array $criterion, int $index): string {
                $name = e((string) ($criterion['name'] ?? 'Tiêu chí '.($index + 1)));
                $score = isset($criterion['score']) && $criterion['score'] !== ''
                    ? e(number_format((float) $criterion['score'], 1, ',', '.').'/10')
                    : '-';
                $note = filled($criterion['note'] ?? null)
                    ? e((string) $criterion['note'])
                    : '<span style="color:#9ca3af;">Chưa có nhận xét</span>';

                return <<<HTML
                    <div style="display:grid;grid-template-columns:minmax(0,1fr) 64px minmax(0,1.2fr);gap:10px;border-top:1px solid #f3f4f6;padding:8px 0;font-size:12px;line-height:1.45;">
                        <div style="font-weight:700;color:#111827;">{$name}</div>
                        <div style="font-weight:800;color:#111827;">{$score}</div>
                        <div style="color:#4b5563;">{$note}</div>
                    </div>
                HTML;
            })
            ->implode('');

        if ($rows === '') {
            return '';
        }

        return <<<HTML
            <details style="display:block;margin-top:12px;">
                <summary style="cursor:pointer;display:inline-flex;align-items:center;border-radius:8px;border:1px solid #e5e7eb;background:#ffffff;padding:6px 10px;font-size:13px;font-weight:700;color:#374151;list-style:none;">Xem chi tiết đánh giá</summary>
                <div style="margin-top:10px;border-top:1px solid #e5e7eb;padding-top:2px;">{$rows}</div>
            </details>
        HTML;
    }

    private function interviewConclusionLabel(?string $conclusion): string
    {
        return match ($conclusion) {
            'pass' => 'Đạt phỏng vấn',
            'hold' => 'Cần cân nhắc',
            'fail' => 'Không đạt',
            default => 'Chưa kết luận',
        };
    }

    private function renderCandidateSummary(): HtmlString
    {
        $application = $this->record->application;

        if (! $application) {
            return new HtmlString(
                '<div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;color:#6b7280;font-size:14px;background:#ffffff;">Chưa có hồ sơ ứng tuyển để tóm tắt.</div>'
            );
        }

        $analysis = $this->latestCompletedScreeningAnalysis($application);
        $score = $analysis?->score;
        $recommendation = $analysis?->recommendation;
        $resultJson = (array) ($analysis?->result_json ?? []);
        $directorSummary = trim((string) data_get($resultJson, 'director_brief.summary', ''));
        $summary = $directorSummary !== ''
            ? $directorSummary
            : trim((string) ($analysis?->summary ?? ''));
        $directorKeyPoints = (array) data_get($resultJson, 'director_brief.key_points', []);
        $directorRisks = (array) data_get($resultJson, 'director_brief.risks', []);
        $strengths = $this->renderInlineList($directorKeyPoints !== [] ? $directorKeyPoints : (array) ($analysis?->strengths ?? []), '#15803d');
        $gaps = $this->renderInlineList($directorRisks !== [] ? $directorRisks : (array) ($analysis?->gaps ?? []), '#ea580c');

        $scoreBadge = filled($score)
            ? '<span style="min-width:52px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid #e5e7eb;background:#f9fafb;padding:4px 10px;font-size:12px;font-weight:800;color:#111827;white-space:nowrap;">'.(int) $score.'/100</span>'
            : '';

        $recommendationBadge = filled($recommendation)
            ? '<span style="display:inline-flex;align-items:center;border-radius:999px;background:'.$this->recommendationSoftColor((string) $recommendation).';color:'.$this->recommendationColor((string) $recommendation).';border:1px solid '.$this->recommendationBorderColor((string) $recommendation).';padding:4px 10px;font-size:12px;font-weight:800;white-space:nowrap;">'.$this->recommendationLabel((string) $recommendation).'</span>'
            : '';

        $summaryHtml = $summary !== ''
            ? '<div style="font-size:13px;line-height:1.55;color:#374151;text-align:justify;">'.e(mb_strlen($summary) > 260 ? mb_substr($summary, 0, 260).'...' : $summary).'</div>'
            : '<div style="font-size:13px;line-height:1.55;color:#6b7280;text-align:justify;">Chưa có phân tích sàng lọc AI. Giám đốc có thể đối chiếu scorecard phỏng vấn và CV gốc.</div>';

        return new HtmlString(<<<HTML
            <div style="border:1px solid #e5e7eb;border-radius:12px;background:#ffffff;padding:13px 14px;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;">
                    <div style="font-size:12px;font-weight:800;text-transform:uppercase;color:#6b7280;">Tóm tắt mức độ phù hợp</div>
                    <div style="display:flex;align-items:center;gap:6px;">{$scoreBadge}{$recommendationBadge}</div>
                </div>
                <div style="margin-top:10px;min-width:0;">{$summaryHtml}</div>
                <details style="margin-top:12px;">
                    <summary style="cursor:pointer;display:inline-flex;align-items:center;border-radius:8px;border:1px solid #e5e7eb;background:#ffffff;padding:6px 10px;font-size:13px;font-weight:700;color:#374151;">Xem nhận định từ AI</summary>
                    <div style="margin-top:10px;border-top:1px solid #e5e7eb;padding-top:10px;">
                            <div style="display:grid;gap:10px;">
                                <div>
                                    <div style="margin-bottom:5px;font-size:12px;font-weight:800;color:#15803d;">Điểm phù hợp</div>
                                    {$strengths}
                                </div>
                                <div>
                                    <div style="margin-bottom:5px;font-size:12px;font-weight:800;color:#ea580c;">Điểm cần lưu ý</div>
                                    {$gaps}
                                </div>
                            </div>
                    </div>
                </details>
            </div>
        HTML);
    }
    private function renderInlineList(array $items, string $dotColor = '#f97316'): string
    {
        $items = array_values(array_filter($items, fn ($item): bool => filled($item)));

        if ($items === []) {
            return '<div class="text-sm leading-6 text-gray-500 dark:text-gray-400">Chưa có dữ liệu.</div>';
        }

        $html = '<div class="grid gap-1.5">';
        foreach (array_slice($items, 0, 3) as $item) {
            $html .= '<div class="relative pl-3 text-sm leading-6 text-gray-700 dark:text-gray-300">'
                .'<span style="background:'.$dotColor.';" class="absolute left-0 top-[.7em] h-1 w-1 rounded-full"></span>'
                .e((string) $item)
                .'</div>';
        }

        return $html.'</div>';
    }

    private function latestCompletedScreeningAnalysis(Application $application): ?ApplicationAiAnalysis
    {
        $loaded = $application->relationLoaded('latestScreeningAiAnalysis')
            ? $application->latestScreeningAiAnalysis
            : null;

        if ($loaded?->status === 'completed') {
            return $loaded;
        }

        return $application->aiAnalyses()
            ->where('analysis_type', 'screening')
            ->where('status', 'completed')
            ->latest('id')
            ->first();
    }

    private function recommendationLabel(string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => 'Phù hợp',
            'consider' => 'Cần cân nhắc',
            'reject' => 'Chưa phù hợp',
            default => 'AI hỗ trợ',
        };
    }

    private function recommendationColor(string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => '#16a34a',
            'consider' => '#d97706',
            'reject' => '#dc2626',
            default => '#64748b',
        };
    }

    private function recommendationSoftColor(string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => '#dcfce7',
            'consider' => '#fef3c7',
            'reject' => '#fee2e2',
            default => '#f1f5f9',
        };
    }

    private function recommendationBorderColor(string $recommendation): string
    {
        return match ($recommendation) {
            'pass' => '#bbf7d0',
            'consider' => '#fde68a',
            'reject' => '#fecaca',
            default => '#e2e8f0',
        };
    }

    private function formatDate(mixed $value, string $empty = '-'): string
    {
        if (blank($value)) {
            return $empty;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatMoney(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.').' VND';
    }

    private function formatDateTime(mixed $value, string $empty = '-'): string
    {
        if (blank($value)) {
            return $empty;
        }

        try {
            return Carbon::parse($value)
                ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                ->format('H:i, d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
