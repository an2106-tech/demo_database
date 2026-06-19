<?php

namespace App\Livewire\Client;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Enums\StatusRecruitmentJobsEnum;
use App\Models\Post;
use App\Models\User;
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

        $publishedJobsQuery = RecruitmentJob::query()
            ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->where(function ($q) use ($now) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', $now);
            });

        $jobs = (clone $publishedJobsQuery)
            ->with(['branch', 'department'])
            ->latest()
            ->take(20)
            ->get();

        $featuredJobs = (clone $publishedJobsQuery)
            ->with(['branch', 'department'])
            ->latest()
            ->take(6)
            ->get();

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
                    ->select(['id', 'branch_id', 'title', 'slug', 'salary_range', 'deadline', 'created_at']),
            ])
            ->latest()
            ->get();

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $categories = Schema::hasTable('categories')
            ? Category::query()
                ->orderBy('name')
                ->withCount([
                    'recruitmentJobs as recruitment_jobs_count' => fn ($query) => $query
                        ->where('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
                        ->where(function ($q) use ($now) {
                            $q->whereNull('deadline')
                                ->orWhere('deadline', '>=', $now);
                        }),
                ])
                ->get(['id', 'name', 'slug', 'icon', 'image'])
            : collect();
        $posts = Post::latest()->take(6)->get();

        $publishedJobsCount = (clone $publishedJobsQuery)->count();
        $activeBranchesCount = Branch::query()->where('is_active', true)->count();
        $departmentsCount = Department::query()->count();
        $candidatesCount = Candidate::query()->count();
        $applicationsCount = Application::query()->count();
        $usersCount = User::query()->count();

        return view('livewire.client.home', [
            'branches' => $branches,
            'jobs' => $jobs,
            'featuredJobs' => $featuredJobs,
            'departments' => $departments,
            'categories' => $categories,
            'posts' => $posts,
            'stats' => [
                'published_jobs' => $publishedJobsCount,
                'active_branches' => $activeBranchesCount,
                'departments' => $departmentsCount,
                'candidates' => $candidatesCount,
                'applications' => $applicationsCount,
                'users' => $usersCount,
            ],
        ]);
    }
}
