<?php

namespace App\Filament\Resources;

use App\Models\Offer;
use App\Services\OfferApprovalService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?string $navigationLabel = 'Duyệt Offer';

    protected static ?string $modelLabel = 'offer';

    protected static ?string $pluralModelLabel = 'offer';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                
                // Only show offers awaiting approval for this branch director
                if ($user && $user->branchScopeId()) {
                    $query->whereHas('application.job', fn ($q) => 
                        $q->where('branch_id', $user->branchScopeId())
                    );
                }

                return $query->where('status', 'awaiting_approval');
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID Offer')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('application.candidate.name')
                    ->label('Ứng viên')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('application.job.title')
                    ->label('Vị trí')
                    ->sortable(),
                
                TextColumn::make('salary_offered')
                    ->label('Mức lương')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.') . ' VND')
                    ->sortable(),
                
                TextColumn::make('start_date')
                    ->label('Ngày bắt đầu')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('probation_months')
                    ->label('Thử việc')
                    ->suffix(' tháng')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'awaiting_approval' => 'warning',
                        'pending' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'awaiting_approval' => 'Chờ duyệt',
                        'pending' => 'Chờ phản hồi',
                        'accepted' => 'Đã chấp nhận',
                        'rejected' => 'Đã từ chối',
                        default => $state,
                    }),
                
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'awaiting_approval' => 'Chờ duyệt',
                        'pending' => 'Chờ phản hồi',
                        'accepted' => 'Đã chấp nhận',
                        'rejected' => 'Đã từ chối',
                    ]),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Xem chi tiết')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                
                Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'awaiting_approval')
                    ->requiresConfirmation()
                    ->modalHeading('Duyệt Offer')
                    ->modalDescription(fn ($record) => 'Offer cho ' . $record->application->candidate?->name . ' sẽ được gửi tới ứng viên.')
                    ->action(function ($record) {
                        $user = Auth::user();
                        if (!$user) {
                            Notification::make()
                                ->danger()
                                ->title('Lỗi')
                                ->body('Không tìm thấy thông tin người dùng.')
                                ->send();
                            return;
                        }

                        $service = app(OfferApprovalService::class);
                        if ($service->approve($record, $user)) {
                            Notification::make()
                                ->success()
                                ->title('Đã duyệt offer')
                                ->body('Offer đã được gửi tới ứng viên và thông báo đã được gửi tới nhóm.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Lỗi khi duyệt offer')
                                ->body('Có lỗi xảy ra khi duyệt offer. Vui lòng thử lại.')
                                ->send();
                        }
                    }),
                
                Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'awaiting_approval')
                    ->requiresConfirmation()
                    ->modalHeading('Từ chối Offer')
                    ->modalDescription('HR sẽ cần xem xét lại và điều chỉnh offer trước khi gửi lại duyệt.')
                    ->action(function ($record) {
                        $user = Auth::user();
                        if (!$user) {
                            Notification::make()
                                ->danger()
                                ->title('Lỗi')
                                ->body('Không tìm thấy thông tin người dùng.')
                                ->send();
                            return;
                        }

                        $service = app(OfferApprovalService::class);
                        if ($service->reject($record, $user, 'Từ chối từ giám đốc chi nhánh')) {
                            Notification::make()
                                ->warning()
                                ->title('Đã từ chối offer')
                                ->body('Offer đã bị từ chối. HR sẽ cần điều chỉnh và gửi lại.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Lỗi khi từ chối offer')
                                ->body('Có lỗi xảy ra. Vui lòng thử lại.')
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Pages\ListOffers::route('/'),
            'edit' => \App\Filament\Resources\Pages\EditOffer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Only show to directors for their branches
        if ($user && !$user->hasRole('super_admin')) {
            if ($user->hasRole('director') && $user->branchScopeId()) {
                $query->whereHas('application.job', fn ($q) => 
                    $q->where('branch_id', $user->branchScopeId())
                );
            } else {
                // Non-director users shouldn't see this resource
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
