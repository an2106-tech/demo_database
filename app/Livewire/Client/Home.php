<?php

namespace App\Livewire\Client;

use App\Models\Branch;
use App\Models\Category;
use App\Models\RecruitmentJob;
use App\Enums\StatusRecruitmentJobsEnum;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;


class Home extends Component
{
    #[Layout('layouts.client')] // Khai báo layout ở đây
    public function render()
    {
        $now = Carbon::now();
        $jobs = RecruitmentJob::with('branch')->latest()->get();

        $branches = Branch::query()
            ->where('is_active', true)
            ->whereHas('recruitmentJobs', function ($query) use ($now) {
                $query->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    });
            })
            ->select(['id', 'name', 'image', 'city', 'address', 'is_active'])
            ->withCount([
                'recruitmentJobs as published_jobs_count' => fn ($query) => $query
                    ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    }),
            ])
            ->with([
                'recruitmentJobs' => fn ($query) => $query
                    ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('deadline')
                            ->orWhere('deadline', '>=', $now);
                    })
                    ->orderByDesc('created_at')
                    ->select(['id', 'branch_id', 'title', 'salary_range', 'deadline', 'created_at']),
            ])
            ->latest()
            ->get();
        $categories = Schema::hasTable('categories')
            ? Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'image'])
            : collect();

        return view('livewire.client.home', [
            'branches' => $branches,
            'jobs' => $jobs,
            'categories' => $categories,
        ]);
    }
}
