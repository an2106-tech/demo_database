<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Candidate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['cv_path'])) {
            $cvFile = Candidate::query()
                ->whereKey($data['candidate_id'] ?? null)
                ->value('cv_file');

            if (! empty($cvFile)) {
                $data['cv_path'] = $cvFile;
            }
        }

        if (empty($data['cv_path'])) {
            throw ValidationException::withMessages([
                'cv_path' => 'Ứng viên này chưa có CV. Vui lòng cập nhật CV trước khi tạo hồ sơ ứng tuyển.',
            ]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        if (! $record?->candidate_id || empty($record->cv_path)) {
            return;
        }

        Candidate::query()
            ->whereKey($record->candidate_id)
            ->update(['cv_file' => $record->cv_path]);
    }
}
