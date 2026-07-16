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
                    : 'Xin chào! Mình có thể tổng hợp hồ sơ cần xử lý, lịch phỏng vấn, đề nghị tuyển dụng và các điểm đang vướng theo dữ liệu tuyển dụng bạn được phân quyền xem.',
                'source_keys' => [],
                'suggestions' => $audience === 'candidate'
                    ? ['Tóm tắt hồ sơ ứng tuyển của tôi', 'Gợi ý việc phù hợp với tôi']
                    : ['Hôm nay có hồ sơ nào cần xử lý?', 'Quy trình tuyển dụng đang vướng ở bước nào?'],
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

        if ($this->containsAny($normalized, ['xem chi tiet ho so', 'chi tiet ho so', 'chi tiet ung vien', 'thong tin ho so'])) {
            return $this->answerEmployerApplicationDetail($normalized, $context);
        }

        if ($this->containsAny($normalized, ['lich phong van sap toi', 'lich phong van nao sap toi', 'co lich phong van nao', 'lich phong van hom nay', 'lich phong van tuan nay'])) {
            return $this->answerByKeys(
                'employer_upcoming_interviews',
                'Lịch phỏng vấn sắp tới:',
                $context,
                ['upcoming-interviews'],
                ['Có phỏng vấn nào cần chấm ngay?', 'Hồ sơ nào đang chờ sàng lọc?']
            );
        }

        if ($this->containsAny($normalized, ['phong van can cham', 'phong van chua cham', 'can cham ngay', 'danh gia phong van'])) {
            return $this->answerByKeys(
                'employer_interview_follow_up',
                'Phỏng vấn cần theo dõi:',
                $context,
                ['operational-workload'],
                ['Hồ sơ nào đang chờ sàng lọc?', 'Quy trình tuyển dụng đang vướng ở đâu?']
            );
        }

        if ($this->containsAny($normalized, ['ho so cho sang loc', 'cv cho sang loc', 'dang cho sang loc', 'can sang loc'])) {
            return $this->answerByKeys(
                'employer_cv_screening_follow_up',
                'Hồ sơ cần sàng lọc:',
                $context,
                ['operational-workload'],
                ['Có phỏng vấn nào cần chấm ngay?', 'Quy trình tuyển dụng đang vướng ở đâu?']
            );
        }

        if ($this->containsAny($normalized, ['tin tuyen dung nao co nhieu ho so', 'viec nao co nhieu ho so', 'nhieu ho so nhat', 'it ho so nhat'])) {
            return $this->answerEmployerJobApplicationRanking($normalized, $context);
        }

        if ($this->containsAny($normalized, ['da tuyen', 'tu choi'])
            && $this->containsAny($normalized, ['bao nhieu', 'so luong', 'tong', 'thong ke'])) {
            return $this->answerEmployerOutcomeCounts($context);
        }

        if ($this->containsAny($normalized, ['tin it ho so', 'tin tuyen dung it ho so', 'viec it ho so', 'dang it ho so'])) {
            return $this->answerByKeys(
                'employer_low_application_jobs',
                'Tin tuyển dụng ít hồ sơ:',
                $context,
                ['low-application-jobs'],
                ['Tin tuyển dụng nào có nhiều hồ sơ nhất?', 'Hồ sơ nào đang chờ sàng lọc?']
            );
        }

        if ($this->containsAny($normalized, ['ho so lau chua cap nhat', 'ho so nao lau chua cap nhat', 'ho so qua han xu ly', 'ho so bi cham', 'lau chua xu ly'])) {
            return $this->answerByKeys(
                'employer_stale_applications',
                'Hồ sơ lâu chưa cập nhật:',
                $context,
                ['stale-applications'],
                ['Hồ sơ nào đang chờ sàng lọc?', 'Có phỏng vấn nào cần chấm ngay?']
            );
        }

        if ($this->containsAny($normalized, ['de nghi tuyen dung nhap', 'de nghi tuyen dung nao dang nhap', 'de nghi dang nhap', 'offer nhap'])) {
            return $this->answerByKeys(
                'employer_offer_drafts',
                'Đề nghị tuyển dụng đang nháp:',
                $context,
                ['operational-workload'],
                ['Có đề nghị nào chờ giám đốc duyệt?', 'Đề nghị nào sắp hết hạn phản hồi?']
            );
        }

        if ($this->containsAny($normalized, ['offer sap het han', 'de nghi sap het han', 'het han phan hoi', 'sap het han phan hoi'])) {
            return $this->answerByKeys(
                'employer_offer_expiring',
                'Đề nghị sắp hết hạn phản hồi:',
                $context,
                ['operational-workload'],
                ['Có đề nghị nào chờ giám đốc duyệt?', 'Hôm nay có hồ sơ nào cần xử lý?']
            );
        }

        if ($this->containsAny($normalized, ['briefing', 'uu tien', 'can xu ly', 'viec hom nay', 'qua han'])) {
            return $this->answerByKeys(
                'employer_operational_briefing',
                'Các việc HR nên ưu tiên:',
                $context,
                ['operational-workload'],
                ['Quy trình tuyển dụng đang vướng ở bước nào?', 'Có đề nghị tuyển dụng nào cần theo dõi?']
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
                ['Quy trình tuyển dụng đang vướng ở bước nào?', 'Tóm tắt hiệu quả 30 ngày']
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

        if ($this->containsAny($normalized, ['pipeline', 'quy trinh', 'tien do', 'diem nghen', 'diem vuong', 'vuong o', 'nghen o', 'giai doan nao', 'buoc nao'])) {
            return $this->answerByKeys(
                'employer_pipeline_summary',
                'Tổng quan quy trình tuyển dụng:',
                $context,
                ['recruitment-pipeline', 'operational-workload'],
                ['Hôm nay có hồ sơ nào cần xử lý?', 'Tin tuyển dụng nào có nhiều hồ sơ nhất?']
            );
        }

        return null;
    }

    /**
     * @param  array<int, array{key: string, label: string, content: string, url: string|null}>  $context
     * @return array{answer: string, source_keys: array<int, string>, suggestions: array<int, string>, intent: string}
     */
    private function answerEmployerJobApplicationRanking(string $normalizedQuestion, array $context): array
    {
        $jobs = collect($context)
            ->filter(fn (array $source): bool => str_starts_with($source['key'], 'employer-job-'))
            ->map(function (array $source): array {
                preg_match('/so ho so\s*:\s*(\d+)/u', $this->normalize($source['content']), $matches);

                return [
                    'source' => $source,
                    'count' => (int) ($matches[1] ?? 0),
                ];
            })
            ->values();

        if ($jobs->isEmpty()) {
            return [
                'answer' => 'Hiện chưa có tin tuyển dụng phù hợp để tổng hợp. HR có thể kiểm tra lại danh sách tin đang mở hoặc thử câu hỏi cụ thể hơn.',
                'source_keys' => [],
                'suggestions' => ['Hồ sơ nào đang chờ sàng lọc?', 'Quy trình tuyển dụng đang vướng ở đâu?'],
                'intent' => 'employer_job_application_ranking',
            ];
        }

        $ascending = str_contains($normalizedQuestion, 'it ho so');
        $ranked = $ascending ? $jobs->sortBy('count')->values() : $jobs->sortByDesc('count')->values();
        $top = $ranked->take(3);
        $heading = $ascending ? 'Tin tuyển dụng có ít hồ sơ nhất:' : 'Tin tuyển dụng có nhiều hồ sơ nhất:';

        $lines = [$heading];
        foreach ($top as $index => $item) {
            $label = preg_replace('/^Tin tuyển dụng:\s*/u', '', $item['source']['label']) ?: $item['source']['label'];
            $lines[] = ($index + 1).". {$label}: {$item['count']} hồ sơ.";
        }

        $lines[] = '';
        $lines[] = $ascending
            ? 'Gợi ý: nên kiểm tra lại nội dung tin, hạn nộp và mức độ phù hợp của vị trí nếu tin có quá ít hồ sơ.'
            : 'Gợi ý: nên ưu tiên xử lý các tin có nhiều hồ sơ để tránh tồn đọng ở bước sàng lọc.';

        return [
            'answer' => implode("\n", $lines),
            'source_keys' => $top->pluck('source.key')->all(),
            'suggestions' => ['Hồ sơ nào đang chờ sàng lọc?', 'Quy trình tuyển dụng đang vướng ở đâu?'],
            'intent' => 'employer_job_application_ranking',
        ];
    }

    /**
     * @param  array<int, array{key: string, label: string, content: string, url: string|null}>  $context
     * @return array{answer: string, source_keys: array<int, string>, suggestions: array<int, string>, intent: string}
     */
    private function answerEmployerApplicationDetail(string $normalizedQuestion, array $context): array
    {
        $applications = collect($context)
            ->filter(fn (array $source): bool => str_starts_with($source['key'], 'employer-application-'))
            ->values();

        $terms = collect(preg_split('/\s+/u', $normalizedQuestion) ?: [])
            ->reject(fn (string $term): bool => in_array($term, [
                'xem', 'chi', 'tiet', 'ho', 'so', 'ung', 'vien', 'thong', 'tin', 'cua', 'cho',
            ], true) || mb_strlen($term) < 3)
            ->values();

        $matched = $applications
            ->map(function (array $source) use ($terms): array {
                $haystack = $this->normalize($source['label'].' '.$source['content']);
                $score = $terms->sum(fn (string $term): int => str_contains($haystack, $term) ? 1 : 0);

                return ['source' => $source, 'score' => $score];
            })
            ->sortByDesc('score')
            ->first();

        $source = is_array($matched) && $matched['score'] > 0
            ? $matched['source']
            : $applications->first();

        if (! $source) {
            return [
                'answer' => 'Mình chưa tìm thấy hồ sơ ứng tuyển khớp với câu hỏi này. HR có thể thử nhập tên ứng viên, vị trí hoặc mở quản lý ứng tuyển để lọc chi tiết hơn.',
                'source_keys' => [],
                'suggestions' => ['Hồ sơ nào đang chờ sàng lọc?', 'Quy trình tuyển dụng đang vướng ở đâu?'],
                'intent' => 'employer_application_detail',
            ];
        }

        $fields = $this->extractApplicationFields($source['content']);
        $candidate = $fields['Ứng viên'] ?? $source['label'];
        $position = $fields['Vị trí'] ?? 'chưa rõ vị trí';
        $status = $fields['Trạng thái'] ?? 'chưa rõ trạng thái';
        $appliedAt = $fields['Ngày ứng tuyển'] ?? 'chưa có ngày ứng tuyển';
        $interview = $fields['Phỏng vấn'] ?? null;

        $lines = [
            "Hồ sơ {$candidate}:",
            "- Vị trí ứng tuyển: {$position}.",
            "- Trạng thái hiện tại: {$status}.",
            "- Ngày ứng tuyển: {$appliedAt}.",
        ];

        if (filled($interview)) {
            $lines[] = "- Lịch phỏng vấn gần nhất: {$interview}.";
        }

        $lines[] = '';
        $lines[] = 'Chatbox chỉ hỗ trợ tra cứu và gợi ý. Để sàng lọc CV, hãy mở nguồn tham chiếu của hồ sơ và thực hiện thao tác tại màn hình xử lý.';

        return [
            'answer' => implode("\n", $lines),
            'source_keys' => [$source['key']],
            'suggestions' => ['Hồ sơ nào đang chờ sàng lọc?', 'Có phỏng vấn nào cần chấm ngay?'],
            'intent' => 'employer_application_detail',
        ];
    }

    /** @return array<string, string> */
    private function extractApplicationFields(string $content): array
    {
        $fields = [];
        foreach (preg_split('/\s+(?=Ứng viên:|Vị trí:|Trạng thái:|Ngày ứng tuyển:|Phỏng vấn:)/u', trim($content)) ?: [] as $part) {
            if (preg_match('/^(Ứng viên|Vị trí|Trạng thái|Ngày ứng tuyển|Phỏng vấn):\s*(.+)$/u', trim($part), $matches) === 1) {
                $fields[$matches[1]] = trim($matches[2]);
            }
        }

        return $fields;
    }

    /**
     * @param  array<int, array{key: string, label: string, content: string, url: string|null}>  $context
     * @return array{answer: string, source_keys: array<int, string>, suggestions: array<int, string>, intent: string}
     */
    private function answerEmployerOutcomeCounts(array $context): array
    {
        $source = collect($context)->firstWhere('key', 'recruitment-pipeline');
        $counts = is_array($source) ? $this->pipelineCounts((string) $source['content']) : [];

        $hired = (int) ($counts['hired'] ?? 0);
        $rejected = (int) ($counts['rejected'] ?? 0);

        $answer = $hired === 0 && $rejected === 0
            ? 'Hiện chưa có hồ sơ nào đã tuyển hoặc đã từ chối trong dữ liệu tuyển dụng đang xem.'
            : "Hiện có {$hired} hồ sơ đã tuyển và {$rejected} hồ sơ đã từ chối.";

        return [
            'answer' => $answer."\n\nGợi ý: nếu cần xem danh sách chi tiết, HR có thể mở quản lý ứng tuyển và lọc theo trạng thái.",
            'source_keys' => is_array($source) ? ['recruitment-pipeline'] : [],
            'suggestions' => ['Hồ sơ nào đang chờ sàng lọc?', 'Quy trình tuyển dụng đang vướng ở đâu?', 'Tin tuyển dụng nào ít hồ sơ?'],
            'intent' => 'employer_outcome_counts',
        ];
    }

    private function answerByKeys(string $intent, string $heading, array $context, array $keys, array $suggestions): array
    {
        $keyLookup = array_flip($keys);

        return $this->buildAnswer(
            $intent,
            $heading,
            array_values(array_filter($context, fn (array $source): bool => isset($keyLookup[$source['key']]))),
            $suggestions,
            'Hiện chưa có thông tin phù hợp với câu hỏi này. HR có thể thử hỏi theo nhóm hồ sơ chờ sàng lọc, lịch phỏng vấn hoặc tin tuyển dụng đang mở.'
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

        if (str_starts_with($intent, 'employer_') || str_starts_with($intent, 'director_')) {
            return $this->buildRecruitmentAnswer($intent, $heading, $sources, $suggestions);
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

    private function buildRecruitmentAnswer(string $intent, string $heading, array $sources, array $suggestions): array
    {
        if (in_array($intent, ['employer_interview_follow_up', 'employer_cv_screening_follow_up'], true)) {
            return $this->buildFocusedWorkloadAnswer($intent, $heading, $sources, $suggestions);
        }

        if (in_array($intent, ['employer_offer_drafts', 'employer_offer_expiring', 'director_pending_approvals'], true)) {
            return $this->buildFocusedOfferAnswer($intent, $heading, $sources, $suggestions);
        }

        $items = collect($sources)
            ->flatMap(fn (array $source): array => $this->summarizeRecruitmentSource($source))
            ->filter()
            ->unique()
            ->take(6)
            ->values();

        if ($items->isEmpty()) {
            $items = collect(['- Hiện chưa có hạng mục nổi bật cần xử lý.']);
        }

        $answer = $heading."\n"
            .$items->implode("\n")
            ."\n\n".$this->nextActionForIntent($intent, $sources);

        return [
            'answer' => mb_substr($answer, 0, 4000),
            'source_keys' => array_column($sources, 'key'),
            'suggestions' => $this->suggestionsForRecruitmentIntent($intent, $sources, $suggestions),
            'intent' => $intent,
        ];
    }

    private function buildFocusedWorkloadAnswer(string $intent, string $heading, array $sources, array $suggestions): array
    {
        $workload = collect($sources)->firstWhere('key', 'operational-workload');
        $content = is_array($workload) ? $this->normalize((string) $workload['content']) : '';

        if ($intent === 'employer_interview_follow_up') {
            $pending = $this->workloadCount($content, 'phong van qua han chua cham');
            $unsent = $this->workloadCount($content, 'lich phong van chua gui thu moi');
            $items = [];

            $items[] = $pending > 0
                ? "- {$pending} buổi phỏng vấn đã qua nhưng chưa chấm."
                : '- Không có buổi phỏng vấn quá hạn chưa chấm.';

            if ($unsent > 0) {
                $items[] = "- {$unsent} lịch phỏng vấn sắp tới chưa gửi thư mời.";
            }

            $nextAction = $pending > 0
                ? 'Gợi ý: nên mở màn hình quản lý ứng tuyển để chấm scorecard trước khi chuyển bước tiếp theo.'
                : 'Gợi ý: có thể kiểm tra tiếp nhóm hồ sơ chờ sàng lọc hoặc lịch phỏng vấn sắp tới.';
        } else {
            $pending = $this->workloadCount($content, 'cv cho sang loc');
            $items = [
                $pending > 0
                    ? "- {$pending} hồ sơ đang chờ HR sàng lọc CV."
                    : '- Không có hồ sơ nào đang chờ sàng lọc CV.',
            ];

            $nextAction = $pending > 0
                ? 'Gợi ý: nên mở nguồn tham chiếu để xem CV, đối chiếu JD và quyết định chuyển sang sơ tuyển hoặc từ chối.'
                : 'Gợi ý: có thể kiểm tra nhóm phỏng vấn cần chấm hoặc đề nghị tuyển dụng cần theo dõi.';
        }

        return [
            'answer' => mb_substr($heading."\n".implode("\n", $items)."\n\n".$nextAction, 0, 4000),
            'source_keys' => array_column($sources, 'key'),
            'suggestions' => $this->suggestionsForRecruitmentIntent($intent, $sources, $suggestions),
            'intent' => $intent,
        ];
    }

    private function buildFocusedOfferAnswer(string $intent, string $heading, array $sources, array $suggestions): array
    {
        $workload = collect($sources)->firstWhere('key', 'operational-workload');
        $content = is_array($workload) ? $this->normalize((string) $workload['content']) : '';

        $drafts = $this->workloadCount($content, 'de nghi tuyen dung nhap');
        $pending = $this->workloadCount($content, 'de nghi cho giam doc duyet');
        $expiring = $this->workloadCount($content, 'de nghi sap het han');

        $items = match ($intent) {
            'employer_offer_drafts' => [
                $drafts > 0
                    ? "- {$drafts} đề nghị tuyển dụng đang nháp."
                    : '- Không có đề nghị tuyển dụng nào đang nháp.',
            ],
            'employer_offer_expiring' => [
                $expiring > 0
                    ? "- {$expiring} đề nghị sắp hết hạn phản hồi."
                    : '- Không có đề nghị nào sắp hết hạn phản hồi.',
            ],
            default => [
                $pending > 0
                    ? "- {$pending} đề nghị đang chờ giám đốc duyệt."
                    : '- Không có đề nghị tuyển dụng nào đang chờ duyệt.',
            ],
        };

        if ($intent === 'director_pending_approvals') {
            $approvalSource = collect($sources)->firstWhere('key', 'offers-awaiting-approval');
            if (is_array($approvalSource)) {
                $items = array_merge($items, array_slice($this->summarizeGenericSource($approvalSource), 0, 2));
            }
        }

        $nextAction = match ($intent) {
            'employer_offer_drafts' => $drafts > 0
                ? 'Gợi ý: HR nên kiểm tra nội dung, file đề nghị và hạn phản hồi trước khi gửi giám đốc duyệt.'
                : 'Gợi ý: có thể theo dõi tiếp nhóm đề nghị chờ duyệt hoặc hồ sơ cần sàng lọc.',
            'employer_offer_expiring' => $expiring > 0
                ? 'Gợi ý: nên theo dõi phản hồi của ứng viên trước hạn để tránh kéo dài quy trình.'
                : 'Gợi ý: có thể kiểm tra tiếp đề nghị chờ duyệt hoặc các buổi phỏng vấn cần chấm.',
            default => $pending > 0
                ? 'Gợi ý: giám đốc nên mở màn hình duyệt đề nghị để xem CV, scorecard và thông tin đề nghị trước khi quyết định.'
                : 'Gợi ý: hiện chưa có đề nghị cần duyệt, có thể xem thêm hiệu quả tuyển dụng hoặc khối lượng hồ sơ của HR.',
        };

        return [
            'answer' => mb_substr($heading."\n".implode("\n", $items)."\n\n".$nextAction, 0, 4000),
            'source_keys' => array_column($sources, 'key'),
            'suggestions' => $this->suggestionsForRecruitmentIntent($intent, $sources, $suggestions),
            'intent' => $intent,
        ];
    }

    /**
     * @param  array<int, array{key: string, label: string, content: string, url: string|null}>  $sources
     * @param  array<int, string>  $fallback
     * @return array<int, string>
     */
    private function suggestionsForRecruitmentIntent(string $intent, array $sources, array $fallback): array
    {
        $workload = collect($sources)->firstWhere('key', 'operational-workload');
        $content = is_array($workload) ? $this->normalize((string) $workload['content']) : '';

        $suggestions = match ($intent) {
            'employer_operational_briefing' => [
                $this->workloadCount($content, 'phong van qua han chua cham') > 0
                    ? 'Có phỏng vấn nào cần chấm ngay?'
                    : null,
                $this->workloadCount($content, 'cv cho sang loc') > 0
                    ? 'Hồ sơ nào đang chờ sàng lọc?'
                    : null,
                $this->workloadCount($content, 'de nghi tuyen dung nhap') > 0
                    ? 'Đề nghị tuyển dụng nào đang nháp?'
                    : null,
            ],
            'employer_pipeline_summary' => [
                'Hôm nay nên xử lý nhóm hồ sơ nào trước?',
                'Có hồ sơ nào quá hạn xử lý không?',
                'Tin tuyển dụng nào có nhiều hồ sơ nhất?',
            ],
            'director_pending_approvals' => [
                'Đề nghị nào cần duyệt trước?',
                'Hiệu quả tuyển dụng 30 ngày thế nào?',
                'HR nào đang có nhiều hồ sơ mở?',
            ],
            'employer_offer_drafts' => [
                'Có đề nghị nào chờ giám đốc duyệt?',
                'Đề nghị nào sắp hết hạn phản hồi?',
                'Hôm nay có hồ sơ nào cần xử lý?',
            ],
            'employer_offer_expiring' => [
                'Có đề nghị nào chờ giám đốc duyệt?',
                'Hồ sơ nào đang chờ sàng lọc?',
                'Có phỏng vấn nào cần chấm ngay?',
            ],
            'director_hr_workload' => [
                'HR nào đang có nhiều hồ sơ mở?',
                'Có việc nào đang quá hạn không?',
                'Quy trình tuyển dụng đang vướng ở đâu?',
            ],
            'director_performance' => [
                'Có đề nghị nào đang chờ duyệt?',
                'HR nào đang có nhiều hồ sơ mở?',
                'Có việc nào đang quá hạn không?',
            ],
            default => $fallback,
        };

        $cleaned = collect($suggestions)
            ->filter(fn ($suggestion): bool => is_string($suggestion) && trim($suggestion) !== '')
            ->map(fn (string $suggestion): string => trim($suggestion))
            ->unique()
            ->take(3)
            ->values()
            ->all();

        return $cleaned !== [] ? $cleaned : array_slice($fallback, 0, 3);
    }

    /**
     * @param  array{key: string, label: string, content: string, url: string|null}  $source
     * @return array<int, string>
     */
    private function summarizeRecruitmentSource(array $source): array
    {
        return match ($source['key']) {
            'operational-workload' => $this->summarizeWorkload($source['content']),
            'recruitment-pipeline' => $this->summarizePipelineCounts($source['content']),
            'upcoming-interviews' => $this->summarizeUpcomingInterviews($source['content']),
            default => $this->summarizeGenericSource($source),
        };
    }

    /** @return array<int, string> */
    private function summarizeUpcomingInterviews(string $content): array
    {
        if (str_contains($content, 'Không có lịch phỏng vấn sắp tới')) {
            return ['- Hiện chưa có lịch phỏng vấn sắp tới.'];
        }

        $items = [];
        foreach (preg_split('/\n/u', trim($content)) ?: [] as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('|', $line))));
            if ($parts === []) {
                continue;
            }

            $time = $parts[0] ?? 'chưa rõ thời gian';
            $candidate = $parts[1] ?? 'ứng viên chưa rõ';
            $job = $parts[2] ?? 'vị trí chưa rõ';
            $mode = $parts[3] ?? null;
            $invite = $parts[array_key_last($parts)] ?? null;
            $suffix = trim(implode(', ', array_filter([$mode, $invite])));

            $items[] = '- '.$time.' - '.$candidate.' - '.$job.($suffix !== '' ? ' ('.$suffix.').' : '.');
        }

        return $items !== [] ? array_slice($items, 0, 5) : ['- Hiện chưa có lịch phỏng vấn sắp tới.'];
    }

    /** @return array<int, string> */
    private function summarizeWorkload(string $content): array
    {
        $labels = [
            'tin tuyen dung cho duyet' => 'tin tuyển dụng chờ duyệt',
            'cv cho sang loc' => 'hồ sơ chờ sàng lọc CV',
            'lich phong van chua gui thu moi' => 'lịch phỏng vấn chưa gửi thư mời',
            'phong van qua han chua cham' => 'phỏng vấn quá hạn chưa chấm',
            'de nghi tuyen dung nhap' => 'đề nghị tuyển dụng nháp',
            'de nghi cho giam doc duyet' => 'đề nghị chờ giám đốc duyệt',
            'de nghi sap het han' => 'đề nghị sắp hết hạn phản hồi',
        ];

        $normalized = $this->normalize($content);
        $items = [];

        foreach ($labels as $needle => $display) {
            if (preg_match('/'.preg_quote($needle, '/').'\s*:\s*(\d+)/u', $normalized, $matches) !== 1) {
                continue;
            }

            $count = (int) $matches[1];
            if ($count > 0) {
                $items[] = "- {$count} {$display}.";
            }
        }

        return $items !== [] ? $items : ['- Không có việc quá hạn hoặc hạng mục cần xử lý nổi bật.'];
    }

    /** @return array<int, string> */
    private function summarizePipelineCounts(string $content): array
    {
        if (preg_match('/\{.*\}/u', $content, $matches) !== 1) {
            return $this->summarizeGenericSource(['label' => 'Quy trình tuyển dụng', 'content' => $content, 'key' => 'recruitment-pipeline', 'url' => null]);
        }

        $counts = json_decode($matches[0], true);
        if (! is_array($counts)) {
            return [];
        }

        arsort($counts);

        $labels = [
            'cv_reviewing' => 'chờ sàng lọc CV',
            'screening' => 'đang sơ tuyển',
            'interview_scheduled' => 'đã lên lịch phỏng vấn',
            'interview_pending' => 'chờ đánh giá phỏng vấn',
            'interviewing' => 'đang phỏng vấn',
            'offered' => 'đang ở bước đề nghị tuyển dụng',
            'offer' => 'đang ở bước đề nghị tuyển dụng',
            'hired' => 'đã tuyển',
            'rejected' => 'đã từ chối',
        ];

        $items = [];
        foreach ($counts as $status => $count) {
            $count = (int) $count;
            if ($count <= 0) {
                continue;
            }

            $label = $labels[(string) $status] ?? str_replace('_', ' ', (string) $status);
            $items[] = "- {$count} hồ sơ {$label}.";
        }

        return $items !== [] ? array_slice($items, 0, 5) : ['- Hiện chưa có hồ sơ đang mở trong quy trình tuyển dụng.'];
    }

    /**
     * @param  array{key: string, label: string, content: string, url: string|null}  $source
     * @return array<int, string>
     */
    /** @return array<string, int> */
    private function pipelineCounts(string $content): array
    {
        if (preg_match('/\{.*\}/u', $content, $matches) !== 1) {
            return [];
        }

        $counts = json_decode($matches[0], true);
        if (! is_array($counts)) {
            return [];
        }

        return collect($counts)
            ->mapWithKeys(fn ($count, $status): array => [(string) $status => (int) $count])
            ->all();
    }

    private function summarizeGenericSource(array $source): array
    {
        $content = trim(preg_replace('/\s+/u', ' ', $source['content']) ?: '');
        if ($content === '') {
            return [];
        }

        return ['- '.$source['label'].': '.Str::limit($content, 220, '...')];
    }

    private function nextActionForIntent(string $intent, array $sources): string
    {
        $workload = collect($sources)->firstWhere('key', 'operational-workload');
        $content = is_array($workload) ? $this->normalize((string) $workload['content']) : '';

        if (str_contains($content, 'phong van qua han chua cham: 0') === false
            && preg_match('/phong van qua han chua cham\s*:\s*([1-9]\d*)/u', $content) === 1) {
            return 'Gợi ý: nên xử lý các buổi phỏng vấn quá hạn trước để tránh nghẽn bước đánh giá.';
        }

        if (preg_match('/cv cho sang loc\s*:\s*([1-9]\d*)/u', $content) === 1) {
            return 'Gợi ý: nên ưu tiên sàng lọc CV mới trước, sau đó mới kiểm tra các đề nghị tuyển dụng cần theo dõi.';
        }

        return match ($intent) {
            'employer_pipeline_summary' => 'Gợi ý: nên mở trang quản lý ứng tuyển để xem chi tiết các hồ sơ ở bước đang có nhiều hồ sơ nhất.',
            'director_pending_approvals' => 'Gợi ý: nên kiểm tra các đề nghị đang chờ duyệt và phản hồi sớm để HR tiếp tục xử lý với ứng viên.',
            'director_hr_workload' => 'Gợi ý: nên xem lại phân bổ hồ sơ nếu một HR đang giữ quá nhiều hồ sơ mở.',
            default => 'Gợi ý: nên bắt đầu từ hạng mục có số lượng lớn hoặc đã quá hạn để quy trình tuyển dụng không bị chậm.',
        };
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

    private function workloadCount(string $content, string $label): int
    {
        if (preg_match('/'.preg_quote($label, '/').'\s*:\s*(\d+)/u', $content, $matches) !== 1) {
            return 0;
        }

        return (int) $matches[1];
    }

    private function normalize(string $value): string
    {
        $normalized = Str::lower(Str::ascii(trim($value)));

        return preg_replace('/\s+/u', ' ', $normalized) ?: '';
    }
}
