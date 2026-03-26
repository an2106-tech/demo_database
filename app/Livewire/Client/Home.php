<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout; // Thêm dòng này

class Home extends Component
{
    #[Layout('layouts.client')] // Khai báo layout ở đây
    public function render()
    {
        return view('livewire.client.home');
    }
}