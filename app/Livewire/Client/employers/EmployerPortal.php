<?php

namespace App\Livewire\Client\Employers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Category;
use App\Models\RecruitmentJob;
use Livewire\Attributes\Layout;
use Livewire\Component;

class EmployerPortal extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        $recentJobs = RecruitmentJob::query()
            ->where('status', 'active')
            ->with(['branch', 'department'])
            ->latest()
            ->limit(6)
            ->get();

        $categories = Category::query()
            ->where('status', 'active')
            ->limit(8)
            ->get();

        $branches = Branch::query()
            ->where('is_active', true)
            ->limit(4)
            ->get();

        $totalJobs = RecruitmentJob::where('status', 'active')->count();
        $totalApplications = Application::whereHas('job', function ($query) {
            $query->where('status', 'active');
        })->count();
        $totalBranches = Branch::where('is_active', true)->count();

        return view('livewire.client.employers.portal', [
            'recentJobs' => $recentJobs,
            'categories' => $categories,
            'branches' => $branches,
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'totalBranches' => $totalBranches,
        ]);
    }
}
