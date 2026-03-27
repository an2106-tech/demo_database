<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;


class PostJob extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.employers.post_job');
    }
}
