<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class AiChatIntentService
{
    /**
     * @param  array<int, array{key: string, label: string, content: string, url: string|null}>  $context
     * @return array{answer: string, source_keys: array<int, string>, suggestions: array<int, string>, intent: string}|null
     */
    public function resolve(User $user, string $audience, string $question, array $context): ?array
    {
        $normalized = $this->normalize($question);

        if (in_array($normalized, ['xin chao', 'chao ai', 'hello', 'hi'], true)) {
            return [
                'answer' => $audience === 'candidate'
                    ? 'Xin chào! Mình có thể tra cứu hồ sơ ứng tuyển, lịch phỏng vấn, offer và gợi ý việc phù hợp với hồ sơ của bạn.'
                    : 'Xin chào! Mình có thể tổng hợp pipeline, việc quá hạn, offer chờ duyệt và các ưu tiên tuyển dụng theo đúng phạm vi tài khoản của bạn.',
                'source_keys' => [],
                'suggestions' => $audience === 'candidate'
                    ? ['Tóm tắt hồ sơ ứng tuyển của tôi', 'Gợi ý việc phù hợp với tôi']
                    : ['Tóm tắt việc cần ưu tiên hôm nay', 'Pipeline đang nghẽn ở đâu?'],
                'intent' => 'greeting',
            ];
        }

        if ($audience === 'candidate') {
            if ($this->containsAny($normalized, ['trang thai ho so', 'tinh trang ho so', 'ho so cua toi', 'ho so dang o dau'])) {
                return $this->answerFromSources(
                    'candidate_application_status',
                    'Tình trạng hồ sơ ứng tuyển của bạn:',
                    $context,
                    fn (array $source): bool => str_starts_with($source['key'], 'application-'),
                    ['Tôi có lịch phỏng vấn nào sắp tới?', 'Gợi ý việc phù hợp với tôi'],
                    'Bạn chưa có hồ sơ ứng tuyển nào trong hệ thống.'
                );
            }

            if ($this->containsAny($normalized, ['viec phu hop', 'cong viec phu hop', 'goi y viec', 'viec nao hop'])) {
                return $this->answerFromSources(
                    'candidate_job_recommendations',
                    'Các công việc được ưu tiên theo thông tin CV và hồ sơ hiện tại:',
                    $context,
                    fn (array $source): bool => str_starts_with($source['key'], 'job-'),
                    ['Yêu cầu của công việc đầu tiên là gì?', 'Tôi nên cải thiện kỹ năng nào?'],
                    'Hiện chưa có tin tuyển dụng đang mở để đối chiếu với hồ sơ của bạn.',
                    3
                );
            }

            if ($this->containsAny($normalized, ['lich phong van', 'offer', 'de nghi tuyen dung', 'sap toi'])) {
                return $this->answerFromSources(
                    'candidate_upcoming_events',
                    'Thông tin lịch phỏng vấn và offer trong các hồ sơ gần đây:',
                    $context,
                    fn (array $source): bool => str_starts_with($source['key'], 'application-')
                        && (str_contains($source['content'], 'Lịch phỏng vấn gần nhất:')
                            || str_contains($source['content'], 'Trạng thái offer:')),
                    ['Tóm tắt trạng thái tất cả hồ sơ', 'Tôi cần chuẩn bị gì cho phỏng vấn?'],
                    'Bạn chưa có lịch phỏng vấn hoặc offer nào được ghi nhận.'
                );
            }

            return null;
        }

        if ($this->containsAny($normalized, ['briefing', 'uu tien', 'can xu ly', 'viec hom nay', 'qua han'])) {
            return $this->answerByKeys(
                'employer_operational_briefing',
                'Các đầu việc tuyển dụng cần chú ý:',
                $context,
                ['operational-workload'],
                ['Pipeline đang nghẽn ở đâu?', 'Có offer nào chờ duyệt?']
            );
        }

        if ($this->containsAny($normalized, ['kpi', 'hieu qua', 'ty le tuyen', '30 ngay'])) {
            return $this->answerByKeys(
                'director_performance',
                'Hiệu quả tuyển dụng gần đây:',
                $context,
                ['branch-performance'],
                ['Khối lượng hồ sơ của HR thế nào?', 'Có việc gì đang quá hạn?']
            );
        }

        if ($this->containsAny($normalized, ['hr nao', 'khoi luong hr', 'phan bo ho so', 'tai cua hr'])) {
            return $this->answerByKeys(
                'director_hr_workload',
                'Khối lượng hồ sơ đang mở theo HR:',
                $context,
                ['hr-workload'],
                ['Pipeline đang nghẽn ở đâu?', 'Tóm tắt KPI 30 ngày']
            );
        }

        if ($this->containsAny($normalized, ['offer cho duyet', 'offer nao', 'de nghi cho duyet', 'dang cho duyet'])) {
            return $this->answerByKeys(
                'director_pending_approvals',
                'Các đề nghị tuyển dụng đang chờ duyệt:',
                $context,
                ['offers-awaiting-approval', 'operational-workload'],
                ['Tóm tắt KPI 30 ngày', 'HR nào đang có nhiều hồ sơ mở?']
            );
        }

        if ($this->containsAny($normalized, ['pipeline', 'diem nghen', 'nghen o', 'giai doan nao'])) {
            return $this->answerByKeys(
                'employer_pipeline_summary',
                'Tổng quan pipeline hiện tại:',
                $context,
                ['recruitment-pipeline', 'operational-workload'],
                ['Việc nào cần ưu tiên hôm nay?', 'Tin nào có nhiều hồ sơ nhất?']
            );
        }

        return null;
    }

    private function answerByKeys(string $intent, string $heading, array $context, array $keys, array $suggestions): array
    {
        $keyLookup = array_flip($keys);

        return $this->buildAnswer(
            $intent,
            $heading,
            array_values(array_filter($context, fn (array $source): bool => isset($keyLookup[$source['key']]))),
            $suggestions,
            'Chưa có đủ dữ liệu phù hợp trong phạm vi tài khoản để tổng hợp.'
        );
    }

    private function answerFromSources(
        string $intent,
        string $heading,
        array $context,
        callable $filter,
        array $suggestions,
        string $emptyMessage,
        int $limit = 6,
    ): array {
        $sources = array_slice(array_values(array_filter($context, $filter)), 0, $limit);

        return $this->buildAnswer($intent, $heading, $sources, $suggestions, $emptyMessage);
    }

    private function buildAnswer(string $intent, string $heading, array $sources, array $suggestions, string $emptyMessage): array
    {
        if ($sources === []) {
            return [
                'answer' => $emptyMessage,
                'source_keys' => [],
                'suggestions' => $suggestions,
                'intent' => $intent,
            ];
        }

        $details = collect($sources)
            ->map(fn (array $source): string => '- '.$source['label'].': '.$source['content'])
            ->implode("\n");

        return [
            'answer' => mb_substr($heading."\n".$details, 0, 6000),
            'source_keys' => array_column($sources, 'key'),
            'suggestions' => $suggestions,
            'intent' => $intent,
        ];
    }

    private function containsAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        $normalized = Str::lower(Str::ascii(trim($value)));

        return preg_replace('/\s+/u', ' ', $normalized) ?: '';
    }
}
