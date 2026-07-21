<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Client\Employers\PostJob;

class PostJobLivewireTest extends TestCase
{
    public function test_generate_ai_draft(): void
    {
        Livewire::test(PostJob::class)
            ->set('ai_brief', 'Cần tuyển dev PHP Laravel kinh nghiệm 2 năm')
            ->call('generateAiDraft')
            ->assertHasNoErrors(['ai_brief']);
    }
}
