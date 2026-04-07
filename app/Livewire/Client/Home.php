<?php

namespace App\Livewire\Client;

use App\Models\Branch;
use App\Models\Category;
use App\Models\RecruitmentJob;
use Livewire\Attributes\Layout;
use Livewire\Component;


class Home extends Component
{
    #[Layout('layouts.client')] // Khai báo layout ở đây
    public function render()
    {
        $jobs = RecruitmentJob::with('branch')->latest()->get();
        $branches = Branch::withCount('workplaces')->latest()->get();
        $categories = Category::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'image']);

        return view('livewire.client.home', [
            'branches' => $branches,
            'jobs' => $jobs,
            'categories' => $categories,
        ]);
    }
}
