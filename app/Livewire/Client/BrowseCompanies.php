<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Branch;
use App\Enums\StatusRecruitmentJobsEnum;

class BrowseCompanies extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $branches = Branch::query()
            ->withCount([
                'recruitmentJobs as published_jobs_count' => fn ($query) => $query
                    ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value),
            ])
            ->with([
                'recruitmentJobs' => fn ($query) => $query
                    ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    ->orderByDesc('created_at')
                    ->select(['id', 'branch_id', 'title', 'salary_range', 'deadline', 'created_at']),
            ])
            ->latest()
            ->get(['id', 'name', 'image', 'city', 'address', 'is_active']);

        $branchesByLetter = $branches->groupBy(function (Branch $branch) {
            $name = (string) ($branch->name ?? '');
            $firstChar = function_exists('mb_substr')
                ? mb_substr($name, 0, 1, 'UTF-8')
                : substr($name, 0, 1);

            return strtoupper($firstChar !== '' ? $firstChar : '#');
        });

        $letters = range('A', 'Z');

        return view('livewire.client.browse-companies', [
            'branches' => $branches,
            'branchesByLetter' => $branchesByLetter,
            'letters' => $letters,
        ]);
    }
}
