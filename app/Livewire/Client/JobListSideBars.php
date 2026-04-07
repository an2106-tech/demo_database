<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Category;
use App\Models\RecruitmentJob;
use App\Models\Skill;

class JobListSideBars extends Component
{
    #[Layout('layouts.client')]
   
    public function render()
    {
        $jobs = RecruitmentJob::query()
            ->with(['branch:id,name,image,city', 'workplace:id,name'])
            ->latest()
            ->get();

        $skills = Skill::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $categories = Category::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'image']);

        return view('livewire.client.job-list-sidebar', [
            'jobs' => $jobs,
            'skills' => $skills,
            'categories' => $categories,
        ]);
    }
}
