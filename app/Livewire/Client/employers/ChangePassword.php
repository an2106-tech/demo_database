<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;

class ChangePassword extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.employers.change_password');
    }
}
