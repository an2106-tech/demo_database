<?php

namespace App\Livewire\Client\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

class About extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.pages.about');
    }
}
