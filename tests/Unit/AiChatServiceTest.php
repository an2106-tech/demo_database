<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiChatContextService;
use App\Services\AiChatService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatServiceTest extends TestCase
{
    public function test_service_maps_only_known_source_keys(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'test-model',
        ]);

        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([[
            'key' => 'job-1',
            'label' => 'Laravel Developer',
            'content' => 'Vị trí Laravel Developer đang tuyển.',
            'url' => '/jobs/laravel-developer',
        ]]);

        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'answer' => 'Bạn có thể xem vị trí Laravel Developer.',
                            'source_keys' => ['job-1', 'unknown-secret'],
                            'suggestions' => ['Yêu cầu công việc là gì?'],
                        ], JSON_UNESCAPED_UNICODE),
                    ]]],
                ]],
            ]),
        ]);

        $user = new User(['name' => 'Test User', 'email' => 'test@example.com']);
        $user->id = 99;

        $result = app(AiChatService::class)->reply($user, 'candidate', 'Phân tích lộ trình phát triển dài hạn');

        $this->assertSame('Bạn có thể xem vị trí Laravel Developer.', $result['answer']);
        $this->assertSame([[
            'label' => 'Laravel Developer',
            'url' => '/jobs/laravel-developer',
        ]], $result['sources']);
        $this->assertCount(1, $result['suggestions']);

        Http::assertSent(function ($request): bool {
            $prompt = (string) data_get($request->data(), 'contents.0.parts.0.text');

            return ! str_contains($prompt, 'unknown-secret')
                && str_contains($prompt, 'Chỉ trả lời từ dữ liệu');
        });
    }

    public function test_director_prompt_receives_role_specific_instructions(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'test-model',
        ]);

        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([]);
        Http::fake(['*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'answer' => 'Không có việc tồn đọng.',
                        'source_keys' => [],
                        'suggestions' => [],
                    ], JSON_UNESCAPED_UNICODE),
                ]]],
            ]],
        ])]);

        $director = new User([
            'name' => 'Giám đốc',
            'email' => 'director@example.com',
            'role' => 'director',
        ]);
        $director->id = 100;

        app(AiChatService::class)->reply($director, 'employer', 'Phân tích rủi ro tuyển dụng chiến lược');

        Http::assertSent(function ($request): bool {
            $prompt = (string) data_get($request->data(), 'contents.0.parts.0.text');

            return str_contains($prompt, 'giám đốc chi nhánh')
                && str_contains($prompt, 'ưu tiên KPI, điểm nghẽn, việc chờ duyệt');
        });
    }

    public function test_provider_error_does_not_leak_raw_message_to_user(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'test-model',
        ]);

        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([]);
        Http::fake(['*' => Http::response([
            'error' => ['message' => 'internal-provider-secret-and-debug-details'],
        ], 500)]);

        $user = new User([
            'name' => 'Candidate',
            'email' => 'candidate@example.com',
            'role' => 'candidate',
        ]);
        $user->id = 101;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Dịch vụ AI chưa thể xử lý yêu cầu lúc này. Vui lòng thử lại sau.');

        app(AiChatService::class)->reply($user, 'candidate', 'Kiểm tra lỗi');
    }

    public function test_context_failure_is_wrapped_in_safe_error(): void
    {
        config(['services.gemini.key' => 'test-key']);
        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')
            ->once()
            ->andThrow(new \RuntimeException('select secret from private_table'));

        $user = new User([
            'name' => 'Candidate',
            'email' => 'candidate-safe-error@example.com',
            'role' => 'candidate',
        ]);
        $user->id = 102;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Không thể kết nối AI lúc này. Vui lòng thử lại sau.');

        app(AiChatService::class)->reply($user, 'candidate', 'Kiểm tra context');
    }

    public function test_known_business_intent_works_without_gemini_request(): void
    {
        config(['services.gemini.key' => null]);
        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([[
            'key' => 'application-7',
            'label' => 'Hồ sơ ứng tuyển: PHP Developer',
            'content' => 'Trạng thái: Sơ tuyển',
            'url' => '/candidates/applications/7',
        ]]);
        Http::fake();

        $user = new User(['role' => 'candidate']);
        $user->id = 103;
        $result = app(AiChatService::class)->reply($user, 'candidate', 'Tình trạng hồ sơ của tôi?');

        $this->assertSame('local', $result['provider']);
        $this->assertSame('rules-v1', $result['model']);
        $this->assertSame('candidate_application_status', $result['intent']);
        $this->assertStringContainsString('Sơ tuyển', $result['answer']);
        Http::assertNothingSent();
    }
}
