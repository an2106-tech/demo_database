<?php

namespace App\Livewire\Client\pages;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Login extends Component
{
    #[Layout('layouts.client')]
    public string $role = 'candidate';

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected array $queryString = [
        'role' => ['except' => 'candidate'],
    ];

    public function mount(): void
    {
        $requestedRole = request()->query('role');
        $this->role = $this->normalizeRole(is_string($requestedRole) ? $requestedRole : '');
    }

    public function setRole(string $role): void
    {
        $this->resetErrorBag();
        $this->role = $this->normalizeRole($role);
    }

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
        } elseif ($user->role === 'candidate') {
            session(['client_menu_type' => 'candidate']);
        } else {
            session()->forget('client_menu_type');
        }

        return redirect()->intended(route('home'));
    }

    private function normalizeRole(string $role): string
    {
        $role = strtolower(trim($role));

        if ($role === 'hr') {
            return 'employer';
        }

        return in_array($role, ['candidate', 'employer'], true) ? $role : 'candidate';
    }

    public function render()
    {
        return view('livewire.client.pages.login');
    }
}
