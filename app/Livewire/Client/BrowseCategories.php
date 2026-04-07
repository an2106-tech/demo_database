<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Category;

class BrowseCategories extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $categories = Category::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'image']);

        return view('livewire.client.browse-categories', [
            'categories' => $categories,
        ]);
    }
}
