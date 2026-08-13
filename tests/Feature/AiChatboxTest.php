<?php

namespace Tests\Feature;

use App\Livewire\AiChatbox;
use App\Models\AiChatSession;
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
            ->assertDispatched('ai-chat-open')
            ->call('rateMessage', 1, 'helpful')
            ->assertSet('messages.0.feedback', null)
            ->call('newConversation')
            ->assertSet('messages', [])
            ->assertSet('messageSequence', 0);
    }

    public function test_using_suggestion_keeps_chatbox_open(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'is_active' => true]);
        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->call('useSuggestion', 'Hôm nay có hồ sơ nào cần xử lý?')
            ->assertSet('message', 'Hôm nay có hồ sơ nào cần xử lý?')
            ->assertDispatched('ai-chat-open');
    }

    public function test_chat_history_is_restored_for_active_user_session(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'is_active' => true]);

        $this->mock(AiChatService::class, function ($mock): void {
            $mock->shouldReceive('reply')->once()->andReturn([
                'answer' => 'Có 2 hồ sơ cần ưu tiên xử lý hôm nay.',
                'sources' => [],
                'suggestions' => ['Pipeline đang nghẽn ở đâu?'],
                'provider' => 'local',
                'model' => null,
                'intent' => 'employer_operational_briefing',
            ]);
        });

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->set('message', 'Hôm nay cần xử lý gì?')
            ->call('sendMessage')
            ->assertSet('messages', fn (array $messages): bool => count($messages) === 2);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->assertSet('messages', fn (array $messages): bool => count($messages) === 2
                && $messages[0]['content'] === 'Hôm nay cần xử lý gì?'
                && $messages[1]['content'] === 'Có 2 hồ sơ cần ưu tiên xử lý hôm nay.');
    }

    public function test_chat_message_time_uses_business_timezone(): void
    {
        config([
            'app.timezone' => 'UTC',
            'app.interview_timezone' => 'Asia/Ho_Chi_Minh',
        ]);
        date_default_timezone_set('UTC');

        $user = User::factory()->create(['role' => 'hr', 'is_active' => true]);

        $session = AiChatSession::query()->create([
            'user_id' => $user->id,
            'audience' => 'employer',
            'title' => 'Test',
            'is_active' => true,
            'last_message_at' => now(),
        ]);

        $message = $session->messages()->create([
            'role' => 'assistant',
            'content' => 'Xin chao',
            'status' => 'completed',
        ]);
        $message->forceFill([
            'created_at' => \Illuminate\Support\Carbon::parse('2026-07-16 08:00:00', 'UTC'),
            'updated_at' => \Illuminate\Support\Carbon::parse('2026-07-16 08:00:00', 'UTC'),
        ])->save();

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->assertSet('messages', fn (array $messages): bool => $messages[0]['db_id'] === $message->id
                && $messages[0]['time'] === '15:00'
            );
    }

    public function test_new_conversation_closes_previous_chat_session(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'is_active' => true]);

        $this->mock(AiChatService::class, function ($mock): void {
            $mock->shouldReceive('reply')->once()->andReturn([
                'answer' => 'Không có hồ sơ quá hạn.',
                'sources' => [],
                'suggestions' => [],
                'provider' => 'local',
                'model' => null,
                'intent' => 'employer_operational_briefing',
            ]);
        });

        $this->actingAs($user);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->set('message', 'Có hồ sơ quá hạn không?')
            ->call('sendMessage')
            ->call('newConversation')
            ->assertSet('messages', [])
            ->assertSet('currentSessionId', null)
            ->assertDispatched('ai-chat-open');

        $this->assertFalse(AiChatSession::query()->firstOrFail()->is_active);

        Livewire::test(AiChatbox::class, ['audience' => 'employer'])
            ->assertSet('messages', []);
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
