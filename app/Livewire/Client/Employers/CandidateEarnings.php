<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;

class CandidateEarnings extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.employers.candidate_earnings');
    }
}
