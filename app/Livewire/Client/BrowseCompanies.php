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
        $branches = Branch::query()
            ->latest()
            ->get(['id', 'name', 'image']);

        $branchesByLetter = $branches->groupBy(function (Branch $branch) {
            $name = (string) ($branch->name ?? '');
            $firstChar = function_exists('mb_substr')
                ? mb_substr($name, 0, 1, 'UTF-8')
                : substr($name, 0, 1);

            return strtoupper($firstChar !== '' ? $firstChar : '#');
        });

        $letters = range('A', 'Z');

        return view('livewire.client.browse-companies', [
            'branches' => $branches,
            'branchesByLetter' => $branchesByLetter,
            'letters' => $letters,
        ]);
    }
}
