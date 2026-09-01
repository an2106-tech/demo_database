<?php

namespace App\Filament\Resources\RecruitmentJobs\Pages;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Filament\Resources\RecruitmentJobs\Tables\RecruitmentJobsTable;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewRecruitmentJob extends ViewRecord
{
    protected static string $resource = RecruitmentJobResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->title;
    }

    public function getBreadcrumb(): string
    {
        return 'Chi tiết';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return collect([
            'Chi tiết tin tuyển dụng',
            $this->record->branch?->name,
            $this->record->department?->name,
        ])->filter()->implode(' · ');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public_job')
                ->label('Xem tin công khai')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): string => route('jobs.public', ['slug' => $this->record->slug]))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->slug)
                    && $this->record->status === StatusRecruitmentJobsEnum::PUBLISHED),
            EditAction::make()
                ->label('Chỉnh sửa tin')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn (): bool => RecruitmentJobResource::canEdit($this->record)),
            ...RecruitmentJobsTable::lifecycleActions(),
        ];
    }
}
