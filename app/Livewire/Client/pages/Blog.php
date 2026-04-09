<?php

namespace App\Livewire\Client\pages;

use App\Models\Post;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Blog extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
       $posts = Post::latest()->get();
        return view('livewire.client.pages.blog', [
            'posts' => $posts
        ]);
    }
}
