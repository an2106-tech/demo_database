<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả'),
            'new' => Tab::make('Mới')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'new'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', 'new')->count()),
            'screening' => Tab::make('Sàng lọc')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'screening'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', 'screening')->count()),
            'interview' => Tab::make('Phỏng vấn')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'interview'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', 'interview')->count()),
            'offer' => Tab::make('Offer')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'offer'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', 'offer')->count()),
            'hired' => Tab::make('Đã tuyển')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'hired'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', 'hired')->count()),
            'rejected' => Tab::make('Từ chối')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'rejected'))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', 'rejected')->count()),
        ];
    }
}
