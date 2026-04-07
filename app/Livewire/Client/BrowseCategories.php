<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;

class BrowseCategories extends Component
{
    #[Layout('layouts.client')]
    public function render()
    {
        $categories = Schema::hasTable('categories')
            ? Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon', 'image'])
            : collect();

        return view('livewire.client.browse-categories', [
            'categories' => $categories,
        ]);
    }
}
