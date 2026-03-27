<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;

class EmployersDashboard extends Component
{
    #[Layout('layouts.client')]

    public function render()
    {
        return view('livewire.client.employers.employers_dashboard');
    }
}
