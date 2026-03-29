<?php

namespace App\Livewire\Client\pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Blog extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
        return view('livewire.client.pages.blog');
    }
}
