<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\OfferResource;
use App\Services\OfferApprovalService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

    protected static ?string $title = 'Duyet Offer';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thong tin ung vien')
                    ->columns(2)
                    ->schema([
                        TextInput::make('candidate_name')
                            ->label('Ung vien')
                            ->disabled(),
                        TextInput::make('candidate_email')
                            ->label('Email')
                            ->disabled(),
                        TextInput::make('job_title')
                            ->label('Vi tri')
                            ->columnSpan(2)
                            ->disabled(),
                    ]),

                Section::make('Chi tiet offer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('salary_offered')
                            ->label('Muc luong de nghi')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.') . ' VND'),
                        DatePicker::make('start_date')
                            ->label('Ngay bat dau')
                            ->disabled(),
                        TextInput::make('probation_months')
                            ->label('Thoi gian thu viec')
                            ->disabled()
                            ->suffix(' thang'),
                        TextInput::make('id')
                            ->label('ID Offer')
                            ->disabled(),
                    ]),

                Section::make('Noi dung offer')
                    ->schema([
                        Textarea::make('content')
                            ->label('Noi dung bo sung')
                            ->disabled()
                            ->rows(6),
                    ]),

                Section::make('Trang thai')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('status')
                                    ->label('Trang thai hien tai')
                                    ->disabled()
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'awaiting_approval' => 'Cho duyet',
                                        'pending' => 'Cho phan hoi tu ung vien',
                                        'accepted' => 'Da chap nhan',
                                        'rejected' => 'Da tu choi',
                                        default => (string) $state,
                                    }),
                                TextInput::make('approval_requested_at')
                                    ->label('Lan gui duyet gan nhat')
                                    ->disabled()
                                    ->formatStateUsing(function ($state) {
                                        if (blank($state)) {
                                            return 'Chua';
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

        $data['candidate_name'] = $this->record->application?->candidate?->name ?? '';
        $data['candidate_email'] = $this->record->application?->candidate?->email ?? '';
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
                ->label('Duyet offer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $offer->status === 'awaiting_approval')
                ->requiresConfirmation()
                ->modalHeading('Xac nhan duyet offer')
                ->modalDescription('Offer se duoc gui toi ung vien sau khi duyet.')
                ->action(function () use ($offer) {
                    $user = Auth::user();

                    if (! $user) {
                        Notification::make()
                            ->danger()
                            ->title('Loi')
                            ->body('Khong tim thay thong tin nguoi dung.')
                            ->send();

                        return;
                    }

                    $service = app(OfferApprovalService::class);

                    if ($service->approve($offer, $user)) {
                        Notification::make()
                            ->success()
                            ->title('Da duyet offer')
                            ->body('Offer da duoc gui toi ung vien.')
                            ->send();

                        $this->redirect(OfferResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Loi')
                            ->body('Co loi xay ra khi duyet offer.')
                            ->send();
                    }
                }),

            Action::make('reject')
                ->label('Tu choi')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $offer->status === 'awaiting_approval')
                ->requiresConfirmation()
                ->modalHeading('Tu choi offer')
                ->modalDescription('HR can dieu chinh offer truoc khi gui lai.')
                ->action(function () use ($offer) {
                    $user = Auth::user();

                    if (! $user) {
                        Notification::make()
                            ->danger()
                            ->title('Loi')
                            ->body('Khong tim thay thong tin nguoi dung.')
                            ->send();

                        return;
                    }

                    $service = app(OfferApprovalService::class);

                    if ($service->reject($offer, $user, 'Tu choi tu giam doc chi nhanh')) {
                        Notification::make()
                            ->warning()
                            ->title('Da tu choi offer')
                            ->body('Offer da bi tu choi.')
                            ->send();

                        $this->redirect(OfferResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Loi')
                            ->body('Co loi xay ra.')
                            ->send();
                    }
                }),

            Action::make('back')
                ->label('Quay lai')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(OfferResource::getUrl('index')),
        ];
    }
}
