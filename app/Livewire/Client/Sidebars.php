<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Category;
use App\Models\RecruitmentJob;
use App\Models\Skill;
use Illuminate\Support\Facades\Schema;

class Sidebars extends Component
{
    #[Layout('layouts.client')]

    public function render()
    {
        $jobs = RecruitmentJob::query()
            ->with(['branch:id,name,image,city', 'workplace:id,name'])
            ->latest()
            ->get();

        $skills = Schema::hasTable('skills')
            ? Skill::query()
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        $categories = Schema::hasTable('categories')
            ? Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'image'])
            : collect();

        return view('livewire.client.sidebar', [
            'jobs' => $jobs,
            'skills' => $skills,
            'categories' => $categories,
        ]);
    }
}
