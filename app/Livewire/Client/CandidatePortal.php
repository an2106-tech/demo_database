<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidatePortal extends Component
{
    #[Layout('layouts.candidate')]
    public function mount(): void
    {
    }

    public function render()
    {
        return view('livewire.client.candidate-portal');
    }
}
