<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Login extends Component
{
    #[Layout('layouts.client')]

    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['boolean'],
        ]);

        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $credentials['remember'] ?? false
        )) {
            $this->addError('email', 'Email hoặc mật khẩu không đúng.');
            return;
        }

        request()->session()->regenerate();

        // Nếu cần chặn tài khoản bị khóa
        if (! Auth::user()->is_active) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            $this->addError('email', 'Tài khoản đã bị khóa.');
            return;
        }

        return redirect()->intended(route('home'));
    }

    public function render()
    {
        return view('livewire.client.login');
    }
}
