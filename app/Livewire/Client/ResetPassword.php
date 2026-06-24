<?php

namespace App\Livewire\Client;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Component;

class ResetPassword extends Component
{
    public string $contextRole = 'candidate';

    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = trim(strtolower((string) request()->query('email', '')));

        $role = strtolower((string) request()->query('role', 'candidate'));
        $this->contextRole = $role === 'employer' ? 'employer' : 'candidate';
    }

    public function resetPassword(): mixed
    {
        $data = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            [
                'email' => trim(strtolower($data['email'])),
                'password' => $data['password'],
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                $this->contextRole = in_array($user->role, ['director', 'hr', 'pm'], true)
                    ? 'employer'
                    : 'candidate';
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->addError('email', $status === Password::INVALID_TOKEN
                ? 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.'
                : 'Không thể đặt lại mật khẩu cho email này.');

            return null;
        }

        $loginRoute = $this->contextRole === 'employer'
            ? 'employers.login'
            : 'candidates.login';

        return redirect()
            ->route($loginRoute)
            ->with('status', 'Mật khẩu đã được cập nhật. Vui lòng đăng nhập lại.');
    }

    public function render()
    {
        /** @var mixed $view */
        $view = view('livewire.client.pages.reset-password', [
            'contextRole' => $this->contextRole,
        ]);

        return $view->layout('layouts.auth', [
            'authTitle' => 'Đặt lại mật khẩu',
            'authContextRole' => $this->contextRole,
        ]);
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }
}
