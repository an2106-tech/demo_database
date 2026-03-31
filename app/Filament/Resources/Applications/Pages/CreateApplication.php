<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Candidate;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $cvFile = Candidate::query()
            ->whereKey($data['candidate_id'] ?? null)
            ->value('cv_file');

        if (! empty($cvFile)) {
            $data['cv_path'] = $cvFile;
        }

        return $data;
    }
}
