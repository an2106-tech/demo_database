<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Hồ sơ ứng tuyển - '.$this->record->snapshotCandidateName();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Chỉnh sửa'),
        ];
    }
}
