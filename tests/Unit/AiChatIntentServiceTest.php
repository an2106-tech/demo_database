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

    public function test_employer_workload_answer_is_summarized_for_hr(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'operational-workload',
            'label' => 'Việc tuyển dụng cần ưu tiên',
            'content' => implode("\n", [
                'tin tuyển dụng chờ duyệt: 0',
                'CV chờ sàng lọc: 2',
                'lịch phỏng vấn chưa gửi thư mời: 0',
                'phỏng vấn quá hạn chưa chấm: 1',
                'đề nghị tuyển dụng nháp: 0',
                'đề nghị chờ giám đốc duyệt: 0',
                'đề nghị sắp hết hạn: 0',
            ]),
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Hôm nay có hồ sơ nào cần xử lý?', $context);

        $this->assertSame('employer_operational_briefing', $result['intent']);
        $this->assertCount(2, $result['suggestions']);
        $this->assertStringContainsString('2 hồ sơ chờ sàng lọc CV', $result['answer']);
        $this->assertStringContainsString('1 phỏng vấn quá hạn chưa chấm', $result['answer']);
        $this->assertStringContainsString('Gợi ý:', $result['answer']);
    }
    public function test_employer_follow_up_suggestion_is_answered_without_gemini(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'operational-workload',
            'label' => 'Việc tuyển dụng cần ưu tiên',
            'content' => implode("\n", [
                'CV chờ sàng lọc: 1',
                'phỏng vấn quá hạn chưa chấm: 1',
            ]),
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Có phỏng vấn nào cần chấm ngay?', $context);

        $this->assertSame('employer_interview_follow_up', $result['intent']);
        $this->assertSame(['operational-workload'], $result['source_keys']);
        $this->assertStringContainsString('buổi phỏng vấn đã qua nhưng chưa chấm', $result['answer']);
        $this->assertStringNotContainsString('hồ sơ đang chờ HR sàng lọc CV', $result['answer']);
    }

    public function test_employer_offer_drafts_are_answered_from_workload_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'operational-workload',
            'label' => 'Viec tuyen dung can uu tien',
            'content' => implode("\n", [
                'CV cho sang loc: 4',
                'phong van qua han chua cham: 2',
                'de nghi tuyen dung nhap: 3',
                'de nghi cho giam doc duyet: 1',
                'de nghi sap het han: 0',
            ]),
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'De nghi tuyen dung nao dang nhap?', $context);

        $this->assertSame('employer_offer_drafts', $result['intent']);
        $this->assertSame(['operational-workload'], $result['source_keys']);
        $this->assertStringContainsString('3', $result['answer']);
        $this->assertStringNotContainsString('4', $result['answer']);
    }

    public function test_employer_expiring_offers_are_answered_from_workload_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'operational-workload',
            'label' => 'Viec tuyen dung can uu tien',
            'content' => implode("\n", [
                'CV cho sang loc: 4',
                'phong van qua han chua cham: 2',
                'de nghi tuyen dung nhap: 3',
                'de nghi cho giam doc duyet: 1',
                'de nghi sap het han: 5',
            ]),
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'De nghi nao sap het han phan hoi?', $context);

        $this->assertSame('employer_offer_expiring', $result['intent']);
        $this->assertSame(['operational-workload'], $result['source_keys']);
        $this->assertStringContainsString('5', $result['answer']);
        $this->assertStringNotContainsString('4', $result['answer']);
    }

    public function test_employer_stale_applications_are_answered_from_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'stale-applications',
            'label' => 'Hồ sơ lâu chưa cập nhật',
            'content' => 'Nguyễn Văn A | Backend Developer | Chờ sàng lọc CV | cập nhật lần cuối 01/07/2026 09:00',
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Có hồ sơ nào lâu chưa cập nhật không?', $context);

        $this->assertSame('employer_stale_applications', $result['intent']);
        $this->assertSame(['stale-applications'], $result['source_keys']);
        $this->assertStringContainsString('Nguyễn Văn A', $result['answer']);
    }

    public function test_employer_low_application_jobs_are_answered_from_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'low-application-jobs',
            'label' => 'Tin tuyển dụng ít hồ sơ',
            'content' => 'Giảng viên Công nghệ thông tin | 0 hồ sơ | hạn 30/07/2026',
            'url' => '/employers/manage-jobs',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Tin tuyển dụng nào đang ít hồ sơ?', $context);

        $this->assertSame('employer_low_application_jobs', $result['intent']);
        $this->assertSame(['low-application-jobs'], $result['source_keys']);
        $this->assertStringContainsString('Giảng viên Công nghệ thông tin', $result['answer']);
    }

    public function test_employer_hired_and_rejected_counts_are_answered_from_pipeline_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'recruitment-pipeline',
            'label' => 'Tong quan quy trinh tuyen dung',
            'content' => 'So luong ho so theo trang thai: {"hired":2,"rejected":4,"cv_reviewing":1}',
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Co bao nhieu ho so da tuyen/tu choi?', $context);

        $this->assertSame('employer_outcome_counts', $result['intent']);
        $this->assertSame(['recruitment-pipeline'], $result['source_keys']);
        $this->assertStringContainsString('2', $result['answer']);
        $this->assertStringContainsString('4', $result['answer']);
    }

    public function test_employer_application_detail_is_answered_as_lookup_not_action(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'employer-application-15',
            'label' => 'Ứng viên Nguyễn Minh Khang — Giảng viên Công nghệ thông tin',
            'content' => 'Ứng viên: Nguyễn Minh Khang Vị trí: Giảng viên Công nghệ thông tin Trạng thái: Chờ sàng lọc CV Ngày ứng tuyển: 15/07/2026',
            'url' => '/employers/candidates/9',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Xem chi tiết hồ sơ Nguyễn Minh Khang', $context);

        $this->assertSame('employer_application_detail', $result['intent']);
        $this->assertSame(['employer-application-15'], $result['source_keys']);
        $this->assertStringContainsString('Chatbox chỉ hỗ trợ tra cứu', $result['answer']);
        $this->assertStringNotContainsString('**', $result['answer']);
    }

    public function test_employer_job_application_ranking_is_answered_from_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [
            [
                'key' => 'employer-job-1',
                'label' => 'Tin tuyển dụng: Backend Developer',
                'content' => 'Vị trí: Backend Developer Số hồ sơ: 2',
                'url' => '/employers/manage-jobs',
            ],
            [
                'key' => 'employer-job-2',
                'label' => 'Tin tuyển dụng: Product Owner',
                'content' => 'Vị trí: Product Owner Số hồ sơ: 5',
                'url' => '/employers/manage-jobs',
            ],
        ];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Tin tuyển dụng nào có nhiều hồ sơ nhất?', $context);

        $this->assertSame('employer_job_application_ranking', $result['intent']);
        $this->assertSame(['employer-job-2', 'employer-job-1'], $result['source_keys']);
        $this->assertStringContainsString('Product Owner: 5 hồ sơ', $result['answer']);
    }

    public function test_employer_upcoming_interviews_are_answered_from_context(): void
    {
        $user = new User(['role' => 'hr']);
        $context = [[
            'key' => 'upcoming-interviews',
            'label' => 'Lịch phỏng vấn sắp tới',
            'content' => '20/07/2026 09:00 | Nguyễn Văn A | Backend Developer | Online | chưa gửi thư mời',
            'url' => '/employers/application-pipeline',
        ]];

        $result = (new AiChatIntentService)->resolve($user, 'employer', 'Có lịch phỏng vấn nào sắp tới?', $context);

        $this->assertSame('employer_upcoming_interviews', $result['intent']);
        $this->assertSame(['upcoming-interviews'], $result['source_keys']);
        $this->assertStringContainsString('20/07/2026 09:00 - Nguyễn Văn A - Backend Developer', $result['answer']);
    }
}
