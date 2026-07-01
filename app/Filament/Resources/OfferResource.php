<?php

namespace App\Filament\Resources;

use App\Models\Offer;
use App\Services\OfferApprovalService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?string $navigationLabel = 'Duyệt đề nghị';

    protected static ?string $modelLabel = 'đề nghị tuyển dụng';

    protected static ?string $pluralModelLabel = 'đề nghị tuyển dụng';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin')
            || $user->role === 'admin'
            || $user->hasRole('director')
            || $user->role === 'director';
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin') || $user->role === 'admin') {
            return true;
        }

        $isDirector = $user->hasRole('director') || $user->role === 'director';

        if (! $isDirector || ! $user->branchScopeId()) {
            return false;
        }

        return (int) $record->application?->job?->branch_id === (int) $user->branchScopeId();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                
                // Only show offers awaiting approval for this branch director
                if ($user && ($user->hasRole('director') || $user->role === 'director') && $user->branchScopeId()) {
                    $query->whereHas('application.job', fn ($q) => 
                        $q->where('branch_id', $user->branchScopeId())
                    );
                }

                return $query->where('status', 'awaiting_approval');
            })
            ->columns([
                TextColumn::make('id')
                    ->label('Mã đề nghị')
                    ->formatStateUsing(fn ($state) => 'OFF-'.str_pad((string) $state, 6, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('application.candidate.name')
                    ->label('Ứng viên')
                    ->formatStateUsing(fn ($record): string => $record->application?->snapshotCandidateName() ?? 'Ứng viên')
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
                    ->modalHeading('Duyệt đề nghị tuyển dụng')
                    ->modalDescription(fn ($record) => 'Đề nghị tuyển dụng cho ' . ($record->application?->snapshotCandidateName() ?? 'ứng viên') . ' sẽ được gửi thành thư mời nhận việc tới ứng viên.')
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
                                ->title('Đã duyệt đề nghị tuyển dụng')
                                ->body('Thư mời nhận việc đã được gửi tới ứng viên và thông báo đã được gửi tới nhóm.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Lỗi khi duyệt đề nghị')
                                ->body('Có lỗi xảy ra khi duyệt đề nghị tuyển dụng. Vui lòng thử lại.')
                                ->send();
                        }
                    }),
                
                Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'awaiting_approval')
                    ->requiresConfirmation()
                    ->modalHeading('Từ chối đề nghị tuyển dụng')
                    ->modalDescription('HR sẽ cần xem xét lại và điều chỉnh đề nghị tuyển dụng trước khi gửi duyệt lại.')
                    ->form([
                        Textarea::make('approval_notes')
                            ->label('Lý do từ chối')
                            ->rows(4)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
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
                        if ($service->reject($record, $user, trim((string) ($data['approval_notes'] ?? '')))) {
                            Notification::make()
                                ->warning()
                                ->title('Đã từ chối đề nghị tuyển dụng')
                                ->body('Đề nghị tuyển dụng đã bị từ chối. HR sẽ cần điều chỉnh và gửi lại.')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Lỗi khi từ chối đề nghị')
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
            $isDirector = $user->hasRole('director') || $user->role === 'director';

            if ($isDirector && $user->branchScopeId()) {
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
