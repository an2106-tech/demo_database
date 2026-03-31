<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Candidate;

class ManageCandidate extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $candidates = Candidate::latest()->get();
        return view('livewire.client.employers.manage_candidate', ['candidates' => $candidates]);
    }
}
