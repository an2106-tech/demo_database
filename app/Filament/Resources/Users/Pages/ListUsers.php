<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả'),

            'hr_pending' => Tab::make('HR chờ duyệt')
                ->modifyQueryUsing(static function (Builder $query): Builder {
                    return $query
                        ->where('role', 'hr')
                        ->where('is_active', false)
                        ->where(static function (Builder $q): Builder {
                            return $q
                                ->where('metadata->approval_status', 'pending')
                                ->orWhereNull('metadata');
                        });
                })
                ->badge(fn () => static::getResource()::getEloquentQuery()
                    ->where('role', 'hr')
                    ->where('is_active', false)
                    ->where(static function (Builder $q): Builder {
                        return $q
                            ->where('metadata->approval_status', 'pending')
                            ->orWhereNull('metadata');
                    })
                    ->count()),

            'hr_approved' => Tab::make('HR đã duyệt')
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->where('role', 'hr')->where('is_active', true))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('role', 'hr')->where('is_active', true)->count()),

            'hr_rejected' => Tab::make('HR từ chối')
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->where('role', 'hr')->where('metadata->approval_status', 'rejected'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('role', 'hr')->where('metadata->approval_status', 'rejected')->count()),

            'others' => Tab::make('Người dùng khác')
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->where('role', '!=', 'hr'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('role', '!=', 'hr')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->label('Thêm người dùng')
                ->modalHeading('Thêm người dùng')
                ->modalSubmitActionLabel('Tạo mới'),
        ];
    }
}
