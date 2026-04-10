<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;

class CompanyProfile extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        return view('livewire.client.employers.company_profile');
    }
}
