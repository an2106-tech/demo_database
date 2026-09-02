<?php

namespace App\Livewire\Client\Employers;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Branch;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SingleCompany extends Component
{
    public ?Branch $branch = null;

    public function mount(?Branch $branch = null): void
    {
        if ($branch?->exists && ! $branch->is_active) {
            abort(404);
        }

        $this->branch = $branch?->exists
            ? $branch
            : Branch::query()
                ->where('is_active', true)
                ->whereHas('recruitmentJobs', $this->publishedJobsScope())
                ->latest()
                ->first();

        abort_unless($this->branch, 404);

        $this->branch->load(['recruitmentJobs' => function ($query): void {
            ($this->publishedJobsScope())($query);
            $query->with(['workplace', 'department'])->latest();
        }]);
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.employers.single_company', [
            'branch' => $this->branch,
            'jobs' => $this->branch?->recruitmentJobs ?? collect(),
        ]);
    }

    private function publishedJobsScope(): \Closure
    {
        return function ($query): void {
            $now = Carbon::now();

            $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                ->where(function ($query) use ($now): void {
                    $query->whereNull('deadline')->orWhere('deadline', '>=', $now);
                });
        };
    }
}
