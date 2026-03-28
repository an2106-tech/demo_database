<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidateDashboard extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-dashboard');
    }
}
