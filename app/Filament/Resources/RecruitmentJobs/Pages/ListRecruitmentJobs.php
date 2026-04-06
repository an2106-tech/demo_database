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

            'published' => Tab::make(StatusRecruitmentJobsEnum::PUBLISHED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', StatusRecruitmentJobsEnum::PUBLISHED)->count()),

            'draft' => Tab::make(StatusRecruitmentJobsEnum::DRAFT->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRecruitmentJobsEnum::DRAFT))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', StatusRecruitmentJobsEnum::DRAFT)->count()),

            'closed' => Tab::make(StatusRecruitmentJobsEnum::CLOSED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRecruitmentJobsEnum::CLOSED))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', StatusRecruitmentJobsEnum::CLOSED)->count()),

            'archived' => Tab::make(StatusRecruitmentJobsEnum::ARCHIVED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRecruitmentJobsEnum::ARCHIVED))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', StatusRecruitmentJobsEnum::ARCHIVED)->count()),

            'expired' => Tab::make(StatusRecruitmentJobsEnum::EXPIRED->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', StatusRecruitmentJobsEnum::EXPIRED))
                ->badge(fn () => static::getResource()::getEloquentQuery()->where('status', StatusRecruitmentJobsEnum::EXPIRED)->count()),
        ];
    }
}
