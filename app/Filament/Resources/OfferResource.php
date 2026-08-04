<?php

namespace App\Filament\Resources;

use App\Models\Offer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
            ->actions([
                Action::make('view_details')
                    ->label('Xem và duyệt')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record])),
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
