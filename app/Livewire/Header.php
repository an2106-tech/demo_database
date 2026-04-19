<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Header extends Component
{
    public string $type = 'candidate';

    public function mount(string $type = 'candidate'): void
    {
        $this->type = in_array($type, ['candidate', 'employer'], true) ? $type : 'candidate';
    }

    public function render()
    {
        $user = Auth::user();
        $metadata = is_array($user?->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];

        $canEmployerAccess = (bool) $user && (
            in_array($user->role, ['hr', 'admin', 'director'], true)
            || in_array('employer', $accountTypes, true)
        );

        $canCandidateAccess = (bool) $user && (
            $user->role === 'candidate'
            || in_array('candidate', $accountTypes, true)
        );

        return $this->type === 'employer'
            ? view('livewire.client.header-employer', ['canEmployerAccess' => $canEmployerAccess])
            : view('livewire.client.header-candidate', ['canCandidateAccess' => $canCandidateAccess]);
    }
}
