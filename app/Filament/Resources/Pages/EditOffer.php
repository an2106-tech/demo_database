<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OfferResource;
use App\Services\OfferApprovalService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EditOffer extends EditRecord
{
    protected static string $resource = OfferResource::class;

    protected static ?string $title = 'Duyệt đề nghị tuyển dụng';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin ứng viên')
                    ->columns(2)
                    ->schema([
                        TextInput::make('candidate_name')
                            ->label('Ứng viên')
                            ->disabled(),
                        TextInput::make('candidate_email')
                            ->label('Email')
                            ->disabled(),
                        TextInput::make('job_title')
                            ->label('Vị trí')
                            ->columnSpan(2)
                            ->disabled(),
                    ]),

                Section::make('Chi tiết đề nghị tuyển dụng')
                    ->columns(2)
                    ->schema([
                        TextInput::make('salary_offered')
                            ->label('Mức lương đề nghị')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.').' VND'),
                        DatePicker::make('start_date')
                            ->label('Ngày bắt đầu')
                            ->disabled(),
                        TextInput::make('probation_months')
                            ->label('Thời gian thử việc')
                            ->disabled()
                            ->suffix(' tháng'),
                        TextInput::make('id')
                            ->label('ID đề nghị')
                            ->disabled(),
                    ]),

                Section::make('Nội dung thư mời')
                    ->schema([
                        Textarea::make('content')
                            ->label('Nội dung bổ sung')
                            ->disabled()
                            ->rows(6),
                    ]),

                Section::make('Trạng thái')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('status')
                                    ->label('Trạng thái hiện tại')
                                    ->disabled()
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'awaiting_approval' => 'Chờ duyệt',
                                        'pending' => 'Chờ phản hồi từ ứng viên',
                                        'accepted' => 'Đã chấp nhận',
                                        'rejected' => 'Đã từ chối',
                                        default => (string) $state,
                                    }),
                                TextInput::make('approval_requested_at')
                                    ->label('Lần gửi duyệt gần nhất')
                                    ->disabled()
                                    ->formatStateUsing(function ($state) {
                                        if (blank($state)) {
                                            return 'Chưa';
                                        }

                                        if ($state instanceof \DateTimeInterface) {
                                            return $state->format('d/m/Y H:i');
                                        }

                                        try {
                                            return Carbon::parse($state)->format('d/m/Y H:i');
                                        } catch (\Throwable) {
                                            return (string) $state;
                                        }
                                    }),
                            ]),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing(['application.candidate', 'application.job']);

        $data['candidate_name'] = $this->record->application?->snapshotCandidateName() ?? '';
        $data['candidate_email'] = $this->record->application?->snapshotCandidateEmail() ?? '';
        $data['job_title'] = $this->record->application?->job?->title ?? '';

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

                    if ($service->reject($offer, $user, 'Từ chối từ giám đốc chi nhánh')) {
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
}
