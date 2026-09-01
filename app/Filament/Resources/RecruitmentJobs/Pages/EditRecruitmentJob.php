<?php

namespace App\Filament\Resources\RecruitmentJobs\Pages;

use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecruitmentJob extends EditRecord
{
    protected static string $resource = RecruitmentJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Xóa bản nháp'),
        ];
    }
}
