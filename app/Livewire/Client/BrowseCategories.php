<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Category;
use App\Enums\StatusRecruitmentJobsEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class BrowseCategories extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $now = Carbon::now();

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

        return view('livewire.client.browse-categories', [
            'categories' => $categories,
        ]);
    }
}
