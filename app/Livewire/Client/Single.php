<?php

namespace App\Livewire\Client;

use App\Models\Post;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Single extends Component
{
    #[Layout('layouts.client')] 
    public function render()
    {
         $posts = Post::latest()->get();
        return view('livewire.client.pages.single',[
            'posts' => $posts
        ]);
    }
}
