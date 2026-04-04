<?php

namespace App\Livewire\Client\Employers;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Branch;

class SingleCompany extends Component
{
    #[Layout('layouts.employer')]
    public function render()
    {
        $branches = Branch::find(1);
        return view('livewire.client.employers.single_company', [
            'branches' => $branches,
        ]);
    }
}
