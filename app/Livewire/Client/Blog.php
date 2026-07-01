<?php

namespace App\Livewire\Client;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Bài viết & Career Tips')]
class Blog extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.client')]
    public function render()
    {
        $searchTerm = trim($this->search);

        $posts = Post::query()
            ->when($searchTerm !== '', function ($query) use ($searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('excerpt', 'like', '%' . $searchTerm . '%')
                        ->orWhere('content', 'like', '%' . $searchTerm . '%');
                });
            })
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(6);

        $latestPosts = Post::query()
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(4)
            ->get();

        return view('livewire.client.pages.blog', [
            'posts' => $posts,
            'latestPosts' => $latestPosts,
        ]);
    }
}
