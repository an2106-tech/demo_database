<?php

namespace App\Filament\Resources\Workplaces\Pages;

use App\Filament\Resources\Workplaces\WorkplaceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkplaces extends ListRecords
{
    protected static string $resource = WorkplaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->label('Thêm địa điểm')
                ->modalHeading('Thêm địa điểm làm việc')
                ->modalSubmitActionLabel('Tạo mới'),
        ];
    }
}
