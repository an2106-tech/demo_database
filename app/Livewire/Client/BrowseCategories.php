<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Department;

class BrowseCategories extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $departments = Department::query()
            ->withCount('jobs')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.client.browse-categories', [
            'departments' => $departments,
        ]);
    }
}
