<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Mail\CandidateApplicationRejectedMail;
use App\Enums\StatusApplicationEnum;
use App\Models\Candidate;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected StatusApplicationEnum|string|null $originalStatus = null;

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
        $this->originalStatus = $this->record->status;

        $candidateId = $data['candidate_id'] ?? null;
        $applyMethod = $data['apply_method'] ?? null;
        $newStatus = $data['status'] ?? null;

        if (! ApplicationForm::canMoveStatusForwardOnly($this->record->status, $newStatus)) {
            throw ValidationException::withMessages([
                'status' => 'Không thể chuyển trạng thái ứng tuyển.',
            ]);
        }

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
            $this->sendRejectedEmailIfNeeded();

            return;
        }

        Candidate::query()
            ->whereKey($this->record->candidate_id)
            ->update(['cv_file' => $this->record->cv_path]);

        $this->sendRejectedEmailIfNeeded();
    }

    protected function sendRejectedEmailIfNeeded(): void
    {
        $currentStatus = $this->record->status instanceof StatusApplicationEnum
            ? $this->record->status
            : StatusApplicationEnum::tryFrom((string) $this->record->status);

        $previousStatus = $this->originalStatus instanceof StatusApplicationEnum
            ? $this->originalStatus
            : StatusApplicationEnum::tryFrom((string) $this->originalStatus);

        if ($currentStatus !== StatusApplicationEnum::REJECTED || $previousStatus === StatusApplicationEnum::REJECTED) {
            return;
        }

        $candidate = $this->record->candidate;
        $job = $this->record->job;

        if (! $candidate?->email || ! $job) {
            return;
        }

        try {
            Mail::to($candidate->email)->send(new CandidateApplicationRejectedMail($candidate, $this->record, $job));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send rejection email to candidate.', [
                'application_id' => $this->record->id,
                'candidate_id' => $candidate->id,
                'recipient' => $candidate->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
