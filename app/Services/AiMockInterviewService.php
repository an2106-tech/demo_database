<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMockInterviewService
{
    protected ?string $apiKey;
    protected string $apiUrl;
    protected ?string $lastError = null;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-3.1-flash-lite');
        $this->apiUrl = config('services.gemini.url', 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent');
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function startInterview(Candidate $candidate, RecruitmentJob $job, ?Application $application = null): ?Chat
    {
        $chat = Chat::create([
            'candidate_id' => $candidate->id,
            'job_id' => $job->id,
            'type' => 'ai_mock_interview',
            'status' => 'active',
            'metadata' => [
                'current_step' => 1,
                'application_id' => $application?->id,
            ],
        ]);

        $firstQuestionText = $this->generateQuestion($job, $candidate, 1, []);

        if (blank($firstQuestionText)) {
            $firstQuestionText = 'Chào bạn! Hãy giới thiệu bản thân và lý do bạn muốn ứng tuyển vị trí ' . $job->title . ' tại FPT.';
        }

        ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'ai',
            'content' => $firstQuestionText,
            'metadata' => [
                'question_number' => 1,
            ],
        ]);

        return $chat;
    }

    public function submitAnswer(Chat $chat, string $userAnswer): bool
    {
        $metadata = $chat->metadata ?? [];
        $currentStep = $metadata['current_step'] ?? 1;
        
        $currentMessage = $chat->messages()->where('sender_type', 'ai')
            ->whereJsonContains('metadata->question_number', $currentStep)->first();

        if (! $currentMessage) {
            $this->lastError = 'Không tìm thấy câu hỏi hiện tại.';
            return false;
        }

        // Save user answer
        ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'candidate',
            'sender_id' => $chat->candidate_id,
            'content' => $userAnswer,
            'metadata' => [
                'answered_to' => $currentStep,
            ],
        ]);

        // Evaluate answer using Gemini
        $eval = $this->evaluateAnswer($chat->job, $currentMessage->content, $userAnswer);

        $currentMessageMsgData = $currentMessage->metadata ?? [];
        $currentMessage->update([
            'metadata' => array_merge($currentMessageMsgData, [
                'score' => $eval['score'] ?? 7,
                'feedback' => $eval['feedback'] ?? 'Câu trả lời của bạn thể hiện sự tự tin và thiện chí.',
                'suggested_answer' => $eval['suggested_answer'] ?? 'Nên làm rõ thêm kết quả đạt được cụ thể.',
            ]),
        ]);

        if ($currentStep < 5) {
            // Create Next Question
            $nextStep = $currentStep + 1;
            $historyRaw = $chat->messages()->orderBy('created_at')->get();
            $history = [];
            foreach ($historyRaw as $msg) {
                if ($msg->sender_type === 'ai') {
                    $history[] = ['question_number' => $msg->metadata['question_number'] ?? 0, 'question_text' => $msg->content];
                } elseif ($msg->sender_type === 'candidate' && count($history) > 0) {
                    $history[count($history) - 1]['answer_text'] = $msg->content;
                }
            }

            $nextQuestionText = $this->generateQuestion($chat->job, $chat->candidate, $nextStep, $history);

            if (blank($nextQuestionText)) {
                $nextQuestionText = 'Bạn đã bao giờ gặp khó khăn hoặc sự cố khi làm dự án thực tế chưa, và bạn xử lý ra sao?';
            }

            ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_type' => 'ai',
                'content' => $nextQuestionText,
                'metadata' => [
                    'question_number' => $nextStep,
                ],
            ]);

            $metadata['current_step'] = $nextStep;
            $chat->update(['metadata' => $metadata]);
        } else {
            // Finish Interview & Generate Final Summary
            $chat->load('messages');
            $summary = $this->generateFinalReport($chat);

            $aiMessages = $chat->messages->where('sender_type', 'ai');
            $avgScore = $aiMessages->map(fn($m) => $m->metadata['score'] ?? 0)->avg() ?? 0;

            $metadata['summary_feedback'] = $summary;
            $metadata['total_score'] = $summary['total_score'] ?? round($avgScore * 10);
            
            $chat->update([
                'status' => 'completed',
                'metadata' => $metadata,
            ]);
        }

        return true;
    }

    protected function generateQuestion(RecruitmentJob $job, Candidate $candidate, int $stepNumber, array $history): string
    {
        if (blank($this->apiKey)) {
            return $this->getFallbackQuestion($job->title, $stepNumber);
        }

        // Build resume text from JSON columns (no single resume_text column)
        $resume = CandidateResume::where('candidate_id', $candidate->id)->first();
        $resumeText = '';
        if ($resume) {
            $parts = [];
            if (filled($resume->profile_title)) {
                $parts[] = 'Tiêu đề hồ sơ: ' . $resume->profile_title;
            }
            if (filled($resume->career_objective)) {
                $parts[] = 'Mục tiêu nghề nghiệp: ' . strip_tags($resume->career_objective);
            }
            if (!empty($resume->experiences)) {
                $exps = is_array($resume->experiences) ? $resume->experiences : json_decode($resume->experiences, true);
                foreach ((array) $exps as $exp) {
                    $parts[] = 'Kinh nghiệm: ' . ($exp['position'] ?? '') . ' tại ' . ($exp['company'] ?? '');
                }
            }
            if (!empty($resume->skills)) {
                $sks = is_array($resume->skills) ? $resume->skills : json_decode($resume->skills, true);
                $skillNames = collect((array) $sks)->pluck('name')->filter()->implode(', ');
                if (filled($skillNames)) {
                    $parts[] = 'Kỹ năng: ' . $skillNames;
                }
            }
            if (!empty($resume->educations)) {
                $edus = is_array($resume->educations) ? $resume->educations : json_decode($resume->educations, true);
                foreach ((array) $edus as $edu) {
                    $parts[] = 'Học vấn: ' . ($edu['school'] ?? '') . ' — ' . ($edu['major'] ?? '');
                }
            }
            $resumeText = implode("\n", $parts);
        }

        $prompt = "Bạn là Trưởng phòng phỏng vấn tuyển dụng tại tập đoàn FPT.\n";
        $prompt .= "Hãy đưa ra CÂU HỎI PHỎNG VẤN SỐ {$stepNumber} (trên tổng số 5 câu) dành cho ứng viên ứng tuyển vị trí: {$job->title}.\n";
        $prompt .= "Mô tả công việc (JD): " . strip_tags($job->description ?? '') . "\n";
        if (filled($resumeText)) {
            $prompt .= "Tóm tắt CV Ứng viên: " . mb_substr($resumeText, 0, 500) . "\n";
        }

        if (! empty($history)) {
            $prompt .= "Các câu hỏi và trả lời trước đó:\n";
            foreach ($history as $h) {
                $prompt .= "- Câu hỏi " . ($h['question_number'] ?? '') . ": " . ($h['question_text'] ?? '') . "\n";
                if (! empty($h['answer_text'])) {
                    $prompt .= "  Trả lời: " . $h['answer_text'] . "\n";
                }
            }
        }

        $prompt .= "\nYêu cầu:\n";
        $prompt .= "- Nếu step=1: Hỏi chào hỏi + kinh nghiệm tổng quan liên quan đến vị trí.\n";
        $prompt .= "- Nếu step=2 hoặc 3: Hỏi sâu về Kỹ năng chuyên môn / Công nghệ nêu trong JD.\n";
        $prompt .= "- Nếu step=4: Hỏi về tình huống giải quyết xung đột, áp lực hoặc làm việc nhóm.\n";
        $prompt .= "- Nếu step=5: Hỏi về định hướng phát triển sự nghiệp và mức độ sẵn sàng gắn bó với FPT.\n";
        $prompt .= "- Trả về JSON duy nhất định dạng: {\"question\": \"Nội dung câu hỏi phỏng vấn tự nhiên, lịch sự và sát thực tế\"}";

        $res = $this->callGeminiJson($prompt);
        return (string) ($res['question'] ?? $this->getFallbackQuestion($job->title, $stepNumber));
    }

    protected function evaluateAnswer(RecruitmentJob $job, string $question, string $answer): array
    {
        if (blank($this->apiKey)) {
            return [
                'score' => 8,
                'feedback' => 'Câu trả lời đầy đủ, thể hiện sự am hiểu vị trí tuyển dụng.',
                'suggested_answer' => 'Nên bổ sung thêm ví dụ thực tế và số liệu cụ thể.',
            ];
        }

        $prompt = "Bạn là Chuyên gia Tuyển dụng tại FPT.\n";
        $prompt .= "Hãy đánh giá câu trả lời phỏng vấn của ứng viên cho vị trí: {$job->title}.\n";
        $prompt .= "Câu hỏi: {$question}\n";
        $prompt .= "Câu trả lời của ứng viên: {$answer}\n\n";
        $prompt .= "Yêu cầu trả về JSON có dạng:\n{\n";
        $prompt .= "  \"score\": 8, // thang điểm 1-10 (số nguyên)\n";
        $prompt .= "  \"feedback\": \"Nhận xét ưu điểm và nhược điểm ngắn gọn (2-3 câu)\",\n";
        $prompt .= "  \"suggested_answer\": \"Gợi ý cách trả lời hoàn hảo nhất để ghi điểm tuyệt đối\"\n";
        $prompt .= "}";

        $res = $this->callGeminiJson($prompt);
        return [
            'score' => (int) ($res['score'] ?? 7),
            'feedback' => (string) ($res['feedback'] ?? 'Câu trả lời khá tốt.'),
            'suggested_answer' => (string) ($res['suggested_answer'] ?? 'Nên cung cấp thêm ví dụ dự án cụ thể.'),
        ];
    }

    protected function generateFinalReport(Chat $chat): array
    {
        if (blank($this->apiKey)) {
            return [
                'total_score' => 82,
                'pros' => ['Tự tin giao tiếp', 'Am hiểu chuyên môn vị trí', 'Thái độ cầu thị'],
                'cons' => ['Cần bổ sung số liệu minh chứng dự án', 'Nên đào sâu hơn về xử lý tình huống'],
                'recommendation' => 'Ứng viên đạt yêu cầu phỏng vấn thử, đáp ứng tốt yêu cầu công việc.',
            ];
        }

        $job = $chat->job;
        $prompt = "Bạn là Trưởng phòng Tuyển dụng FPT. Hãy tổng hợp phiên phỏng vấn thử 5 câu của ứng viên cho vị trí: {$job->title}.\n\n";
        $messagesRaw = $chat->messages()->orderBy('created_at')->get();
        $history = [];
        foreach ($messagesRaw as $msg) {
            if ($msg->sender_type === 'ai') {
                $history[] = [
                    'question_number' => $msg->metadata['question_number'] ?? 0, 
                    'question_text' => $msg->content,
                    'score' => $msg->metadata['score'] ?? 0,
                    'feedback' => $msg->metadata['feedback'] ?? ''
                ];
            } elseif ($msg->sender_type === 'candidate' && count($history) > 0) {
                $history[count($history) - 1]['answer_text'] = $msg->content;
            }
        }

        foreach ($history as $h) {
            $prompt .= "Câu " . ($h['question_number'] ?? '') . ": " . ($h['question_text'] ?? '') . "\n";
            $prompt .= "Trả lời: " . ($h['answer_text'] ?? 'Chưa trả lời') . "\n";
            $prompt .= "Điểm: " . ($h['score'] ?? '') . "/10. Nhận xét: " . ($h['feedback'] ?? '') . "\n---\n";
        }

        $prompt .= "\nYêu cầu trả về JSON:\n{\n";
        $prompt .= "  \"total_score\": 85, // thang điểm 0-100\n";
        $prompt .= "  \"pros\": [\"Điểm mạnh 1\", \"Điểm mạnh 2\", \"Điểm mạnh 3\"],\n";
        $prompt .= "  \"cons\": [\"Điểm cần cải thiện 1\", \"Điểm cần cải thiện 2\"],\n";
        $prompt .= "  \"recommendation\": \"Đánh giá tổng quan và lời khuyên truyền cảm hứng cho ứng viên trước phỏng vấn thật\"\n";
        $prompt .= "}";

        $res = $this->callGeminiJson($prompt);
        
        $aiMessages = $chat->messages->where('sender_type', 'ai');
        $avgScore = $aiMessages->map(fn($m) => $m->metadata['score'] ?? 0)->avg() ?? 0;
        
        return [
            'total_score' => (int) ($res['total_score'] ?? round($avgScore * 10)),
            'pros' => (array) ($res['pros'] ?? ['Kinh nghiệm phù hợp', 'Trả lời tự tin']),
            'cons' => (array) ($res['cons'] ?? ['Cần làm rõ chi tiết dự án']),
            'recommendation' => (string) ($res['recommendation'] ?? 'Ứng viên có tiềm năng tốt, hãy tự tin bước vào phỏng vấn thật.'),
        ];
    }

    protected function callGeminiJson(string $prompt): array
    {
        try {
            $response = Http::timeout(30)->withHeaders([
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

            if ($response->successful()) {
                $text = (string) $response->json('candidates.0.content.parts.0.text');
                $text = preg_replace('/^```json\s*|\s*```$/i', '', trim($text));
                $data = json_decode($text, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            Log::error('AiMockInterviewService Gemini Error: ' . $e->getMessage());
        }

        return [];
    }

    protected function getFallbackQuestion(string $jobTitle, int $stepNumber): string
    {
        return match ($stepNumber) {
            1 => "Chào bạn! Bạn có thể giới thiệu bản thân và lý do bạn muốn ứng tuyển vị trí {$jobTitle} tại FPT không?",
            2 => "Theo bạn, những kỹ năng quan trọng nhất để làm tốt vị trí {$jobTitle} là gì và bạn đã áp dụng chúng như thế nào?",
            3 => "Hãy mô tả một dự án hoặc bài toán khó nhất bạn từng giải quyết liên quan tới công việc này?",
            4 => "Khi làm việc nhóm xảy ra bất đồng ý kiến về giải pháp kỹ thuật, bạn thường xử lý thế nào?",
            5 => "Định hướng phát triển sự nghiệp của bạn trong 2-3 năm tới tại FPT là gì?",
            default => "Bạn có thắc mắc gì thêm về vị trí công việc này không?",
        };
    }
}
