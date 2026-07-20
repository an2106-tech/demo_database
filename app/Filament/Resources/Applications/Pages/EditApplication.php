<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Enums\StatusApplicationEnum;
use App\Models\Candidate;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected StatusApplicationEnum|string|null $originalStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ApplicationResource::canDelete($this->record)),
            ForceDeleteAction::make()
                ->visible(fn (): bool => ApplicationResource::canForceDelete($this->record)),
            RestoreAction::make()
                ->visible(fn (): bool => ApplicationResource::canRestore($this->record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->originalStatus = $this->record->status;

        $candidateId = $data['candidate_id'] ?? null;
        $applyMethod = $data['apply_method'] ?? null;
        // Status is managed via table actions (buttons), not via the edit form.
        $data['status'] = $this->record->status;

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
                    'candidate_id' => 'Ứng viên đã chọn chưa có CV. Vui lòng cập nhật CV cho ứng viên hoặc chọn ứng viên khác.',
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
