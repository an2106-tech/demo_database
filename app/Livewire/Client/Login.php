<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): mixed
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

            return null;
        }

        request()->session()->regenerate();

        $user = Auth::user();
        if (! $user?->is_active) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            $this->addError(
                'email',
                $user?->role === 'hr'
                    ? 'Tài khoản nhà tuyển dụng đang chờ super admin duyệt.'
                    : 'Tài khoản đã bị khóa.'
            );

            return null;
        }

        if ($user->role === 'hr') {
            session(['client_menu_type' => 'employer']);

            return redirect()->intended(route('employers.dashboard'));
        }

        if ($user->role === 'candidate') {
            session(['client_menu_type' => 'candidate']);

            return redirect()->intended(route('candidates.candidate_dashboard'));
        }

        session()->forget('client_menu_type');

        return redirect()->intended(route('home'));
    }

    public function render()
    {
        /** @var mixed $view */
        $view = view('livewire.client.pages.login');

        return $view->layout('layouts.client');
    }
}
