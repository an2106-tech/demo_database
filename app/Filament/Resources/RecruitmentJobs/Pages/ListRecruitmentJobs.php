<?php

namespace App\Filament\Resources\RecruitmentJobs\Pages;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRecruitmentJobs extends ListRecords
{
    protected static string $resource = RecruitmentJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Thêm tin tuyển dụng'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả'),
            'published' => $this->makeStatusTab(StatusRecruitmentJobsEnum::PUBLISHED),
            'draft' => $this->makeStatusTab(StatusRecruitmentJobsEnum::DRAFT),
            'closed' => $this->makeStatusTab(StatusRecruitmentJobsEnum::CLOSED),
            'archived' => $this->makeStatusTab(StatusRecruitmentJobsEnum::ARCHIVED),
            'expired' => $this->makeStatusTab(StatusRecruitmentJobsEnum::EXPIRED),
        ];
    }

    protected function makeStatusTab(StatusRecruitmentJobsEnum $status): Tab
    {
        return Tab::make($status->getLabel())
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status->value))
            ->badge(fn (): int => RecruitmentJobResource::getEloquentQuery()->where('status', $status->value)->count());
    }
}