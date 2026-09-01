<?php

namespace App\Filament\Resources\Candidates\Pages;

use App\Filament\Resources\Candidates\CandidateResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCandidate extends ViewRecord
{
    protected static string $resource = CandidateResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $applicationsQuery = $this->record->applications()
            ->with(['job.branch', 'cvAttachment'])
            ->latest('applied_at')
            ->latest('id')
            ->limit(10);
        CandidateResource::scopeVisibleApplications($applicationsQuery->getQuery());

        $this->record->setRelation('visibleApplications', $applicationsQuery->get());
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->name ?: 'Chi tiết ứng viên';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return collect([
            'Hồ sơ ứng viên',
            $this->record->resume?->profile_title,
        ])->filter()->implode(' · ');
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('openCurrentCv')
                ->label('Mở CV hiện tại')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (): ?string => $this->record->currentCvUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->currentCvUrl())),
        ];

        if (CandidateResource::canAdministerCandidates()) {
            $actions[] = ActionGroup::make([
                EditAction::make()
                    ->label('Điều chỉnh thông tin')
                    ->visible(fn (): bool => CandidateResource::canEdit($this->record)),
                CandidateResource::restrictAction(),
                CandidateResource::clearRestrictionAction(),
            ])
                ->label('Thao tác khác')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->color('gray');
        }

        return $actions;
    }
}
