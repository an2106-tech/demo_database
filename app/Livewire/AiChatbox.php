<?php

namespace App\Livewire;

use App\Exceptions\AiChatException;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
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

    public string $mainMode = 'ai_chat'; // 'ai_chat' | 'shortcuts'

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

    public ?int $currentSessionId = null;

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
        $this->loadActiveConversation($user);
    }

    public function toggle(): void
    {
        if ($this->enabled) {
            $this->isOpen = ! $this->isOpen;
            $this->error = null;
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function switchMainMode(string $mode): void
    {
        $this->mainMode = in_array($mode, ['ai_chat', 'shortcuts'], true) ? $mode : 'ai_chat';
        $this->error = null;
    }

    public function newConversation(): void
    {
        $this->authorizeEnabledUser();

        if ($this->currentSessionId) {
            AiChatSession::query()
                ->whereKey($this->currentSessionId)
                ->update(['is_active' => false]);
        }

        $this->messages = [];
        $this->messageSequence = 0;
        $this->currentSessionId = null;
        $this->message = '';
        $this->error = null;
        $this->dispatch('ai-chat-open');
    }

    public function rateMessage(int $messageId, string $feedback): void
    {
        $this->authorizeEnabledUser();
        abort_unless(in_array($feedback, ['helpful', 'not_helpful'], true), 422);

        foreach ($this->messages as $index => $message) {
            if ($message['id'] === $messageId && $message['role'] === 'assistant' && $message['status'] === 'completed') {
                $this->messages[$index]['feedback'] = $message['feedback'] === $feedback ? null : $feedback;

                if (! empty($message['db_id'])) {
                    AiChatMessage::query()
                        ->whereKey($message['db_id'])
                        ->update(['feedback' => $this->messages[$index]['feedback']]);
                }

                $this->dispatch('ai-chat-open');

                return;
            }
        }

        abort(404);
    }

    public function useSuggestion(string $suggestion): void
    {
        $this->message = mb_substr(trim($suggestion), 0, 1000);
        $this->error = null;
        $this->dispatch('ai-chat-open');
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
            $this->assistantDescription = 'Mình tổng hợp hiệu quả tuyển dụng, điểm đang vướng, đề nghị chờ duyệt và khối lượng của HR trong chi nhánh.';
            $this->quickPrompts = [
                'Tóm tắt việc tuyển dụng cần xử lý hôm nay',
                'Có đề nghị hoặc tin tuyển dụng nào đang chờ duyệt?',
                'Quy trình tuyển dụng đang vướng ở bước nào?',
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
                'Quy trình tuyển dụng đang vướng ở đâu?',
                'Khối lượng hồ sơ đang phân bổ cho HR thế nào?',
            ];

            return;
        }

        $this->assistantTitle = 'Trợ lý tuyển dụng AI';
        $this->assistantSubtitle = 'Ưu tiên công việc và hồ sơ';
        $this->assistantDescription = 'Mình giúp HR xem nhanh hồ sơ cần xử lý, lịch phỏng vấn và các điểm đang vướng trong quy trình tuyển dụng.';
        $this->quickPrompts = [
            'Hôm nay có hồ sơ nào cần xử lý?',
            'Có lịch phỏng vấn nào sắp tới?',
            'Có hồ sơ nào lâu chưa cập nhật?',
            'Quy trình tuyển dụng đang vướng ở bước nào?',
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
        $payload = array_merge([
            'id' => ++$this->messageSequence,
            'db_id' => null,
            'sources' => [],
            'suggestions' => [],
            'status' => 'completed',
            'provider' => null,
            'model' => null,
            'intent' => null,
            'latency_ms' => null,
            'error_message' => null,
            'feedback' => null,
            'time' => $this->displayTime(now()),
        ], $message);

        $payload = $this->persistMessage($payload);
        $this->messages[] = $payload;

        $this->messages = array_slice($this->messages, -30);
    }

    private function loadActiveConversation(User $user): void
    {
        $session = AiChatSession::query()
            ->where('user_id', $user->id)
            ->where('audience', $this->audience)
            ->where('is_active', true)
            ->latest('last_message_at')
            ->latest('id')
            ->first();

        if (! $session) {
            return;
        }

        $this->currentSessionId = $session->id;
        $this->messages = $session->messages()
            ->oldest()
            ->take(30)
            ->get()
            ->map(function (AiChatMessage $message): array {
                return [
                    'id' => ++$this->messageSequence,
                    'db_id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'sources' => $message->sources ?? [],
                    'suggestions' => $message->suggestions ?? [],
                    'status' => $message->status,
                    'provider' => $message->provider,
                    'model' => $message->model,
                    'intent' => $message->intent,
                    'latency_ms' => $message->latency_ms,
                    'error_message' => $message->error_message,
                    'feedback' => $message->feedback,
                    'time' => $this->displayTime($message->created_at),
                ];
            })
            ->all();
    }

    /** @param array<string, mixed> $message */
    private function persistMessage(array $message): array
    {
        $user = Auth::user();
        if (! $this->enabled || ! $user instanceof User) {
            return $message;
        }

        $session = $this->currentSessionId
            ? AiChatSession::query()->whereKey($this->currentSessionId)->first()
            : null;

        if (! $session) {
            $session = AiChatSession::query()->create([
                'user_id' => $user->id,
                'audience' => $this->audience,
                'title' => mb_substr((string) $message['content'], 0, 120),
                'is_active' => true,
                'last_message_at' => now(),
            ]);
            $this->currentSessionId = $session->id;
        }

        $stored = $session->messages()->create([
            'role' => $message['role'],
            'content' => $message['content'],
            'sources' => $message['sources'] ?? [],
            'suggestions' => $message['suggestions'] ?? [],
            'status' => $message['status'] ?? 'completed',
            'provider' => $message['provider'] ?? null,
            'model' => $message['model'] ?? null,
            'intent' => $message['intent'] ?? null,
            'latency_ms' => $message['latency_ms'] ?? null,
            'error_message' => $message['error_message'] ?? null,
            'feedback' => $message['feedback'] ?? null,
        ]);

        $session->forceFill([
            'is_active' => true,
            'last_message_at' => now(),
        ])->save();

        $this->pruneStoredMessages($session);

        $message['db_id'] = $stored->id;

        return $message;
    }

    private function pruneStoredMessages(AiChatSession $session): void
    {
        $idsToKeep = $session->messages()
            ->latest('id')
            ->limit(30)
            ->pluck('id');

        $session->messages()
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }

    private function elapsedMilliseconds(int $startedAt): int
    {
        return max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000));
    }

    private function displayTime($dateTime = null): string
    {
        $timezone = config('app.interview_timezone', config('app.timezone', 'Asia/Ho_Chi_Minh'));
        return ($dateTime ? (is_string($dateTime) ? \Illuminate\Support\Carbon::parse($dateTime) : $dateTime->copy()) : now())
            ->timezone($timezone)
            ->format('H:i');
    }
}
