<?php

namespace App\Filament\Resources\Workplaces\Pages;

use App\Filament\Resources\Workplaces\WorkplaceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkplaces extends ViewRecord
{
    protected static string $resource = WorkplaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
