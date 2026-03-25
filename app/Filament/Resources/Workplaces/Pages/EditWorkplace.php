<?php

namespace App\Filament\Resources\Workplaces\Pages;

use App\Filament\Resources\Workplaces\WorkplaceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkplace extends EditRecord
{
    protected static string $resource = WorkplaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
