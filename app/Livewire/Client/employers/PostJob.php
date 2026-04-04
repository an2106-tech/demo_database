<?php

namespace App\Livewire\Client\Employers;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PostJob extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        return view('livewire.client.employers.post_job');
    }
}
