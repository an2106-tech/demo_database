<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationAiAnalysis;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApplicationAiAnalysisService
{
    private string $provider = 'gemini';

    private string $model;

    private string $apiUrl;

    public function __construct(
        private CvExtractionService $cvExtractionService,
    ) {
        $this->model = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'.$this->model.':generateContent';
    }

    public function analyzeScreening(
        Application $application,
        ?User $user = null,
        string $source = 'admin',
        bool $force = false,
    ): ApplicationAiAnalysis {
        $application->loadMissing(['job', 'candidate', 'latestScreeningAiAnalysis']);

        $existing = $application->latestScreeningAiAnalysis;
        if (! $force && $existing?->status === 'completed') {
            return $existing;
        }

        $analysis = new ApplicationAiAnalysis([
            'application_id' => $application->id,
            'analysis_type' => 'screening',
            'status' => 'processing',
            'provider' => $this->provider,
            'model' => $this->model,
            'created_by' => $user?->id,
            'created_from' => $source,
        ]);
        $analysis->save();

        $extraction = $this->cvExtractionService->ensureForApplication($application);
        $cvText = $extraction?->extracted_text ?: $application->cv_text_snapshot;

        if (blank($cvText)) {
            return $this->markFailed(
                $analysis,
                'Chưa có nội dung CV để phân tích. Vui lòng kiểm tra file CV hoặc cấu hình công cụ trích xuất PDF/DOCX.'
            );
        }

        $job = $application->job;
        $jobText = trim(implode("\n\n", array_filter([
            $job?->title ? 'Vị trí: '.$job->title : null,
            $job?->description ? 'Mô tả công việc: '.$job->description : null,
            $job?->requirements ? 'Yêu cầu: '.$job->requirements : null,
            $job?->benefits ? 'Quyền lợi: '.$job->benefits : null,
        ])));

        if ($jobText === '') {
            return $this->markFailed($analysis, 'Tin tuyển dụng chưa có đủ mô tả/yêu cầu để AI so khớp.');
        }

        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        if (blank($apiKey)) {
            return $this->markFailed($analysis, 'Chưa cấu hình GEMINI_API_KEY nên không thể phân tích AI.');
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl.'?key='.$apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $this->buildScreeningPrompt($application, $jobText, (string) $cvText)],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'response_mime_type' => 'application/json',
                    ],
                ]);

            if ($response->failed()) {
                return $this->markFailed($analysis, $this->formatProviderError($response), $response->body());
            }

            $raw = (string) $response->json('candidates.0.content.parts.0.text', '');
            $result = $this->decodeJson($raw);

            if (! is_array($result)) {
                return $this->markFailed($analysis, 'AI trả về dữ liệu không đúng định dạng JSON.', $raw);
            }

            $normalized = $this->normalizeResult($result);

            $analysis->forceFill([
                'cv_extraction_id' => $extraction?->id,
                'status' => 'completed',
                'score' => $normalized['score'],
                'recommendation' => $normalized['recommendation'],
                'summary' => $normalized['summary'],
                'strengths' => $normalized['strengths'],
                'gaps' => $normalized['gaps'],
                'suggested_note' => $normalized['suggested_note'],
                'result_json' => $normalized['result_json'],
                'raw_response' => $raw,
                'error_message' => null,
                'analyzed_at' => now(),
            ])->save();

            return $analysis;
        } catch (\Throwable $exception) {
            Log::warning('Application AI analysis failed.', [
                'application_id' => $application->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->markFailed($analysis, mb_substr($exception->getMessage(), 0, 1000));
        }
    }

    public function generateInterviewQuestions(
        Application $application,
        array $criteria = [],
        ?User $user = null,
        string $source = 'admin',
        bool $force = false,
    ): ApplicationAiAnalysis {
        $application->loadMissing(['job', 'candidate', 'latestScreeningAiAnalysis', 'latestInterviewQuestionAiAnalysis']);

        $existing = $application->latestInterviewQuestionAiAnalysis;
        if (! $force && $existing?->status === 'completed') {
            return $existing;
        }

        $screening = $application->latestScreeningAiAnalysis?->status === 'completed'
            ? $application->latestScreeningAiAnalysis
            : $application->aiAnalyses()
                ->where('analysis_type', 'screening')
                ->where('status', 'completed')
                ->latest('id')
                ->first();
        if (! $screening || $screening->status !== 'completed') {
            $analysis = $this->newAnalysis($application, 'interview_questions', $user, $source);

            return $this->markFailed($analysis, 'Chưa có kết quả sàng lọc AI để tạo câu hỏi phỏng vấn.');
        }

        $gaps = array_values(array_filter((array) $screening->gaps, fn ($value): bool => filled($value)));
        if ($gaps === []) {
            $analysis = $this->newAnalysis($application, 'interview_questions', $user, $source);

            return $this->markFailed($analysis, 'Chưa có điểm cần làm rõ từ bước sàng lọc.');
        }

        $criteriaNames = collect($criteria)
            ->map(fn ($criterion) => is_array($criterion) ? ($criterion['name'] ?? null) : $criterion)
            ->filter(fn ($value): bool => filled($value))
            ->map(fn ($value): string => trim((string) $value))
            ->unique()
            ->values()
            ->all();

        if ($criteriaNames === []) {
            $criteriaNames = [
                'Kinh nghiệm phù hợp vị trí',
                'Kỹ năng chuyên môn',
                'Tư duy giải quyết vấn đề',
                'Kỹ năng giao tiếp',
                'Mức độ phù hợp văn hóa',
            ];
        }

        $job = $application->job;
        $jobText = trim(implode("\n\n", array_filter([
            $job?->title ? 'Vị trí: '.$job->title : null,
            $job?->description ? 'Mô tả công việc: '.$job->description : null,
        ])));

        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $analysis = $this->newAnalysis($application, 'interview_questions', $user, $source);

        if (blank($apiKey)) {
            return $this->markFailed($analysis, 'Chưa cấu hình GEMINI_API_KEY nên không thể tạo câu hỏi phỏng vấn.');
        }

        try {
            $response = Http::timeout(45)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl.'?key='.$apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $this->buildInterviewQuestionPrompt(
                                    $application,
                                    $jobText,
                                    (string) $screening->summary,
                                    $gaps,
                                    $criteriaNames,
                                )],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.25,
                        'response_mime_type' => 'application/json',
                    ],
                ]);

            if ($response->failed()) {
                return $this->markFailed($analysis, $this->formatProviderError($response), $response->body());
            }

            $raw = (string) $response->json('candidates.0.content.parts.0.text', '');
            $result = $this->decodeJson($raw);

            if (! is_array($result)) {
                return $this->markFailed($analysis, 'AI trả về dữ liệu câu hỏi không đúng định dạng JSON.', $raw);
            }

            $questions = $this->normalizeInterviewQuestions($result['questions'] ?? []);
            if ($questions === []) {
                return $this->markFailed($analysis, 'AI chưa tạo được câu hỏi phù hợp.', $raw);
            }

            $analysis->forceFill([
                'status' => 'completed',
                'summary' => 'Câu hỏi gợi ý phỏng vấn được tạo từ điểm cần làm rõ và tiêu chí scorecard.',
                'result_json' => [
                    'questions' => $questions,
                    'criteria' => $criteriaNames,
                    'gaps' => array_slice($gaps, 0, 5),
                ],
                'raw_response' => $raw,
                'error_message' => null,
                'analyzed_at' => now(),
            ])->save();

            return $analysis;
        } catch (\Throwable $exception) {
            Log::warning('Interview question generation failed.', [
                'application_id' => $application->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->markFailed($analysis, mb_substr($exception->getMessage(), 0, 1000));
        }
    }

    protected function newAnalysis(
        Application $application,
        string $type,
        ?User $user,
        string $source,
    ): ApplicationAiAnalysis {
        $analysis = new ApplicationAiAnalysis([
            'application_id' => $application->id,
            'analysis_type' => $type,
            'status' => 'processing',
            'provider' => $this->provider,
            'model' => $this->model,
            'created_by' => $user?->id,
            'created_from' => $source,
        ]);
        $analysis->save();

        return $analysis;
    }

    protected function formatProviderError($response): string
    {
        $status = $response->status();
        $providerStatus = (string) ($response->json('error.status') ?: '');
        $providerMessage = (string) ($response->json('error.message') ?: $response->body());

        if ($status === 503 || $providerStatus === 'UNAVAILABLE') {
            return 'Model AI đang quá tải tạm thời. Vui lòng thử lại sau ít phút.';
        }

        if ($status === 404) {
            return 'Model AI hiện không khả dụng với cấu hình hiện tại. Vui lòng kiểm tra GEMINI_MODEL.';
        }

        if (in_array($status, [401, 403], true)) {
            return 'API key AI không hợp lệ hoặc chưa có quyền sử dụng model hiện tại.';
        }

        return mb_substr($providerMessage, 0, 1000);
    }

    protected function buildScreeningPrompt(Application $application, string $jobText, string $cvText): string
    {
        $candidateName = $application->snapshotCandidateName();
        $candidateProfile = trim(implode("\n", array_filter([
            'Tên ứng viên: '.$candidateName,
            $application->snapshotProfileTitle() ? 'Tiêu đề hồ sơ: '.$application->snapshotProfileTitle() : null,
            filled($application->snapshotCandidateExperienceYears()) ? 'Kinh nghiệm: '.$application->snapshotCandidateExperienceYears().' năm' : null,
        ])));

        $cvText = mb_substr($cvText, 0, 30000);
        $jobText = mb_substr($jobText, 0, 12000);

        return <<<PROMPT
Bạn là trợ lý tuyển dụng cho hệ thống tuyển dụng FPT Career. Hãy hỗ trợ HR sàng lọc CV bằng cách so khớp CV ứng viên với JD.

Quy tắc:
- Chỉ đưa ra gợi ý, không thay HR quyết định.
- Không suy đoán thông tin không có trong CV/JD.
- Nếu thiếu dữ liệu, ghi rõ là cần xác minh thêm.
- Ưu tiên căn cứ có thể đối chiếu từ CV/JD, tránh nhận xét chung chung.
- Đánh giá theo góc nhìn HR ở bước sàng lọc CV: có đủ cơ sở chuyển bước tiếp theo hay cần làm rõ/từ chối.
- Đồng thời tạo bản tóm tắt rất ngắn cho giám đốc chi nhánh khi xem đề nghị tuyển dụng ở bước sau.
- Trả về JSON hợp lệ, không bọc markdown.

Thông tin ứng viên:
{$candidateProfile}

JD/yêu cầu tuyển dụng:
{$jobText}

Nội dung CV:
{$cvText}

Trả về đúng cấu trúc JSON:
{
  "score": 78,
  "recommendation": "pass|consider|reject",
  "summary": "Tóm tắt 2-3 câu về mức độ phù hợp của ứng viên.",
  "strengths": ["Điểm phù hợp 1", "Điểm phù hợp 2", "Điểm phù hợp 3"],
  "gaps": ["Điểm cần làm rõ hoặc còn thiếu 1", "Điểm cần làm rõ hoặc còn thiếu 2"],
  "evidence": ["Căn cứ cụ thể từ CV/JD giúp HR đối chiếu"],
  "risks": ["Rủi ro hoặc điểm còn mơ hồ cần cân nhắc"],
  "next_step_hint": "Gợi ý bước tiếp theo cho HR, ví dụ chuyển phỏng vấn và làm rõ điểm nào.",
  "suggested_note": "Gợi ý ghi chú sàng lọc ngắn để HR có thể chỉnh sửa trước khi lưu.",
  "director_brief": {
    "summary": "Tóm tắt 1-2 câu cho giám đốc chi nhánh khi duyệt đề nghị tuyển dụng.",
    "key_points": ["Căn cứ chính để xem xét duyệt đề nghị"],
    "risks": ["Điểm cần lưu ý trước khi duyệt nếu có"],
    "decision_support": "Gợi ý hỗ trợ xem xét, không thay quyết định duyệt/từ chối."
  }
}
PROMPT;
    }

    protected function buildInterviewQuestionPrompt(
        Application $application,
        string $jobText,
        string $screeningSummary,
        array $gaps,
        array $criteria,
    ): string {
        $candidateName = $application->snapshotCandidateName();
        $gapsText = collect($gaps)->take(5)->map(fn ($gap, $index): string => ($index + 1).'. '.$gap)->implode("\n");
        $criteriaText = collect($criteria)->take(8)->map(fn ($criterion, $index): string => ($index + 1).'. '.$criterion)->implode("\n");
        $screeningSummary = mb_substr($screeningSummary, 0, 1800);
        $jobText = mb_substr($jobText, 0, 5000);

        return <<<PROMPT
Bạn là trợ lý tuyển dụng cho hệ thống FPT Career. Hãy tạo câu hỏi gợi ý cho người phỏng vấn.

Quy tắc:
- Chỉ tạo câu hỏi tham khảo, không thay người phỏng vấn đánh giá.
- Ưu tiên các điểm cần làm rõ từ bước sàng lọc và tiêu chí scorecard.
- Nếu CV có dự án hoặc kinh nghiệm gần nhất, hãy hỏi sâu vào dự án/kinh nghiệm đó để kiểm chứng vai trò thật.
- Không tạo câu hỏi Có/Không, câu hỏi quá cơ bản, câu hỏi định nghĩa lý thuyết đơn giản hoặc câu hỏi đã rõ trong CV.
- Ưu tiên câu hỏi tình huống, bài toán thực tế, quyết định kỹ thuật, kết quả đạt được hoặc mức độ tham gia thật.
- Gắn mỗi câu hỏi với một tiêu chí scorecard phù hợp nếu có thể.
- Câu hỏi phải cụ thể, lịch sự, dùng được trong phỏng vấn thực tế.
- Mỗi câu hỏi là một câu duy nhất, tối đa 95 ký tự.
- Không giải thích dài trong trường question; phần giải thích ngắn đặt ở purpose.
- Purpose tối đa 70 ký tự.
- expected_signal mô tả ngắn dấu hiệu của câu trả lời tốt, tối đa 110 ký tự.
- Không hỏi thông tin nhạy cảm hoặc không liên quan công việc.
- Trả về JSON hợp lệ, không bọc markdown.

Ứng viên: {$candidateName}

Thông tin vị trí:
{$jobText}

Tóm tắt sàng lọc:
{$screeningSummary}

Điểm cần làm rõ:
{$gapsText}

Tiêu chí scorecard:
{$criteriaText}

Trả về đúng cấu trúc JSON:
{
  "questions": [
    {
      "criterion": "Tên tiêu chí scorecard phù hợp",
      "type": "project_deep_dive|gap_validation|scenario|risk_check",
      "question": "Câu hỏi phỏng vấn cụ thể",
      "purpose": "Mục đích hỏi, gắn với điểm cần làm rõ",
      "expected_signal": "Dấu hiệu người phỏng vấn nên nghe trong câu trả lời tốt"
    }
  ]
}

Yêu cầu số lượng: đúng 4 câu hỏi nếu dữ liệu đủ, tối thiểu 3 câu nếu dữ liệu hạn chế.
Phân bổ ưu tiên: 1 câu đào sâu dự án/kinh nghiệm gần nhất, 1 câu làm rõ gap quan trọng nhất, 1 câu tình huống sát JD, 1 câu kiểm tra rủi ro hoặc mức độ tham gia thật.
PROMPT;
    }

    protected function decodeJson(string $raw): ?array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;
        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    protected function normalizeResult(array $result): array
    {
        $score = (int) max(0, min(100, (int) ($result['score'] ?? 0)));
        $recommendation = (string) ($result['recommendation'] ?? 'consider');
        if (! in_array($recommendation, ['pass', 'consider', 'reject'], true)) {
            $recommendation = match (true) {
                $score >= 75 => 'pass',
                $score < 50 => 'reject',
                default => 'consider',
            };
        }

        $strengths = array_values(array_filter((array) ($result['strengths'] ?? []), fn ($value): bool => filled($value)));
        $gaps = array_values(array_filter((array) ($result['gaps'] ?? []), fn ($value): bool => filled($value)));

        return [
            'score' => $score,
            'recommendation' => $recommendation,
            'summary' => trim((string) ($result['summary'] ?? '')),
            'strengths' => array_slice($strengths, 0, 5),
            'gaps' => array_slice($gaps, 0, 5),
            'suggested_note' => trim((string) ($result['suggested_note'] ?? '')),
            'result_json' => $result,
        ];
    }

    protected function normalizeInterviewQuestions(mixed $questions): array
    {
        return collect((array) $questions)
            ->filter(fn ($question): bool => is_array($question) && filled($question['question'] ?? null))
            ->map(fn (array $question): array => [
                'criterion' => $this->normalizeText((string) ($question['criterion'] ?? '')),
                'type' => $this->normalizeText((string) ($question['type'] ?? '')),
                'question' => $this->normalizeText((string) ($question['question'] ?? '')),
                'purpose' => $this->normalizeText((string) ($question['purpose'] ?? '')),
                'expected_signal' => $this->normalizeText((string) ($question['expected_signal'] ?? '')),
            ])
            ->take(4)
            ->values()
            ->all();
    }

    protected function normalizeText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    protected function markFailed(ApplicationAiAnalysis $analysis, string $message, ?string $rawResponse = null): ApplicationAiAnalysis
    {
        $analysis->forceFill([
            'status' => 'failed',
            'error_message' => $message,
            'raw_response' => $rawResponse,
            'analyzed_at' => now(),
        ])->save();

        return $analysis;
    }
}
