<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;

class ManageJobs extends Component
{
    #[Layout('layouts.client')]

    public function render()
    {
        return view('livewire.client.employers.manage_jobs');
    }
}
