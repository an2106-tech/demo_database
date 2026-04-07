<?php

namespace App\Livewire\Client\Job;

use Livewire\Component;
use Livewire\Attributes\Layout;

class JobDetail extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        return view('livewire.client.job.job-detail');
    }
}
