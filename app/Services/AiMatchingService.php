<?php

namespace App\Services;

use App\Models\CandidateJobSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMatchingService
{
    protected ?string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
    }

    public function calculateMatch(CandidateJobSubmission $submission): bool
    {
        if (blank($this->apiKey)) {
            Log::error('AI Matching Failed: GEMINI_API_KEY is missing in .env');
            return false;
        }

        $jobDescription = $submission->job->description;
        $cvText = $submission->cv_text_snapshot;

        if (blank($jobDescription) || blank($cvText)) {
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

            return false;
        } catch (\Throwable $e) {
            Log::error('AI Matching Failed: ' . $e->getMessage());
            return false;
        }
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
