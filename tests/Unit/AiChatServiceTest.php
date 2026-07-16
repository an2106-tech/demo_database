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

    public function test_employer_sources_use_actionable_business_labels(): void
    {
        config(['services.gemini.key' => null]);

        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([[
            'key' => 'operational-workload',
            'label' => 'Việc tuyển dụng cần ưu tiên',
            'content' => 'CV chờ sàng lọc: 2',
            'url' => '/employers/application-pipeline',
        ]]);

        Http::fake();

        $user = new class(['role' => 'hr']) extends User
        {
            public function isSuperAdmin(): bool
            {
                return false;
            }
        };
        $user->id = 106;

        $result = app(AiChatService::class)->reply($user, 'employer', 'Hôm nay có hồ sơ nào cần xử lý?');

        $this->assertSame([[
            'label' => 'Mở quản lý ứng tuyển',
            'url' => '/employers/application-pipeline',
        ]], $result['sources']);
        Http::assertNothingSent();
    }

    public function test_employer_application_sources_keep_candidate_context_in_labels(): void
    {
        config(['services.gemini.key' => null]);

        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([[
            'key' => 'employer-application-15',
            'label' => 'Ứng viên Nguyễn Minh Khang — Giảng viên Công nghệ thông tin',
            'content' => 'Ứng viên: Nguyễn Minh Khang Vị trí: Giảng viên Công nghệ thông tin Trạng thái: Chờ sàng lọc CV Ngày ứng tuyển: 15/07/2026',
            'url' => '/employers/candidates/9',
        ]]);

        Http::fake();

        $user = new class(['role' => 'hr']) extends User
        {
            public function isSuperAdmin(): bool
            {
                return false;
            }
        };
        $user->id = 107;

        $result = app(AiChatService::class)->reply($user, 'employer', 'Xem chi tiết hồ sơ Nguyễn Minh Khang');

        $this->assertSame([[
            'label' => 'Mở hồ sơ Nguyễn Minh Khang - Giảng viên Công nghệ thông tin',
            'url' => '/employers/candidates/9',
        ]], $result['sources']);
        Http::assertNothingSent();
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

    public function test_hr_open_question_prompt_keeps_chatbox_as_advisory_support(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.model' => 'test-model',
        ]);

        $context = $this->mock(AiChatContextService::class);
        $context->shouldReceive('build')->once()->andReturn([[
            'key' => 'operational-workload',
            'label' => 'Việc tuyển dụng cần ưu tiên',
            'content' => 'CV chờ sàng lọc: 2',
            'url' => '/employers/application-pipeline',
        ]]);

        Http::fake(['*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [[
                    'text' => json_encode([
                        'answer' => 'Nên ưu tiên nhóm hồ sơ chờ sàng lọc.',
                        'source_keys' => ['operational-workload'],
                        'suggestions' => ['Hồ sơ nào đang chờ sàng lọc?'],
                    ], JSON_UNESCAPED_UNICODE),
                ]]],
            ]],
        ])]);

        $user = new class(['role' => 'hr']) extends User
        {
            public function isSuperAdmin(): bool
            {
                return false;
            }
        };
        $user->id = 105;

        app(AiChatService::class)->reply($user, 'employer', 'Phan tich cach cai thien trai nghiem tuyen dung tuan toi');

        Http::assertSent(function ($request): bool {
            $prompt = (string) data_get($request->data(), 'contents.0.parts.0.text');

            return str_contains($prompt, 'Với câu hỏi mở của HR')
                && str_contains($prompt, 'không đưa ra kết luận như đã thao tác xong')
                && str_contains($prompt, 'Tránh diễn đạt kiểu kỹ thuật');
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

    public function test_invalid_gemini_payload_falls_back_without_exposing_format_error(): void
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
                    'text' => 'Nên ưu tiên tuyển thêm vị trí giảng viên Công nghệ thông tin nếu số hồ sơ mở đang tăng và chi nhánh còn thiếu người phỏng vấn chuyên môn.',
                ]]],
            ]],
        ])]);

        $user = new class(['role' => 'hr']) extends User
        {
            public function isSuperAdmin(): bool
            {
                return false;
            }
        };
        $user->id = 106;

        $result = app(AiChatService::class)->reply($user, 'employer', 'Vậy định hướng nên tuyển thêm các vị trí nào cho chi nhánh?');

        $this->assertSame('generative_fallback', $result['intent']);
        $this->assertStringContainsString('Nên ưu tiên tuyển thêm', $result['answer']);
        $this->assertStringNotContainsString('không đúng định dạng', $result['answer']);
        $this->assertNotEmpty($result['suggestions']);
    }

    public function test_gemini_suggestions_use_business_wording(): void
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
                        'answer' => 'Hiện chưa có dữ liệu cần xử lý.',
                        'source_keys' => [],
                        'suggestions' => ['Pipeline đang vướng ở đâu?', 'Sàng lọc CV của Nguyễn Văn A'],
                    ], JSON_UNESCAPED_UNICODE),
                ]]],
            ]],
        ])]);

        $user = new User(['role' => 'director']);
        $user->id = 104;

        $result = app(AiChatService::class)->reply($user, 'employer', 'Phân tích chiến lược tuyển dụng quý tới');

        $this->assertSame([
            'Quy trình tuyển dụng đang vướng ở đâu?',
            'Hồ sơ nào đang chờ sàng lọc?',
        ], $result['suggestions']);
    }
}
