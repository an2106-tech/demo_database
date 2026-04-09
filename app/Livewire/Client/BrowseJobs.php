<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\RecruitmentJob;
use App\Enums\StatusRecruitmentJobsEnum;
use Carbon\Carbon;

class BrowseJobs extends Component
{
    #[Layout('layouts.client')]

    public string $display = 'grid';

    protected array $queryString = [
        'display' => ['except' => 'grid'],
    ];

    public function mount(): void
    {
        if (! request()->has('display') && request()->has('view')) {
            $requestedView = request()->query('view');
            if (is_string($requestedView)) {
                $this->display = $requestedView;
            }
        }

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
        $jobs = RecruitmentJob::query()
            ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->where(function ($q) use ($now) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', $now);
            })
            ->with(['branch:id,name,image,city,address', 'workplace:id,name'])
            ->latest()
            ->get();

        return view('livewire.client.browse-jobs', [
            'jobs' => $jobs
        ]);
    }
}
