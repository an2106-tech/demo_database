<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Contact extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.pages.contact');
    }
}
