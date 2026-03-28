<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

class SubmitResume extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.submit-resume');
    }
}
