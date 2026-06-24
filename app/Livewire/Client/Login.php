<?php

namespace App\Livewire\Client;

use App\Models\User;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use RuntimeException;

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

        if ($routeName && ($user = Auth::user())) {
            $this->redirectAuthenticatedUser($user);
        }
    }

    public function login(): mixed
    {
        if ($user = Auth::user()) {
            return $this->redirectAuthenticatedUser($user);
        }

        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['boolean'],
        ]);

        $email = trim(strtolower($credentials['email']));
        $password = (string) $credentials['password'];
        $remember = (bool) ($credentials['remember'] ?? false);

        try {
            $authenticated = Auth::attempt(['email' => $email, 'password' => $password], $remember);
        } catch (RuntimeException) {
            $authenticated = false;
        }

        if (! $authenticated) {
            $user = User::query()->where('email', $email)->first();

            if (! $this->attemptLegacyLogin($user, $password, $remember)) {
                $this->addError('email', 'Email hoặc mật khẩu không đúng.');

                return null;
            }
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $user = Auth::user();

        if (! $user?->is_active) {
            $approvalStatus = is_array($user?->metadata) ? ($user->metadata['approval_status'] ?? null) : null;
            $inactiveMessage = match ($approvalStatus) {
                'pending' => 'Tài khoản nhà tuyển dụng đang chờ duyệt.',
                'rejected' => 'Tài khoản nhà tuyển dụng đã bị từ chối. Vui lòng liên hệ quản trị viên.',
                default => $user?->role === 'hr'
                    ? 'Tài khoản nhà tuyển dụng đang chờ duyệt.'
                    : 'Tài khoản đã bị khóa.',
            };

            Auth::logout();

            if (request()->hasSession()) {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }

            $this->addError('email', $inactiveMessage);

            return null;
        }

        if ($user->role === 'admin') {
            session()->forget('client_menu_type');

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

        if (in_array($user->role, ['director', 'hr', 'pm'], true)) {
            $candidateAccounts->setPreferredAccountType($user, 'employer');
            session(['client_menu_type' => 'employer']);

            return $this->redirect(route('employers.dashboard'));
        }

        session()->forget('client_menu_type');

        return $this->redirect(route('home'));
    }

    private function redirectAuthenticatedUser(User $user): mixed
    {
        if ($user->role === 'admin') {
            session()->forget('client_menu_type');

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

        if (in_array($user->role, ['director', 'hr', 'pm'], true)) {
            $candidateAccounts->setPreferredAccountType($user, 'employer');
            session(['client_menu_type' => 'employer']);

            return $this->redirect(route('employers.dashboard'));
        }

        session()->forget('client_menu_type');

        return $this->redirect(route('home'));
    }

    private function attemptLegacyLogin(?User $user, string $password, bool $remember): bool
    {
        if (! $user) {
            return false;
        }

        $storedPassword = (string) $user->password;
        $isBcrypt = str_starts_with($storedPassword, '$2y$')
            || str_starts_with($storedPassword, '$2a$')
            || str_starts_with($storedPassword, '$2b$');

        if ($isBcrypt || ! hash_equals($storedPassword, md5($password))) {
            return false;
        }

        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();

        Auth::login($user, $remember);

        return true;
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

    protected function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
        ];
    }
}
