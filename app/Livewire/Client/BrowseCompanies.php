<?php

namespace App\Livewire\Client;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Enums\VietnamProvince;
use App\Models\Branch;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

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
    ];

    public function mount(): void
    {
    }

    public function updatingSearch()
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

    public function render()
    {
        $now = Carbon::now();

        $provincesList = VietnamProvince::options();
        if (! empty($this->search_city_keyword)) {
            $provincesList = array_filter($provincesList, function ($label) {
                return str_contains(mb_strtolower($label), mb_strtolower($this->search_city_keyword));
            });
        }

        $query = Branch::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'image', 'city', 'address', 'email_contact', 'is_active']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%')
                    ->orWhereHas('recruitmentJobs', function ($sub) {
                        $sub->where('title', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if (! empty($this->applied_cities)) {
            $query->whereIn('city', $this->applied_cities);
        }

        if ($this->date_filter !== 'all') {
            $threshold = match ($this->date_filter) {
                'hour' => $now->copy()->subHour(),
                '24h' => $now->copy()->subDay(),
                '7d' => $now->copy()->subDays(7),
                '14d' => $now->copy()->subDays(14),
                '30d' => $now->copy()->subDays(30),
                default => null,
            };

            if ($threshold) {
                $query->whereHas('recruitmentJobs', function ($q) use ($threshold) {
                    $q->where('created_at', '>=', $threshold);
                });
            }
        }

        $jobCondition = function ($query) use ($now) {
            $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                ->where(function ($q) use ($now) {
                    $q->whereNull('deadline')->orWhere('deadline', '>=', $now);
                });

            if ($this->salary_min > 0) {
                $query->whereRaw("CAST(JSON_EXTRACT(salary_range, '$.max') AS UNSIGNED) >= ?", [$this->salary_min]);
            }
        };

        $query->whereHas('recruitmentJobs', $jobCondition)
            ->withCount(['recruitmentJobs as published_jobs_count' => $jobCondition])
            ->with(['recruitmentJobs' => function ($q) use ($jobCondition) {
                $jobCondition($q);
                $q->orderByDesc('created_at')->select(['id', 'branch_id', 'title', 'slug', 'salary_range', 'deadline', 'created_at']);
            }]);

        return view('livewire.client.browse-companies', [
            'branches' => $query->latest()->paginate(10),
            'provincesList' => $provincesList,
        ]);
    }
}
