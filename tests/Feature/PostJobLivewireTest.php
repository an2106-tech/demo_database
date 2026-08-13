<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Client\Employers\PostJob;

class PostJobLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_ai_draft(): void
    {
        $this->mock(\App\Services\AiMatchingService::class, function ($mock) {
            $mock->shouldReceive('cleanJobBrief')->andReturnUsing(fn($b) => $b);
            $mock->shouldReceive('draftRecruitmentJob')->andReturn(['title' => 'Test']);
        });

        Livewire::test(PostJob::class)
            ->set('ai_brief', 'Cần tuyển dev PHP Laravel kinh nghiệm 2 năm')
            ->call('generateAiDraft')
            ->assertHasNoErrors(['ai_brief']);
    }
}
