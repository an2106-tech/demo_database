<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;

class ChangePassword extends Component
{
    public function render()
    {
        if (request()->routeIs('candidates.*')) {
            return view('livewire.client.change-password')
                ->layout('layouts.client');
        }

        return view('livewire.client.employers.change_password')
            ->layout('layouts.employer');
    }
}
