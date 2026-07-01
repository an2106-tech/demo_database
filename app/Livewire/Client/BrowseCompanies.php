<?php

namespace App\Livewire\Client;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Enums\VietnamProvince;
use App\Models\Branch;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Doanh nghiệp tuyển dụng')]
class BrowseCompanies extends Component
{
    use WithPagination;

    public $search = '';

    public $date_filter = 'all';

    public $salary_range = [0, 10000];

    public $selected_cities = [];

    public $search_city_keyword = '';

    public $applied_cities = [];

    public $salary_min = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'date_filter' => ['except' => 'all'],
        'applied_cities' => ['as' => 'cities', 'except' => []],
        'salary_min' => ['except' => 0],
    ];

    public function mount(): void
    {
        $this->selected_cities = $this->applied_cities;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedSalaryMin()
    {
        $this->resetPage();
    }

    #[Layout('layouts.client')]
    public function clearAllCities()
    {
        $this->selected_cities = [];
        $this->applied_cities = [];
        $this->resetPage();
    }

    public function applyCityFilter()
    {
        $this->applied_cities = $this->selected_cities;
        $this->resetPage();
        $this->dispatch('close-city-dropdown');
    }

    private function dateThreshold(Carbon $now): ?Carbon
    {
        return match ($this->date_filter) {
            'hour' => $now->copy()->subHour(),
            '24h' => $now->copy()->subDay(),
            '7d' => $now->copy()->subDays(7),
            '14d' => $now->copy()->subDays(14),
            '30d' => $now->copy()->subDays(30),
            default => null,
        };
    }

    public function render()
    {
        $now = Carbon::now();
        $dateThreshold = $this->dateThreshold($now);
        $searchTerm = trim((string) $this->search);

        $jobCondition = function ($query) use ($now, $dateThreshold) {
            $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                ->where(function ($q) use ($now) {
                    $q->whereNull('deadline')->orWhere('deadline', '>=', $now);
                });

            if ($dateThreshold) {
                $query->where('created_at', '>=', $dateThreshold);
            }

            if ((int) $this->salary_min > 0) {
                $query->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(salary_range, '$.max')) AS DECIMAL(10, 2)) >= ?",
                    [(int) $this->salary_min]
                );
            }
        };

        $provincesList = VietnamProvince::options();
        if (! empty($this->search_city_keyword)) {
            $provincesList = array_filter($provincesList, function ($label) {
                return str_contains(mb_strtolower($label), mb_strtolower($this->search_city_keyword));
            });
        }

        $query = Branch::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'image', 'city', 'address', 'email_contact', 'is_active']);

        if ($searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm, $jobCondition) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('address', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('recruitmentJobs', function ($sub) use ($searchTerm, $jobCondition) {
                        $jobCondition($sub);
                        $sub->where('title', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if (! empty($this->applied_cities)) {
            $query->whereIn('city', $this->applied_cities);
        }

        $query->whereHas('recruitmentJobs', $jobCondition)
            ->withCount(['recruitmentJobs as published_jobs_count' => $jobCondition])
            ->with(['recruitmentJobs' => function ($q) use ($jobCondition, $searchTerm) {
                $jobCondition($q);

                if ($searchTerm !== '') {
                    $q->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ['%' . $searchTerm . '%']);
                }

                $q->orderByDesc('created_at')->select(['id', 'branch_id', 'title', 'slug', 'salary_range', 'deadline', 'created_at']);
            }]);

        return view('livewire.client.browse-companies', [
            'branches' => $query->latest()->paginate(10),
            'provincesList' => $provincesList,
        ]);
    }
}
