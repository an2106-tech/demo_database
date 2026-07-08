<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OfferResource;
use App\Models\Application;
use App\Models\ApplicationAiAnalysis;
use App\Models\Scorecard;
use App\Services\OfferApprovalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
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
                Section::make('Đề nghị cần duyệt')
                    ->columns(3)
                    ->schema([
                        TextInput::make('candidate_name')
                            ->label('Ứng viên')
                            ->disabled(),
                        TextInput::make('job_title')
                            ->label('Vị trí ứng tuyển')
                            ->disabled(),
                        TextInput::make('status_display')
                            ->label('Trạng thái đề nghị')
                            ->disabled(),
                        TextInput::make('branch_name')
                            ->label('Chi nhánh')
                            ->disabled(),
                        TextInput::make('department_name')
                            ->label('Phòng ban')
                            ->disabled(),
                        TextInput::make('request_owner_name')
                            ->label('HR gửi đề nghị')
                            ->disabled(),
                        TextInput::make('approval_requested_at_display')
                            ->label('Gửi duyệt lúc')
                            ->disabled(),
                        TextInput::make('offer_code')
                            ->label('Mã đề nghị')
                            ->disabled(),
                    ]),

                Section::make('Điều khoản đề nghị')
                    ->columns(4)
                    ->schema([
                        TextInput::make('salary_display')
                            ->label('Mức lương đề nghị')
                            ->disabled(),
                        TextInput::make('start_date_display')
                            ->label('Ngày bắt đầu')
                            ->disabled(),
                        TextInput::make('probation_display')
                            ->label('Thời gian thử việc')
                            ->disabled(),
                        TextInput::make('expires_at_display')
                            ->label('Hạn phản hồi')
                            ->disabled(),
                        TextInput::make('template_name')
                            ->label('Mẫu thư')
                            ->disabled(),
                        TextInput::make('pdf_status')
                            ->label('PDF đề nghị')
                            ->disabled(),
                    ]),

                Section::make('Căn cứ đánh giá')
                    ->compact()
                    ->schema([
                        Html::make(fn (): HtmlString => $this->renderDecisionEvidence()),
                    ]),

                Section::make('CV ứng viên')
                    ->schema([
                        Html::make(fn (): HtmlString => $this->renderCvPanel()),
                    ]),

                Section::make('Thông tin bổ sung')
                    ->collapsed()
                    ->hidden()
                    ->columns(2)
                    ->schema([
                        TextInput::make('candidate_email')
                            ->label('Email liên hệ')
                            ->disabled(),
                        TextInput::make('candidate_phone')
                            ->label('Số điện thoại')
                            ->disabled(),
                        TextInput::make('workplace_name')
                            ->label('Nơi làm việc')
                            ->disabled(),
                        TextInput::make('created_at_display')
                            ->label('Ngày tạo đề nghị')
                            ->disabled(),
                        Textarea::make('content_preview')
                            ->label('Nội dung thư mời / ghi chú đề nghị')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
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
                ->label('Tải PDF đề nghị')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => filled($offer->pdf_path) && Storage::disk('local')->exists($offer->pdf_path))
                ->action(fn () => response()->download(
                    Storage::disk('local')->path($offer->pdf_path),
                    $this->formatOfferCode((int) $offer->id).'.pdf'
                )),

            Action::make('approve')
                ->label('Duyệt đề nghị')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $offer->status === 'awaiting_approval')
                ->requiresConfirmation()
                ->modalHeading('Xác nhận duyệt đề nghị tuyển dụng')
                ->modalDescription('Thư mời nhận việc sẽ được gửi tới ứng viên sau khi duyệt.')
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
                            ->body('Thư mời nhận việc đã được gửi tới ứng viên.')
                            ->send();

                        $this->redirect(OfferResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Lỗi')
                            ->body('Có lỗi xảy ra khi duyệt đề nghị tuyển dụng.')
                            ->send();
                    }
                }),

            Action::make('reject')
                ->label('Từ chối')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $offer->status === 'awaiting_approval')
                ->requiresConfirmation()
                ->modalHeading('Từ chối đề nghị tuyển dụng')
                ->modalDescription('HR cần điều chỉnh đề nghị tuyển dụng trước khi gửi lại.')
                ->form([
                    Textarea::make('approval_notes')
                        ->label('Lý do từ chối')
                        ->rows(4)
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
                            ->title('Đã từ chối đề nghị tuyển dụng')
                            ->body('Đề nghị tuyển dụng đã bị từ chối.')
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
            'rejected' => 'Ứng viên đã từ chối',
            'draft' => 'Bản nháp',
            default => $status,
        };
    }

    private function renderDecisionEvidence(): HtmlString
    {
        $interviewSummary = $this->record->application
            ? $this->renderDirectorScorecardSummary($this->record->application)
            : new HtmlString('<span class="text-sm text-gray-500 dark:text-gray-400">Chưa có hồ sơ ứng tuyển để đối chiếu.</span>');

        $aiSummary = $this->renderCandidateSummary();

        return new HtmlString(<<<HTML
            <div style="display:grid;gap:14px;">
                {$aiSummary}
                {$interviewSummary}
            </div>
        HTML);
    }

    private function renderCvPanel(): HtmlString
    {
        $application = $this->record->application;
        $url = $application?->submittedCvUrl();

        if (! $url) {
            return new HtmlString('<div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-500 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-gray-400">Chưa có file CV để đối chiếu.</div>');
        }

        $cvName = e($application?->submittedCvName() ?: 'CV ứng viên');
        $safeUrl = e($url);
        $isPdf = str_ends_with(strtolower((string) $application?->submittedCvName()), '.pdf')
            || str_contains(strtolower($safeUrl), '.pdf');

        $preview = $isPdf
            ? '<iframe src="'.$safeUrl.'#toolbar=1&navpanes=0&view=FitH" style="width:100%;height:720px;border:0;background:#f3f4f6;"></iframe>'
            : '<div style="height:220px;display:flex;align-items:center;justify-content:center;background:#f9fafb;padding:24px;text-align:center;font-size:14px;line-height:1.6;color:#6b7280;">Định dạng CV này không hỗ trợ xem trước trực tiếp. Vui lòng mở CV gốc để đối chiếu.</div>';

        return new HtmlString(<<<HTML
            <div style="overflow:hidden;border:1px solid #e5e7eb;border-radius:12px;background:#ffffff;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #e5e7eb;padding:10px 12px;background:#ffffff;">
                    <div style="min-width:0;">
                        <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.02em;color:#6b7280;">CV gốc để đối chiếu</div>
                        <div style="margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;font-weight:800;color:#111827;">{$cvName}</div>
                    </div>
                    <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer" style="flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;background:#f97316;color:#ffffff;padding:8px 13px;font-size:13px;font-weight:800;text-decoration:none;">Mở CV</a>
                </div>
                {$preview}
            </div>
        HTML);
    }

    private function renderDirectorScorecardSummary(Application $application): HtmlString
    {
        $scorecards = $application->scorecards()
            ->with(['evaluator', 'interview', 'template'])
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
                                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#6b7280;">Khuyến nghị</div>
                                <div style="margin-top:3px;font-size:13px;font-weight:800;color:#111827;">{$recommendation}</div>
                            </div>
                            <div style="border-radius:10px;background:#f9fafb;padding:9px 10px;">
                                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#6b7280;">Kết luận</div>
                                <div style="margin-top:3px;font-size:13px;font-weight:800;color:#111827;">{$conclusion}</div>
                            </div>
                        </div>

                        {$notes}
                    </div>
                    {$criteria}
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
            <details style="display:block;margin:0;">
                <summary style="cursor:pointer;display:inline-flex;align-items:center;border-radius:8px;border:1px solid #e5e7eb;background:#ffffff;padding:6px 10px;font-size:13px;font-weight:700;color:#374151;list-style:none;">Xem điểm theo tiêu chí</summary>
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
        $summary = trim((string) ($analysis?->summary ?? ''));
        $strengths = $this->renderInlineList((array) ($analysis?->strengths ?? []), '#15803d');
        $gaps = $this->renderInlineList((array) ($analysis?->gaps ?? []), '#ea580c');

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
            <div style="position:relative;border:1px solid #e5e7eb;border-radius:12px;background:#ffffff;padding:13px 14px;box-shadow:0 1px 2px rgba(15,23,42,.04);">
                <div style="display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:16px;align-items:start;">
                    <div style="min-width:0;">
                        <div style="margin-bottom:5px;font-size:12px;font-weight:800;text-transform:uppercase;color:#6b7280;">Đánh giá tổng quan</div>
                        {$summaryHtml}
                    </div>
                    <div style="display:grid;gap:10px;justify-items:end;">
                        <div style="display:flex;flex-wrap:nowrap;justify-content:flex-end;gap:6px;">{$scoreBadge}{$recommendationBadge}</div>
                        <details>
                        <summary style="cursor:pointer;display:inline-flex;align-items:center;border-radius:8px;border:1px solid #e5e7eb;background:#ffffff;padding:6px 10px;font-size:13px;font-weight:700;color:#374151;white-space:nowrap;">Xem phân tích AI</summary>
                        <div style="position:absolute;z-index:20;margin-top:8px;right:14px;width:min(520px,calc(100vw - 80px));border:1px solid #e5e7eb;border-radius:12px;background:#ffffff;padding:12px;box-shadow:0 10px 24px rgba(15,23,42,.16);">
                            <div style="display:grid;gap:10px;">
                                <div>
                                    <div style="margin-bottom:5px;font-size:12px;font-weight:800;color:#15803d;">Điểm phù hợp</div>
                                    {$strengths}
                                </div>
                                <div>
                                    <div style="margin-bottom:5px;font-size:12px;font-weight:800;color:#ea580c;">Cần làm rõ</div>
                                    {$gaps}
                                </div>
                            </div>
                        </div>
                        </details>
                    </div>
                </div>
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
