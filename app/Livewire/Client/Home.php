<?php

namespace App\Livewire\Client;

use App\Models\Branch;
use App\Models\Category;
use App\Models\RecruitmentJob;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;


class Home extends Component
{
    #[Layout('layouts.client')] // Khai báo layout ở đây
    public function render()
    {
        $jobs = RecruitmentJob::with('branch')->latest()->get();
        $branches = Branch::withCount('workplaces')->latest()->get();
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
