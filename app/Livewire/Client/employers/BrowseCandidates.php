<?php

namespace App\Livewire\Client\employers;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Candidate;

class BrowseCandidates extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        $candidates = Candidate::latest()->get();
        return view('livewire.client.employers.browse_candidates', [
            'candidates' => $candidates,
        ]);
    }
}
