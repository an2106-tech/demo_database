<?php

namespace App\Livewire\Client\Job;

use Livewire\Component;
use App\Enums\VietnamProvince;
use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Route;

class JobDetail extends Component
{
    public $id;
    public $job;

    public function mount($id)
    {
        $this->id = $id;

        $this->job = RecruitmentJob::with(['branch', 'workplace', 'department', 'skills'])
            ->findOrFail($id);
    }

    public function render()
    {
        $routeName = Route::currentRouteName();
        $layout = str_starts_with((string) $routeName, 'employers.')
            ? 'layouts.employer'
            : 'layouts.client';

        return view('livewire.client.job.job-detail', [
            'job' => $this->job
        ])->layout($layout);
    }
}
