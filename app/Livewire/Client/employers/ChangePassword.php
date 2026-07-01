<?php

namespace App\Livewire\Client\Employers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ChangePassword extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        $this->validate();

        $user = Auth::user();

        if (! $user || ! Hash::check($this->current_password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($this->password),
        ])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('status', 'Mật khẩu đã được cập nhật.');
    }

    public function render()
    {
        if (request()->routeIs('candidates.*')) {
            return view('livewire.client.change-password')
                ->layout('layouts.client');
        }

        return view('livewire.client.employers.change_password')
            ->layout('layouts.employer');
    }

    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
