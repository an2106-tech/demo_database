<?php

namespace App\Livewire\Client\Employers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Branch;

class SingleCompany extends Component
{
    #[Layout('layouts.client')] 

    public function render()
    {
        $branches = Branch::find(1);
        return view('livewire.client.employers.single_company', [
            'branches' => $branches,
        ]);
    }
}
