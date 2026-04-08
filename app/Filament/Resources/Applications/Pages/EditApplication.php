<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Candidate;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $candidateId = $data['candidate_id'] ?? null;
        $applyMethod = $data['apply_method'] ?? null;

        if ($applyMethod === 'profile') {
            $data['cv_path'] = $data['cv_path'] ?? null;

            return $data;
        }

        if (empty($data['cv_path'])) {
            $cvFile = Candidate::query()
                ->whereKey($candidateId)
                ->value('cv_file');

            if (! empty($cvFile)) {
                $data['cv_path'] = $cvFile;
            }
        }

        if (empty($data['cv_path'])) {
            $candidateChanged = (int) $candidateId !== (int) $this->record->candidate_id;

            if ($candidateChanged) {
                throw ValidationException::withMessages([
                    'candidate_id' => 'Ung vien duoc chon chua co CV. Vui long tai CV cho ung vien nay truoc khi luu.',
                ]);
            }

            $data['cv_path'] = $this->record->cv_path;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->record?->candidate_id || empty($this->record->cv_path)) {
            return;
        }

        Candidate::query()
            ->whereKey($this->record->candidate_id)
            ->update(['cv_file' => $this->record->cv_path]);
    }
}
