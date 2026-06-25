<?php

namespace App\Livewire\Client;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Services\LocationSearchNormalizer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class BrowseJobs extends Component
{
    use WithPagination;

    #[Layout('layouts.client')]
    public string $display = 'grid';

    protected string $paginationTheme = 'bootstrap';

    public string $q = '';

    public string $city = '';

    public ?int $department_id = null;

    public ?int $category_id = null;

    protected array $queryString = [
        'display' => ['except' => 'grid'],
        'q' => ['except' => ''],
        'city' => ['except' => ''],
        'department_id' => ['except' => null],
        'category_id' => ['except' => null],
    ];

    public function mount(): void
    {
        if (! request()->has('display') && request()->has('view')) {
            $requestedView = request()->query('view');
            if (is_string($requestedView)) {
                $this->display = $requestedView;
            }
        }

        $this->q = (string) request()->query('q', '');
        $this->city = (string) request()->query('city', '');
        $dept = request()->query('department_id');
        $this->department_id = $dept !== null && $dept !== '' ? (int) $dept : null;

        $cat = request()->query('category_id');
        $this->category_id = $cat !== null && $cat !== '' ? (int) $cat : null;

        $this->normalizeDisplay();
    }

    public function updatedDisplay(): void
    {
        $this->normalizeDisplay();
    }

    public function updated($property): void
    {
        if ($property === 'city') {
            $this->department_id = null;
        }

        if (in_array($property, ['q', 'city', 'department_id', 'category_id'], true)) {
            $this->resetPage();
        }
    }

    public function setDisplay(string $display): void
    {
        $this->display = $display;
        $this->normalizeDisplay();
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->city = '';
        $this->department_id = null;
        $this->category_id = null;
        $this->resetPage();
    }

    private function normalizeDisplay(): void
    {
        $this->display = in_array($this->display, ['grid', 'list'], true) ? $this->display : 'grid';
    }

    public function render(LocationSearchNormalizer $locationSearchNormalizer)
    {
        $now = Carbon::now();
        $location = trim($this->city) !== '' ? $locationSearchNormalizer->normalize($this->city) : null;

        $jobsQuery = RecruitmentJob::query()
            ->where(fn (Builder $query) => $this->applyOpenJobConstraint($query, $now))
            ->with(['branch:id,name,image,city,address', 'workplace:id,name', 'department:id,name']);

        if (trim($this->q) !== '') {
            $keyword = trim($this->q);
            $jobsQuery->where(function ($query) use ($keyword) {
                $query
                    ->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhereHas('branch', function ($branchQuery) use ($keyword) {
                        $branchQuery->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('department', function ($departmentQuery) use ($keyword) {
                        $departmentQuery->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if ($location !== null) {
            $jobsQuery->whereHas('branch', function ($query) use ($location) {
                $this->applyLocationConstraint($query, $location);
            });
        }

        if (! empty($this->department_id)) {
            $jobsQuery->where('department_id', $this->department_id);
        }

        if (! empty($this->category_id)) {
            $jobsQuery->whereHas('categories', function ($query) {
                $query->where('categories.id', $this->category_id);
            });
        }

        $jobs = $jobsQuery->latest()->paginate(12);
        $departments = $this->availableDepartments($now, $location);

        return view('livewire.client.browse-jobs', [
            'jobs' => $jobs,
            'departments' => $departments,
        ]);
    }

    private function applyOpenJobConstraint(Builder $query, Carbon $now): void
    {
        $query
            ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('deadline')
                    ->orWhere('deadline', '>=', $now);
            });
    }

    /**
     * @param array{term: string, province_values: array<int, string>, keywords: array<int, string>} $location
     */
    private function applyLocationConstraint(Builder $query, array $location): void
    {
        $query->where(function (Builder $branchQuery) use ($location) {
            if ($location['province_values'] !== []) {
                $branchQuery->whereIn('city', $location['province_values']);
            }

            foreach ($location['keywords'] as $keyword) {
                $branchQuery
                    ->orWhere('city', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            }
        });
    }

    /**
     * @param array{term: string, province_values: array<int, string>, keywords: array<int, string>}|null $location
     */
    private function availableDepartments(Carbon $now, ?array $location)
    {
        return Department::query()
            ->whereHas('recruitmentJobs', function (Builder $query) use ($now, $location) {
                $this->applyOpenJobConstraint($query, $now);

                if ($location !== null) {
                    $query->whereHas('branch', function (Builder $branchQuery) use ($location) {
                        $this->applyLocationConstraint($branchQuery, $location);
                    });
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
