<?php

namespace App\Livewire\Client\Employers;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.employer')]
class CompanyProfile extends Component
{
    public $branch = null;
    public $canEdit = false;

    public function mount()
    {
        $user = Auth::user();

        if ($user && in_array($user->role, ['hr', 'director'])) {
            $this->branch = Branch::with(['workplaces'])
                ->find($user->branch_id);
            $this->canEdit = $user->role === 'director';
        }
    }

    public function render()
    {
        return view('livewire.client.employers.company_profile', [
            'branch' => $this->branch,
            'canEdit' => $this->canEdit,
        ]);
    }
}
