<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\RecruitmentJob;

class BrowseJobs extends Component
{
    #[Layout('layouts.client')]

    public function render()
    {
        $jobs = RecruitmentJob::query()
            ->with(['branch:id,name,image,city', 'workplace:id,name'])
            ->latest()
            ->get();

        return view('livewire.client.browse-jobs', [
            'jobs' => $jobs
        ]);
    }
}