<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

class BrowseJobs extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.browse-jobs');
    }
}
