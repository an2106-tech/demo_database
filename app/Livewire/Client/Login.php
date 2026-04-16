<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
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

            return redirect()->intended(route('employers.dashboard'));
        }

        if ($user->role === 'candidate') {
            session(['client_menu_type' => 'candidate']);

            return redirect()->intended(route('candidates.candidate_dashboard'));
        }

        session()->forget('client_menu_type');

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
        $layout = match ($this->role) {
            'employer' => 'layouts.employer',
            'candidate' => 'layouts.candidate',
            default => 'layouts.client',
        };

        /** @var mixed $view */
        $view = view('livewire.client.pages.login');

        return $view->layout($layout);
    }
}
