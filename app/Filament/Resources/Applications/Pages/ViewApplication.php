<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing([
            'candidate',
            'cvAttachment',
            'job.branch',
            'job.department',
            'branch',
            'assignedHr',
            'aiAnalyses',
            'latestScreeningAiAnalysis',
            'preScreenings.handledBy',
            'statusHistories.user.branch',
            'interviews.interviewer',
            'interviews.finalizedBy',
            'interviews.workplace',
            'interviews.scorecardTemplate',
            'interviews.evaluators.user',
            'interviews.evaluators.waivedBy',
            'interviews.scorecards.evaluator',
            'interviews.scorecards.template',
            'offers.approvedByUser',
            'offers.letterTemplate',
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Chi tiết ứng tuyển';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return collect([
            $this->record->snapshotCandidateName(),
            $this->record->job?->title,
            $this->record->job?->branch?->name ?? $this->record->branch?->name,
        ])->filter()->implode(' · ');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_cv')
                ->label('Mở CV')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (): ?string => $this->record->submittedCvUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->submittedCvUrl())),
            Action::make('open_kanban')
                ->label('Mở Kanban')
                ->icon('heroicon-o-view-columns')
                ->url(ApplicationResource::getUrl('kanban')),
            ActionGroup::make([
                Action::make('download_offer_pdf')
                    ->label('Tải PDF đề nghị')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (): bool => filled($this->record->latestOffer?->pdf_path))
                    ->action(function () {
                        $offer = $this->record->offers->sortByDesc('id')->first();
                        $disk = Storage::disk('local');

                        if (! $offer?->pdf_path || ! $disk->exists($offer->pdf_path)) {
                            return null;
                        }

                        return response()->download(
                            $disk->path($offer->pdf_path),
                            'thu-moi-nhan-viec-'.$offer->id.'.pdf',
                            ['Content-Type' => 'application/pdf'],
                        );
                    }),
                EditAction::make()
                    ->label('Cập nhật thông tin')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn (): bool => ApplicationResource::canEdit($this->record)),
            ])
                ->label('Thao tác khác')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->color('gray'),
        ];
    }
}
