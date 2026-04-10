<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CompanyProfile extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.company-profile');
    }
}
