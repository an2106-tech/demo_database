<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Tất cả'),
        ];

        foreach (StatusApplicationEnum::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status->value))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', $status->value)->count());
        }

        return $tabs;
    }
}