<?php

namespace App\Livewire;

use App\Exceptions\AiChatException;
use App\Models\User;
use App\Services\AiChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class AiChatbox extends Component
{
    public string $audience = 'candidate';

    public bool $enabled = false;

    public bool $isOpen = false;

    public string $message = '';

    public ?string $error = null;

    public string $assistantTitle = 'AI Career Assistant';

    public string $assistantSubtitle = 'Hồ sơ, việc làm và định hướng';

    public string $assistantDescription = 'Mình có thể hỗ trợ tìm việc, đọc hồ sơ ứng tuyển và tư vấn cải thiện CV.';

    /** @var array<int, string> */
    public array $quickPrompts = [];

    /** @var array<int, array<string, mixed>> */
    public array $messages = [];

    public int $messageSequence = 0;

    public function mount(?string $audience = null): void
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->is_active) {
            return;
        }

        $this->audience = $this->resolveAudience($audience);
        $this->enabled = $this->canUseAudience($user, $this->audience);

        if (! $this->enabled) {
            return;
        }

        $this->configureAssistant($user);
    }

    public function toggle(): void
    {
        if ($this->enabled) {
            $this->isOpen = ! $this->isOpen;
            $this->error = null;
        }
    }

    public function newConversation(): void
    {
        $this->authorizeEnabledUser();
        $this->messages = [];
        $this->messageSequence = 0;
        $this->message = '';
        $this->error = null;
    }

    public function rateMessage(int $messageId, string $feedback): void
    {
        $this->authorizeEnabledUser();
        abort_unless(in_array($feedback, ['helpful', 'not_helpful'], true), 422);

        foreach ($this->messages as $index => $message) {
            if ($message['id'] === $messageId && $message['role'] === 'assistant' && $message['status'] === 'completed') {
                $this->messages[$index]['feedback'] = $message['feedback'] === $feedback ? null : $feedback;

                return;
            }
        }

        abort(404);
    }

    public function useSuggestion(string $suggestion): void
    {
        $this->message = mb_substr(trim($suggestion), 0, 1000);
        $this->error = null;
    }

    public function sendMessage(AiChatService $chatService): void
    {
        $user = $this->authorizeEnabledUser();
        $validated = $this->validate([
            'message' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'message.required' => 'Vui lòng nhập câu hỏi.',
            'message.min' => 'Câu hỏi cần có ít nhất 2 ký tự.',
            'message.max' => 'Câu hỏi không được vượt quá 1.000 ký tự.',
        ]);

        $rateKey = 'ai-chat:'.$user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            $this->error = 'Bạn đang gửi câu hỏi quá nhanh. Vui lòng thử lại sau '.RateLimiter::availableIn($rateKey).' giây.';

            return;
        }
        RateLimiter::hit($rateKey, 60);

        $question = trim($validated['message']);
        $this->message = '';
        $this->error = null;

        $history = collect($this->messages)
            ->where('status', 'completed')
            ->take(-8)
            ->map(fn (array $message): array => [
                'role' => $message['role'],
                'content' => $message['content'],
            ])
            ->values()
            ->all();

        $this->appendMessage([
            'role' => 'user',
            'content' => $question,
            'status' => 'completed',
        ]);

        $startedAt = hrtime(true);

        try {
            $result = $chatService->reply($user, $this->audience, $question, $history);
            $this->appendMessage([
                'role' => 'assistant',
                'content' => $result['answer'],
                'sources' => $result['sources'],
                'suggestions' => $result['suggestions'],
                'provider' => $result['provider'],
                'model' => $result['model'],
                'intent' => $result['intent'] ?? 'generative_answer',
                'latency_ms' => $this->elapsedMilliseconds($startedAt),
            ]);
        } catch (AiChatException $exception) {
            $this->error = $exception->getMessage();
            $this->appendMessage([
                'role' => 'assistant',
                'content' => 'Xin lỗi, mình chưa thể trả lời lúc này. Bạn vui lòng thử lại sau.',
                'status' => 'failed',
                'intent' => 'service_error',
                'latency_ms' => $this->elapsedMilliseconds($startedAt),
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unexpected AI chat failure.', [
                'user_id' => $user->id,
                'audience' => $this->audience,
                'message' => $exception->getMessage(),
            ]);
            $this->error = 'Chatbox gặp lỗi ngoài dự kiến. Vui lòng thử lại sau.';
            $this->appendMessage([
                'role' => 'assistant',
                'content' => 'Xin lỗi, mình chưa thể trả lời lúc này. Bạn vui lòng thử lại sau.',
                'status' => 'failed',
                'intent' => 'unexpected_error',
                'latency_ms' => $this->elapsedMilliseconds($startedAt),
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
        }

        $this->dispatch('ai-chat-updated');
    }

    public function render()
    {
        return view('livewire.ai-chatbox');
    }

    private function resolveAudience(?string $audience): string
    {
        if (in_array($audience, ['candidate', 'employer'], true)) {
            return $audience;
        }

        $routeName = (string) request()->route()?->getName();

        return str_starts_with($routeName, 'employers.') ? 'employer' : 'candidate';
    }

    private function canUseAudience(User $user, string $audience): bool
    {
        if ($audience === 'employer') {
            return in_array($user->role, ['admin', 'director', 'pm', 'hr'], true)
                || $user->hasAnyRole(['super_admin', 'director', 'pm', 'hr']);
        }

        if ($user->role === 'candidate') {
            return true;
        }

        $accountTypes = data_get($user->metadata, 'account_types', []);

        return is_array($accountTypes) && in_array('candidate', $accountTypes, true);
    }

    private function configureAssistant(User $user): void
    {
        if ($this->audience === 'candidate') {
            $this->assistantTitle = 'AI Career Assistant';
            $this->assistantSubtitle = 'Hồ sơ, việc làm và định hướng';
            $this->assistantDescription = 'Mình có thể kiểm tra hồ sơ, lịch phỏng vấn, gợi ý việc phù hợp và cách cải thiện CV.';
            $this->quickPrompts = [
                'Tóm tắt tình trạng hồ sơ ứng tuyển của tôi',
                'Gợi ý công việc phù hợp nhất với hồ sơ của tôi',
                'Tôi nên ưu tiên cải thiện phần nào trong CV?',
                'Tôi có lịch phỏng vấn hoặc offer nào sắp tới?',
            ];

            return;
        }

        if ($user->role === 'director') {
            $this->assistantTitle = 'AI Điều hành chi nhánh';
            $this->assistantSubtitle = 'KPI, phê duyệt và cảnh báo';
            $this->assistantDescription = 'Mình tổng hợp hiệu quả tuyển dụng, điểm nghẽn, offer chờ duyệt và khối lượng của HR trong chi nhánh.';
            $this->quickPrompts = [
                'Cho tôi briefing tuyển dụng cần xử lý hôm nay',
                'Có offer hoặc tin tuyển dụng nào đang chờ duyệt?',
                'Pipeline chi nhánh đang nghẽn ở giai đoạn nào?',
                'HR nào đang có nhiều hồ sơ mở nhất?',
            ];

            return;
        }

        if ($user->role === 'admin' || $user->isSuperAdmin()) {
            $this->assistantTitle = 'AI Điều hành tuyển dụng';
            $this->assistantSubtitle = 'Toàn hệ thống và các điểm nghẽn';
            $this->assistantDescription = 'Mình tổng hợp KPI, khối lượng tuyển dụng và các hạng mục cần ưu tiên trên toàn hệ thống.';
            $this->quickPrompts = [
                'Tóm tắt tình hình tuyển dụng toàn hệ thống',
                'Những hạng mục nào đang quá hạn?',
                'Pipeline đang nghẽn ở đâu?',
                'Khối lượng hồ sơ đang phân bổ cho HR thế nào?',
            ];

            return;
        }

        $this->assistantTitle = 'AI Copilot tuyển dụng';
        $this->assistantSubtitle = 'Ưu tiên công việc và hồ sơ';
        $this->assistantDescription = 'Mình giúp rà soát pipeline, phát hiện việc quá hạn và đề xuất thứ tự xử lý hồ sơ tuyển dụng.';
        $this->quickPrompts = [
            'Tóm tắt các việc tuyển dụng cần ưu tiên hôm nay',
            'Hồ sơ nào đang chờ sàng lọc?',
            'Có lịch phỏng vấn nào chưa gửi hoặc chưa chấm?',
            'Tin tuyển dụng nào đang có nhiều hồ sơ nhất?',
        ];
    }

    private function authorizeEnabledUser(): User
    {
        $user = Auth::user();
        abort_unless($this->enabled && $user instanceof User && $this->canUseAudience($user, $this->audience), 403);

        return $user;
    }

    /** @param array<string, mixed> $message */
    private function appendMessage(array $message): void
    {
        $this->messages[] = array_merge([
            'id' => ++$this->messageSequence,
            'sources' => [],
            'suggestions' => [],
            'status' => 'completed',
            'provider' => null,
            'model' => null,
            'intent' => null,
            'latency_ms' => null,
            'error_message' => null,
            'feedback' => null,
            'time' => now()->format('H:i'),
        ], $message);

        $this->messages = array_slice($this->messages, -30);
    }

    private function elapsedMilliseconds(int $startedAt): int
    {
        return max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000));
    }
}
