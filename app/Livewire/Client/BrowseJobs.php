<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Enums\StatusRecruitmentJobsEnum;
use Carbon\Carbon;

class BrowseJobs extends Component
{
    #[Layout('layouts.client')]

    public string $display = 'grid';

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

    public function setDisplay(string $display): void
    {
        $this->display = $display;
        $this->normalizeDisplay();
    }

    private function normalizeDisplay(): void
    {
        $this->display = in_array($this->display, ['grid', 'list'], true) ? $this->display : 'grid';
    }

    public function render()
    {
        $now = Carbon::now();
        $jobsQuery = RecruitmentJob::query()
            ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->where(function ($q) use ($now) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', $now);
            })
            ->with(['branch:id,name,image,city,address', 'workplace:id,name', 'department:id,name']);

        if (trim($this->q) !== '') {
            $jobsQuery->where('title', 'like', '%' . trim($this->q) . '%');
        }

        if (trim($this->city) !== '') {
            $city = trim($this->city);
            $jobsQuery->whereHas('branch', function ($query) use ($city) {
                $query->where('city', 'like', '%' . $city . '%')
                    ->orWhere('address', 'like', '%' . $city . '%')
                    ->orWhere('name', 'like', '%' . $city . '%');
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

        $jobs = $jobsQuery->latest()->get();

        return view('livewire.client.browse-jobs', [
            'jobs' => $jobs,
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
