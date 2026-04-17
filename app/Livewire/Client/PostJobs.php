<?php

namespace App\Livewire\Client;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PostJobs extends Component
{
    #[Layout('layouts.client')]
    public function mount(): mixed
    {
        return redirect()->route(
            Auth::check() ? 'employers.post_job' : 'employers.login'
        );
    }

    public function render()
    {
        return view('livewire.client.post-jobs');
    }
}
