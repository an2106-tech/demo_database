<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Header extends Component
{
    public string $type = 'candidate';

    public function mount(string $type = 'candidate')
    {
        $this->type = in_array($type, ['candidate', 'employer'], true) ? $type : 'candidate';

        $user = Auth::user();
        if (! $user) {
            return;
        }

        $preferred = session('client_menu_type');
        if (! in_array($preferred, ['candidate', 'employer'], true)) {
            $preferred = null;
        }

        // Defaults per authenticated user:
        // - hr => employer menu
        // - candidate/pm => candidate menu
        $defaultType = $user->role === 'hr' ? 'employer' : 'candidate';
        if (! in_array($user->role, ['candidate', 'pm', 'hr'], true)) {
            $defaultType = 'candidate';
        }

        $this->type = $preferred ?? $defaultType;
    }

    public function switchTo(string $type): void
    {
        if (! in_array($type, ['candidate', 'employer'], true)) {
            return;
        }

        session(['client_menu_type' => $type]);
        $this->type = $type;
    }

    public function render()
    {
        $user = Auth::user();

        $isHr = $user?->role === 'hr';
        $metadata = is_array($user?->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        $hasCandidateAccount = $user && (
            in_array($user->role, ['candidate', 'pm'], true) ||
            ($metadata['account_type'] ?? null) === 'candidate' ||
            in_array('candidate', $accountTypes, true)
        );

        return view('livewire.client.header', [
            'isEmployerHeader' => $this->type === 'employer',
            'showRoleSwitcher' => (bool) $user && $isHr,
            'candidateActivationNeeded' => (bool) $user && $isHr && $this->type === 'candidate' && ! $hasCandidateAccount,
        ]);
    }
}
