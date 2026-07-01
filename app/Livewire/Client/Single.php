<?php

namespace App\Livewire\Client;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Chi tiết bài viết')]
class Single extends Component
{
    public ?string $postIdentifier = null;

    public function mount(?string $post = null): void
    {
        $this->postIdentifier = $post;
    }

    #[Layout('layouts.client')]
    public function render()
    {
        $selectedPost = Post::query()
            ->when($this->postIdentifier, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('slug', $this->postIdentifier)
                        ->orWhere('id', $this->postIdentifier);
                });
            })
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->first();

        $relatedPosts = Post::query()
            ->when($selectedPost, fn ($query) => $query->whereKeyNot($selectedPost->id))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(4)
            ->get();

        return view('livewire.client.pages.single', [
            'post' => $selectedPost,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
