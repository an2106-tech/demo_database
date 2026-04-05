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

        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        $hasCandidateAccount = in_array('candidate', $accountTypes, true);

        // Menu mapping:
        // - candidate + pm => candidate menu
        // - hr => employer menu by default; allow switching to candidate only if activated
        if (in_array($user->role, ['candidate', 'pm'], true)) {
            $this->type = 'candidate';

            return;
        }

        if ($user->role === 'hr') {
            $preferred = session('client_menu_type');
            if ($preferred === 'candidate' && $hasCandidateAccount) {
                $this->type = 'candidate';

                return;
            }

            $this->type = 'employer';
        }
    }

    public function switchTo(string $type): void
    {
        if (! in_array($type, ['candidate', 'employer'], true)) {
            return;
        }

        $user = Auth::user();
        if (! $user || $user->role !== 'hr') {
            return;
        }

        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        $hasCandidateAccount = in_array('candidate', $accountTypes, true);

        if ($type === 'candidate' && ! $hasCandidateAccount) {
            return;
        }

        session(['client_menu_type' => $type]);
        $this->type = $type;
    }

    public function render()
    {
        $user = Auth::user();
        $metadata = is_array($user?->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        $hasCandidateAccount = in_array('candidate', $accountTypes, true);

        return view('livewire.client.header', [
            'isEmployerHeader' => $this->type === 'employer',
            'showRoleSwitcher' => (bool) $user && $user->role === 'hr' && $hasCandidateAccount,
        ]);
    }
}
