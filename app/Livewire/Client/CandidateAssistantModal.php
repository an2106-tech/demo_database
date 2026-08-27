<?php

namespace App\Livewire\Client;

use App\Exceptions\AiChatException;
use App\Models\Attachment;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Application;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Rules\CvUploadFile;
use App\Services\AiChatService;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateAssistantModal extends Component
{
    use WithFileUploads;

    private const SHORTCUT_VIEWS = ['menu', 'manage_cv', 'messages', 'chat_detail', 'security'];

    public bool $isOpen = false;
    public string $mainMode = 'ai_chat'; // 'ai_chat' | 'shortcuts'
    public string $currentShortcutView = 'menu';

    // AI Chatbox State
    public string $aiInput = '';
    public array $chatMessages = [];
    public ?string $aiError = null;
    public int $messageCounter = 0;

    /** @var array<int, string> */
    public array $quickPrompts = [
        'Tóm tắt tình trạng hồ sơ ứng tuyển của tôi',
        'Gợi ý 3 việc làm phù hợp nhất với kỹ năng của tôi',
        'Tôi nên ưu tiên cải thiện phần nào trong CV?',
        'Tôi có lịch phỏng vấn hoặc tin nhắn nào mới không?',
    ];

    // CV Upload State
    public $newCvUpload = null;
    public string $newCvTitle = '';

    // Messages State
    public ?int $selectedChatId = null;

    // Security & Password Form State
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $passwordStatus = null;

    public function mount(): void
    {
        // Initial bot greeting if empty
        if (empty($this->chatMessages)) {
            $this->initWelcomeMessage();
        }
    }

    private function initWelcomeMessage(): void
    {
        $userName = Auth::user()?->name ?? 'bạn';
        $this->chatMessages = [
            [
                'id' => ++$this->messageCounter,
                'role' => 'assistant',
                'content' => "Xin chào **{$userName}**! 👋 Tôi là **Trợ lý AI FPT Careers**.\n\nTôi có thể giúp bạn kiểm tra hồ sơ, gợi ý việc làm tương thích, xem lịch phỏng vấn hoặc giải đáp mọi thắc mắc nghề nghiệp của bạn. Hãy gửi câu hỏi cho tôi bên dưới!",
                'sources' => [],
                'suggestions' => [
                    'Kiểm tra độ hoàn thiện hồ sơ của tôi',
                    'Tìm việc làm IT đang tuyển',
                    'Tiến độ các đơn ứng tuyển gần đây',
                ],
                'created_at' => now()->format('H:i'),
            ]
        ];
    }

    #[On('open-candidate-assistant')]
    public function openWithView(string $view = 'menu'): void
    {
        $this->isOpen = true;
        if ($view === 'ai_chat') {
            $this->mainMode = 'ai_chat';
        } else {
            $this->mainMode = 'shortcuts';
            $this->currentShortcutView = in_array($view, self::SHORTCUT_VIEWS, true) ? $view : 'menu';
        }
        $this->resetErrorBag();
        $this->passwordStatus = null;
        $this->aiError = null;
    }

    public function toggleOpen(): void
    {
        $this->isOpen = ! $this->isOpen;
        $this->passwordStatus = null;
        $this->resetErrorBag();
        $this->aiError = null;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function switchMainMode(string $mode): void
    {
        $this->mainMode = in_array($mode, ['ai_chat', 'shortcuts'], true) ? $mode : 'ai_chat';
        $this->resetErrorBag();
        $this->passwordStatus = null;
    }

    public function setShortcutView(string $view): void
    {
        $this->currentShortcutView = in_array($view, self::SHORTCUT_VIEWS, true) ? $view : 'menu';
        $this->passwordStatus = null;
        $this->resetErrorBag();
    }

    public function backToShortcutMenu(): void
    {
        $this->currentShortcutView = 'menu';
        $this->selectedChatId = null;
        $this->passwordStatus = null;
        $this->resetErrorBag();
    }

    // --- AI Chat Actions ---
    public function sendAiMessage(AiChatService $chatService): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            $this->aiError = 'Vui lòng đăng nhập để sử dụng Trợ lý AI.';
            return;
        }

        $input = trim($this->aiInput);
        if (mb_strlen($input) < 2) {
            $this->aiError = 'Vui lòng nhập câu hỏi có ít nhất 2 ký tự.';
            return;
        }

        $rateKey = 'ai-chat-candidate:'.$user->id;
        if (RateLimiter::tooManyAttempts($rateKey, 12)) {
            $this->aiError = 'Bạn đang gửi câu hỏi quá nhanh. Vui lòng chờ '.RateLimiter::availableIn($rateKey).' giây.';
            return;
        }
        RateLimiter::hit($rateKey, 60);

        $question = mb_substr($input, 0, 1000);
        $this->aiInput = '';
        $this->aiError = null;

        // Append User Message
        $this->chatMessages[] = [
            'id' => ++$this->messageCounter,
            'role' => 'user',
            'content' => $question,
            'sources' => [],
            'suggestions' => [],
            'created_at' => now()->format('H:i'),
        ];

        // Format history
        $history = collect($this->chatMessages)
            ->take(-8)
            ->map(fn ($m) => [
                'role' => $m['role'],
                'content' => $m['content'],
            ])
            ->values()
            ->all();

        try {
            $result = $chatService->reply($user, 'candidate', $question, $history);

            $this->chatMessages[] = [
                'id' => ++$this->messageCounter,
                'role' => 'assistant',
                'content' => $result['answer'] ?? 'Tôi đã tiếp nhận yêu cầu của bạn.',
                'sources' => $result['sources'] ?? [],
                'suggestions' => $result['suggestions'] ?? [],
                'created_at' => now()->format('H:i'),
            ];
        } catch (\Throwable $e) {
            Log::warning('Candidate assistant error: ' . $e->getMessage());

            // Provide intelligent fallback from local context
            $fallback = $this->generateLocalFallback($user, $question);
            $this->chatMessages[] = [
                'id' => ++$this->messageCounter,
                'role' => 'assistant',
                'content' => $fallback['content'],
                'sources' => $fallback['sources'],
                'suggestions' => $fallback['suggestions'],
                'created_at' => now()->format('H:i'),
            ];
        }
    }

    public function sendQuickPrompt(string $prompt, AiChatService $chatService): void
    {
        $this->aiInput = $prompt;
        $this->sendAiMessage($chatService);
    }

    public function clearAiChat(): void
    {
        $this->chatMessages = [];
        $this->messageCounter = 0;
        $this->aiInput = '';
        $this->aiError = null;
        $this->initWelcomeMessage();
    }

    private function generateLocalFallback(User $user, string $question): array
    {
        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        $completion = app(CandidateAccountService::class)->profileCompletion($candidate);
        $appsCount = Application::where('candidate_id', $candidate->id)->count();

        $lower = mb_strtolower($question);
        if (str_contains($lower, 'hồ sơ') || str_contains($lower, 'cv')) {
            return [
                'content' => "Hồ sơ của bạn hiện đạt **{$completion}%** hoàn thiện. Bạn có thể sử dụng công cụ **Tạo CV Online AI** hoặc cập nhật thêm kinh nghiệm/kỹ năng để nâng cao điểm đánh giá từ nhà tuyển dụng.",
                'sources' => [
                    ['label' => 'Tạo CV Online AI', 'url' => route('candidates.cv_builder')],
                    ['label' => 'Cập nhật hồ sơ cá nhân', 'url' => route('candidates.candidate_profile')],
                ],
                'suggestions' => ['Xem việc làm phù hợp', 'Kiểm tra đơn ứng tuyển'],
            ];
        }

        if (str_contains($lower, 'việc') || str_contains($lower, 'tuyển')) {
            $jobsCount = RecruitmentJob::where('status', 'published')->count();
            return [
                'content' => "Hiện tại hệ thống FPT Careers đang mở tuyển **{$jobsCount}** vị trí hấp dẫn. Bạn có thể duyệt danh sách việc làm hoặc sử dụng chức năng AI Match trên Dashboard.",
                'sources' => [
                    ['label' => 'Khám phá tất cả việc làm', 'url' => route('candidates.browse_job')],
                ],
                'suggestions' => ['Kiểm tra độ hoàn thiện hồ sơ', 'Tình trạng ứng tuyển'],
            ];
        }

        return [
            'content' => "Bạn đã nộp **{$appsCount}** đơn ứng tuyển trên hệ thống và hồ sơ đạt **{$completion}%** hoàn thiện. Hãy chọn một trong các gợi ý bên dưới hoặc đặt câu hỏi cụ thể hơn nhé!",
            'sources' => [],
            'suggestions' => ['Tóm tắt tình trạng hồ sơ', 'Gợi ý việc làm phù hợp'],
        ];
    }

    // --- CV Management ---
    public function uploadCv(): void
    {
        $user = Auth::user();
        if (! $user) return;

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $this->validate([
            'newCvUpload' => ['required', 'file', 'max:10240', new CvUploadFile()],
            'newCvTitle' => ['nullable', 'string', 'max:255'],
        ], [
            'newCvUpload.required' => 'Vui lòng chọn file CV cần tải lên.',
            'newCvUpload.max' => 'Dung lượng file CV không được vượt quá 10MB.',
        ]);

        $path = $this->newCvUpload->storePublicly("candidates/{$candidate->id}/cv", 'public');

        $attachment = $candidate->attachments()->create([
            'path' => $path,
            'type' => 'cv',
            'original_filename' => $this->newCvTitle 
                ? (trim($this->newCvTitle) . '.' . $this->newCvUpload->getClientOriginalExtension()) 
                : ($this->newCvUpload->getClientOriginalName() ?: 'CV_Upload.pdf'),
            'mime_type' => $this->newCvUpload->getMimeType() ?: 'application/pdf',
            'size_bytes' => $this->newCvUpload->getSize() ?: 0,
        ]);

        if (empty($candidate->cv_file)) {
            $candidate->cv_file = $path;
            $candidate->save();
        }

        $this->reset(['newCvUpload', 'newCvTitle']);
        $this->dispatch('app-notify', message: 'Tải lên CV thành công!', type: 'success');
    }

    public function deleteCv(int $attachmentId): void
    {
        $user = Auth::user();
        if (! $user) return;

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        $attachment = $candidate->attachments()->where('id', $attachmentId)->first();

        if (! $attachment) return;

        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        if ($candidate->cv_file === $attachment->path) {
            $candidate->cv_file = null;
            $candidate->save();
        }

        $this->dispatch('app-notify', message: 'Đã xóa file CV đính kèm.', type: 'info');
    }

    // --- Messages Management ---
    public function openChatDetail(int $chatId): void
    {
        $this->selectedChatId = $chatId;
        $this->currentShortcutView = 'chat_detail';
    }

    // --- Security & Password ---
    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        $user = Auth::user();

        if (! $user || ! Hash::check($this->current_password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($this->password),
        ])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->passwordStatus = 'Mật khẩu đã được cập nhật thành công!';
        $this->dispatch('app-notify', message: 'Cập nhật mật khẩu thành công!', type: 'success');
    }

    public function render()
    {
        $user = Auth::user();
        $candidate = $user ? app(CandidateAccountService::class)->resolveFor($user) : null;

        $attachments = collect();
        $chats = collect();
        $activeChat = null;
        $profileCompletion = 0;

        if ($candidate) {
            $attachments = $candidate->attachments()
                ->where('type', 'cv')
                ->latest()
                ->get();

            $chats = Chat::where('candidate_id', $candidate->id)
                ->with(['employer', 'job', 'messages' => fn($q) => $q->latest()])
                ->orderByDesc('updated_at')
                ->get();

            if ($this->selectedChatId) {
                $activeChat = $chats->firstWhere('id', $this->selectedChatId);
            }

            $profileCompletion = app(CandidateAccountService::class)->profileCompletion($candidate);
        }

        return view('livewire.client.candidate-assistant-modal', [
            'candidate' => $candidate,
            'attachments' => $attachments,
            'chats' => $chats,
            'activeChat' => $activeChat,
            'profileCompletion' => $profileCompletion,
        ]);
    }
}
