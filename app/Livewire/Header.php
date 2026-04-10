<?php

namespace App\Livewire;

use App\Services\CandidateAccountService;
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

        $hasCandidateAccount = app(CandidateAccountService::class)->hasCandidateAccount($user);

        if ($user->role === 'candidate') {
            $this->type = 'candidate';

            return;
        }

        if (! in_array($user->role, ['hr', 'admin'], true)) {
            $this->type = 'candidate';

            return;
        }

        $preferred = session('client_menu_type');
        if ($preferred === 'candidate' && $hasCandidateAccount) {
            $this->type = 'candidate';

            return;
        }

        $this->type = 'employer';
    }

    public function switchTo(string $type)
    {
        if (! in_array($type, ['candidate', 'employer'], true)) {
            return;
        }

        $user = Auth::user();
        if (! $user || ! in_array($user->role, ['hr', 'admin'], true)) {
            return;
        }

        if ($type === 'candidate') {
            app(CandidateAccountService::class)->activateFor($user);
        }

        session(['client_menu_type' => $type]);
        $this->type = $type;

        return $type === 'candidate'
            ? $this->redirectRoute('candidates.browse_job', navigate: true)
            : $this->redirectRoute('employers.dashboard', navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $hasCandidateAccount = (bool) $user && app(CandidateAccountService::class)->hasCandidateAccount($user);

        $hasEmployerAccount = false;
        if ($user) {
            if (in_array($user->role, ['hr', 'admin'], true)) {
                $hasEmployerAccount = true;
            } else {
                $metadata = is_array($user->metadata) ? $user->metadata : [];
                $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
                $hasEmployerAccount = in_array('employer', $accountTypes, true);
            }
        }

        $isEmployerHeader = $this->type === 'employer';
        $showCandidateMenu = ! $user
            ? true
            : (! $isEmployerHeader && $hasCandidateAccount);
        $showEmployerMenu = ! $user
            ? true
            : ($isEmployerHeader && $hasEmployerAccount);

        return view('livewire.client.header', [
            'isEmployerHeader' => $isEmployerHeader,
            'showRoleSwitcher' => (bool) $user && in_array($user->role, ['hr', 'admin'], true),
            'canCandidateMenu' => $hasCandidateAccount,
            'canEmployerMenu' => $hasEmployerAccount,
            'showCandidateMenu' => $showCandidateMenu,
            'showEmployerMenu' => $showEmployerMenu,
        ]);
    }
}
