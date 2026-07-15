<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AiChatIntentService;
use PHPUnit\Framework\TestCase;

class AiChatIntentServiceTest extends TestCase
{
    public function test_candidate_status_question_is_answered_from_application_sources(): void
    {
        $user = new User(['role' => 'candidate']);
        $context = [[
            'key' => 'application-12',
            'label' => 'Hồ sơ ứng tuyển: Laravel Developer',
            'content' => 'Trạng thái: Đã lên lịch phỏng vấn',
            'url' => '/candidates/applications/12',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'candidate', 'Hồ sơ của tôi đang ở đâu?', $context);

        $this->assertSame('candidate_application_status', $result['intent']);
        $this->assertSame(['application-12'], $result['source_keys']);
        $this->assertStringContainsString('Đã lên lịch phỏng vấn', $result['answer']);
    }

    public function test_director_kpi_question_uses_performance_source(): void
    {
        $user = new User(['role' => 'director']);
        $context = [[
            'key' => 'branch-performance',
            'label' => 'Hiệu quả tuyển dụng 30 ngày',
            'content' => 'Hồ sơ mới: 14 Đã tuyển: 3',
            'url' => '/employers/dashboard',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'KPI tuyển dụng 30 ngày thế nào?', $context);

        $this->assertSame('director_performance', $result['intent']);
        $this->assertSame(['branch-performance'], $result['source_keys']);
    }

    public function test_upcoming_event_answer_ignores_applications_without_interview_or_offer(): void
    {
        $user = new User(['role' => 'candidate']);
        $context = [
            [
                'key' => 'application-1',
                'label' => 'Hồ sơ A',
                'content' => 'Trạng thái: Sơ tuyển',
                'url' => null,
            ],
            [
                'key' => 'application-2',
                'label' => 'Hồ sơ B',
                'content' => 'Trạng thái: Phỏng vấn Lịch phỏng vấn gần nhất: 20/07/2026 09:00',
                'url' => null,
            ],
        ];

        $result = (new AiChatIntentService)->resolve($user, 'candidate', 'Tôi có lịch phỏng vấn nào sắp tới?', $context);

        $this->assertSame(['application-2'], $result['source_keys']);
        $this->assertStringNotContainsString('Hồ sơ A', $result['answer']);
        $this->assertStringContainsString('20/07/2026', $result['answer']);
    }
}
