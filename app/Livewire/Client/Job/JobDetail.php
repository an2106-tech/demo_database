<?php

namespace App\Livewire\Client\Job;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\VietnamProvince;
use App\Models\RecruitmentJob;

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

    #[Layout('layouts.employer')]
    public function render()
    {
        return view('livewire.client.job.job-detail', [
            'job' => $this->job
        ]);
    }
}
