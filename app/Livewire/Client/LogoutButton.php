<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout()
    {
        $redirectRoute = request()->routeIs('employers.*') ? 'employers.login' : 'candidates.login';

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }

    public function render()
    {
        return view('livewire.client.logout-button');
    }
}
