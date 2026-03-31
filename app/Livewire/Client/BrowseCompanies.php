<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Branch;

class BrowseCompanies extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $branches = Branch::latest()->get();
        return view('livewire.client.browse-companies', [
            'branches' => $branches
        ]);
    }
}
