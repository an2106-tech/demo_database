<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    public function render()
    {
        return <<<'HTML'
            <button wire:click="logout" type="button">Đăng xuất</button>
        HTML;
    }
}