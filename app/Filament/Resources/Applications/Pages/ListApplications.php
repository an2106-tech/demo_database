<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kanban')
                ->label('Xem Kanban')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->url(ApplicationResource::getUrl('kanban')),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Tất cả'),
        ];

        foreach (StatusApplicationEnum::pipelineStages() as $stageKey => $stage) {
            $statusValues = StatusApplicationEnum::statusValuesForPipelineStage($stageKey);

            $tabs[$stageKey] = Tab::make($stage['label'])
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $statusValues))
                ->badge(fn () => static::getResource()::getEloquentQuery()->whereIn('status', $statusValues)->count());
        }

        return $tabs;
    }
}
