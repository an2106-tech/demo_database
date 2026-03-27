<?php

namespace App\Livewire\Client\employers;

use Livewire\Component;
use Livewire\Attributes\Layout;

class BrowseCandidates extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.employers.browse_candidates');
    }
}
