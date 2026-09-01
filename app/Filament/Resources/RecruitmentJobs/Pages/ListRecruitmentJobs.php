<?php

namespace App\Filament\Resources\RecruitmentJobs\Pages;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Models\RecruitmentJob;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListRecruitmentJobs extends ListRecords
{
    protected static string $resource = RecruitmentJobResource::class;

    /** @var array<string, int>|null */
    protected ?array $statusCounts = null;

    public function getTitle(): string
    {
        return 'Quản lý tin tuyển dụng';
    }

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
            'all' => Tab::make('Tất cả')
                ->badge(fn (): int => array_sum($this->getStatusCounts())),
            'pending' => $this->makeStatusTab(StatusRecruitmentJobsEnum::PENDING),
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
            ->badge(fn (): int => $this->getStatusCounts()[$status->value] ?? 0)
            ->badgeColor($status->getColor());
    }

    /** @return array<string, int> */
    protected function getStatusCounts(): array
    {
        if ($this->statusCounts !== null) {
            return $this->statusCounts;
        }

        $query = RecruitmentJob::query();

        if (Auth::user()?->branchScopeId()) {
            $query->where('branch_id', Auth::user()->branchScopeId());
        }

        return $this->statusCounts = $query
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }
}
