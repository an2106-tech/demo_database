<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $contextRole = 'candidate';

    public string $email = '';

    public function mount(): void
    {
        $role = strtolower((string) request()->query('role', 'candidate'));
        $this->contextRole = $role === 'employer' ? 'employer' : 'candidate';
    }

    public function sendResetLink(): mixed
    {
        $data = $this->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => trim(strtolower($data['email'])),
        ]);

        if ($status === Password::RESET_THROTTLED) {
            $this->addError('email', 'Vui lòng chờ trước khi gửi lại email đặt lại mật khẩu.');

            return null;
        }

        session()->flash('status', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu.');

        return null;
    }

    public function render()
    {
        /** @var mixed $view */
        $view = view('livewire.client.pages.forgot-password', [
            'contextRole' => $this->contextRole,
        ]);

        return $view->layout('layouts.auth', [
            'authTitle' => 'Quên mật khẩu',
            'authContextRole' => $this->contextRole,
        ]);
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
        ];
    }
}
