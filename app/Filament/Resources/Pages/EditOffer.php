<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OfferResource;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
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

class EditOffer extends EditRecord
{
    protected static string $resource = OfferResource::class;

    protected static ?string $title = 'Duyệt đề nghị tuyển dụng';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Tổng quan đề nghị')
                    ->columns(3)
                    ->schema([
                        TextInput::make('offer_code')
                            ->label('Mã đề nghị')
                            ->disabled(),
                        TextInput::make('status_display')
                            ->label('Trạng thái')
                            ->disabled(),
                        TextInput::make('candidate_name')
                            ->label('Ứng viên')
                            ->disabled(),
                        TextInput::make('job_title')
                            ->label('Vị trí tuyển dụng')
                            ->disabled(),
                        TextInput::make('request_owner_name')
                            ->label('HR phụ trách đề nghị')
                            ->disabled(),
                        TextInput::make('approval_requested_at_display')
                            ->label('Gửi duyệt lúc')
                            ->disabled(),
                    ]),

                Section::make('Điều khoản cần duyệt')
                    ->columns(2)
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
                    ]),

                Section::make('Căn cứ đánh giá')
                    ->schema([
                        Html::make(fn () => ApplicationsTable::renderInterviewScorecardsSummary($this->record->application)),
                        Html::make($this->renderCvLink())
                    ]),

                Section::make('Thông tin bổ sung')
                    ->columns(3)
                    ->schema([
                        TextInput::make('branch_name')
                            ->label('Chi nhánh')
                            ->disabled(),
                        TextInput::make('department_name')
                            ->label('Phòng ban')
                            ->disabled(),
                        TextInput::make('workplace_name')
                            ->label('Nơi làm việc')
                            ->disabled(),
                        TextInput::make('candidate_email')
                            ->label('Email liên hệ')
                            ->disabled(),
                        TextInput::make('candidate_phone')
                            ->label('Số điện thoại')
                            ->disabled(),
                        TextInput::make('profile_title')
                            ->label('Tiêu đề hồ sơ')
                            ->disabled(),
                        TextInput::make('experience_years')
                            ->label('Kinh nghiệm')
                            ->disabled(),
                        TextInput::make('cv_name')
                            ->label('CV đã nộp')
                            ->disabled(),
                        TextInput::make('template_name')
                            ->label('Mẫu thư')
                            ->disabled(),
                        TextInput::make('pdf_status')
                            ->label('PDF đề nghị')
                            ->disabled(),
                        TextInput::make('created_at_display')
                            ->label('Ngày tạo đề nghị')
                            ->disabled(),
                        Textarea::make('content_preview')
                            ->label('Nội dung bổ sung')
                            ->disabled()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing([
            'application.candidate',
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

    private function renderCvLink(): string
    {
        $application = $this->record->application;
        $url = $application?->submittedCvUrl();

        if (! $url) {
            return '<div style="border:1px solid #374151;border-radius:10px;padding:14px 16px;color:#9ca3af;font-size:14px;">Chưa có file CV để đối chiếu.</div>';
        }

        $cvName = e($application?->submittedCvName() ?: 'CV ứng viên');

        return sprintf(
            '<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;border:1px solid #374151;border-radius:10px;padding:14px 16px;">
                <div style="min-width:0;">
                    <div style="color:#9ca3af;font-size:12px;font-weight:600;text-transform:uppercase;">CV ứng viên</div>
                    <div style="margin-top:4px;color:#ffffff;font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">%s</div>
                </div>
                <a href="%s" target="_blank" rel="noopener noreferrer" style="flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;background:#f97316;color:#ffffff;padding:9px 16px;font-size:14px;font-weight:700;text-decoration:none;">Xem CV</a>
            </div>',
            $cvName,
            e($url)
        );
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
