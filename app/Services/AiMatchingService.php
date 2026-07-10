<?php

namespace App\Services;

use App\Models\CandidateJobSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMatchingService
{
    protected ?string $apiKey;
    protected string $apiUrl;
    protected ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        $model = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent';
    }

    public function calculateMatch(CandidateJobSubmission $submission): bool
    {
        $this->lastError = null;

        if (blank($this->apiKey)) {
            $this->lastError = 'Chưa cấu hình GEMINI_API_KEY nên không thể phân tích AI.';
            Log::error('AI Matching Failed: GEMINI_API_KEY is missing in .env');
            return false;
        }

        $jobDescription = $submission->job?->description;
        $cvText = $submission->cv_text_snapshot;

        if (blank($jobDescription) || blank($cvText)) {
            $this->lastError = 'Thiếu mô tả công việc hoặc nội dung CV để AI phân tích.';
            return false;
        }

        $prompt = $this->buildPrompt($jobDescription, $cvText);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->failed()) {
                $this->lastError = $this->formatProviderError($response);
                Log::error('Gemini API Error: ' . $response->body());
                return false;
            }

            $content = $response->json('candidates.0.content.parts.0.text');
            $data = json_decode($content, true);

            if (isset($data['score'])) {
                $submission->update([
                    'ai_matching_score' => $data['score'],
                    'ai_analysis' => $data,
                ]);
                return true;
            }

            $this->lastError = 'AI trả về dữ liệu không đúng định dạng JSON.';
            return false;
        } catch (\Throwable $e) {
            $this->lastError = mb_substr($e->getMessage(), 0, 1000);
            Log::error('AI Matching Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
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

    protected function buildPrompt(string $jd, string $cv): string
    {
        return <<<PROMPT
            Bạn là một chuyên gia tuyển dụng cao cấp. Nhiệm vụ của bạn là so sánh văn bản CV của ứng viên với Mô tả công việc (JD) để đánh giá độ phù hợp.

            Mô tả công việc (JD):
            "$jd"

            Văn bản CV của ứng viên:
            "$cv"

            Yêu cầu:
            1. Chấm điểm độ phù hợp trên thang điểm 100.
            2. Liệt kê tối đa 3 lý do chính tại sao ứng viên phù hợp (match_reasons).
            3. Liệt kê tối đa 3 điểm yếu hoặc kỹ năng còn thiếu so với JD (missing_skills).

            Hãy trả về kết quả duy nhất dưới định dạng JSON sau:
            {
                "score": 85,
                "match_reasons": ["Lý do 1", "Lý do 2", "Lý do 3"],
                "missing_skills": ["Kỹ năng 1", "Kỹ năng 2"]
            }
        PROMPT;
    }
}
