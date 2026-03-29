<?php

namespace App\Livewire\Client\pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Login extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.pages.login');
    }
}
