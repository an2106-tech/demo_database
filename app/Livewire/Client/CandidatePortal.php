<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidatePortal extends Component
{
    #[Layout('layouts.candidate')]

    public function mount(): void
    {
        // Nếu employer (HR) đang đăng nhập, chuyển hướng đến login của nhà tuyển dụng
        if (Auth::check() && Auth::user()->role === 'hr') {
            redirect()->route('auth.login', ['role' => 'employer'])->send();
        }
    }

    public function render()
    {
        return view('livewire.client.candidate-portal');
    }
}
