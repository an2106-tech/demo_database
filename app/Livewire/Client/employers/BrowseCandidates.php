<?php

namespace App\Livewire\Client\employers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Candidate;

class BrowseCandidates extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        $candidates = Candidate::latest()->get();
        return view('livewire.client.employers.browse_candidates', [
            'candidates' => $candidates,
        ]);
    }
}
