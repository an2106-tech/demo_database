<?php

namespace App\Livewire\Client;

use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $contextRole = 'candidate';

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        $routeName = request()->route()?->getName();

        $this->contextRole = $routeName === 'employers.login' ? 'employer' : 'candidate';
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

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $user = Auth::user();
        if (! $user?->is_active) {
            Auth::logout();
            if (request()->hasSession()) {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }
            $this->addError(
                'email',
                $user?->role === 'hr'
                    ? 'Tài khoản nhà tuyển dụng đang chờ super admin duyệt.'
                    : 'Tài khoản đã bị khóa.'
            );

            return null;
        }

        if ($user->role === 'admin') {
            return $this->redirect('/admin');
        }

        $candidateAccounts = app(CandidateAccountService::class);

        if ($user->role === 'candidate') {
            $candidateAccounts->setPreferredAccountType($user, 'candidate');
            session(['client_menu_type' => 'candidate']);

            return $this->redirect(route('candidates.candidate_dashboard'));
        }

        if ($candidateAccounts->hasCandidateAccount($user) && $this->contextRole === 'candidate') {
            $candidateAccounts->setPreferredAccountType($user, 'candidate');
            session(['client_menu_type' => 'candidate']);

            return $this->redirect(route('candidates.candidate_dashboard'));
        }

        if ($user->role === 'director') {
            $candidateAccounts->setPreferredAccountType($user, 'employer');
            session(['client_menu_type' => 'employer']);

            return $this->redirect(route('employers.dashboard'));
        }

        if ($user->role === 'hr') {
            $candidateAccounts->setPreferredAccountType($user, 'employer');
            session(['client_menu_type' => 'employer']);

            return $this->redirect(route('employers.dashboard'));
        }

        session()->forget('client_menu_type');

        return $this->redirect(route('home'));
    }

    public function render()
    {
        /** @var mixed $view */
        $view = view('livewire.client.pages.login', [
            'contextRole' => $this->contextRole,
        ]);

        return $view->layout('layouts.auth', [
            'authTitle' => 'Đăng nhập',
            'authContextRole' => $this->contextRole,
        ]);
    }
}
