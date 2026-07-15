<?php

namespace Tests\Feature;

use App\Livewire\AiChatbox;
use App\Models\User;
use App\Services\AiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AiChatboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_use_chatbox(): void
    {
        Livewire::test(AiChatbox::class, ['audience' => 'candidate'])
            ->assertSet('enabled', false)
            ->assertDontSee('AI Career Assistant');
    }

    public function test_candidate_chat_is_kept_in_component_state_without_ai_tables(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);

        $this->mock(AiChatService::class, function ($mock): void {
            $mock->shouldReceive('reply')
                ->once()
                ->withArgs(fn (User $user, string $audience, string $question, array $history): bool => $audience === 'candidate' && $question === 'Check my application' && $history === [])
                ->andReturn([
                    'answer' => 'Your application is at the interview stage.',
                    'sources' => [['label' => 'Laravel Developer application', 'url' => '/candidates/applications/1']],
                    'suggestions' => ['When is my interview?'],
                    'provider' => 'local',
                    'model' => null,
                    'intent' => 'candidate_application_status',
                ]);
        });

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'candidate'])
            ->assertSet('enabled', true)
            ->assertSeeHtml('ai-chatbox__launcher-orb')
            ->call('toggle')
            ->set('message', 'Check my application')
            ->call('sendMessage')
            ->assertSet('messages', fn (array $messages): bool => count($messages) === 2
                && $messages[0]['role'] === 'user'
                && $messages[1]['intent'] === 'candidate_application_status')
            ->assertSee('Your application is at the interview stage.')
            ->assertSee('Laravel Developer application')
            ->assertSee('When is my interview?');

        $this->assertFalse(Schema::hasTable('ai_conversations'));
        $this->assertFalse(Schema::hasTable('ai_messages'));
    }

    public function test_recent_component_messages_are_sent_as_context(): void
    {
        $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);

        $this->mock(AiChatService::class, function ($mock): void {
            $mock->shouldReceive('reply')->once()->withArgs(
                fn (User $user, string $audience, string $question, array $history): bool => $question === 'Follow up'
                    && count($history) === 2
                    && $history[0]['content'] === 'First question'
                    && $history[1]['content'] === 'First answer'
            )->andReturn([
                'answer' => 'Follow-up answer',
                'sources' => [],
                'suggestions' => [],
                'provider' => 'local',
                'model' => null,
                'intent' => 'generative_answer',
            ]);
        });

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'candidate'])
            ->set('messages', [
                $this->chatMessage(1, 'user', 'First question'),
                $this->chatMessage(2, 'assistant', 'First answer'),
            ])
            ->set('messageSequence', 2)
            ->set('message', 'Follow up')
            ->call('sendMessage')
            ->assertSet('messages', fn (array $messages): bool => count($messages) === 4);
    }

    public function test_candidate_cannot_open_employer_chat(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->assertSet('enabled', false)
            ->assertDontSee('AI Copilot tuyển dụng');
    }

    public function test_director_receives_branch_management_assistant_profile(): void
    {
        $director = User::factory()->create([
            'role' => 'director',
            'is_active' => true,
        ]);

        $this->actingAs($director);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->assertSet('enabled', true)
            ->assertSet('assistantTitle', 'AI Điều hành chi nhánh')
            ->assertSet('assistantSubtitle', 'KPI, phê duyệt và cảnh báo')
            ->call('toggle')
            ->assertSee('AI Điều hành chi nhánh');
    }

    public function test_unexpected_service_error_is_not_exposed_in_chatbox(): void
    {
        $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
        $this->mock(AiChatService::class, function ($mock): void {
            $mock->shouldReceive('reply')
                ->once()
                ->andThrow(new \LogicException('database-password-and-sql-details'));
        });

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'candidate'])
            ->call('toggle')
            ->set('message', 'Trigger unexpected error')
            ->call('sendMessage')
            ->assertSee('Chatbox gặp lỗi ngoài dự kiến. Vui lòng thử lại sau.')
            ->assertDontSee('database-password-and-sql-details');
    }

    public function test_feedback_and_new_conversation_only_change_component_state(): void
    {
        $user = User::factory()->create(['role' => 'candidate', 'is_active' => true]);
        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'candidate'])
            ->set('messages', [$this->chatMessage(1, 'assistant', 'State-only answer')])
            ->set('messageSequence', 1)
            ->call('rateMessage', 1, 'helpful')
            ->assertSet('messages.0.feedback', 'helpful')
            ->call('rateMessage', 1, 'helpful')
            ->assertSet('messages.0.feedback', null)
            ->call('newConversation')
            ->assertSet('messages', [])
            ->assertSet('messageSequence', 0);
    }

    /** @return array<string, mixed> */
    private function chatMessage(int $id, string $role, string $content): array
    {
        return [
            'id' => $id,
            'role' => $role,
            'content' => $content,
            'sources' => [],
            'suggestions' => [],
            'status' => 'completed',
            'provider' => null,
            'model' => null,
            'intent' => null,
            'latency_ms' => null,
            'error_message' => null,
            'feedback' => null,
            'time' => '10:00',
        ];
    }
}
