<?php

namespace App\Livewire\Client\Employers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Candidate;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Message extends Component
{
    public $activeChatId = null;
    public string $newMessage = '';
    public string $search = '';
    public bool $showNewChatModal = false;
    public ?int $selectedCandidateId = null;

    protected $queryString = ['activeChatId' => ['as' => 'chat']];

    public function mount(): void
    {
        $this->activeChatId = request()->query('chat');
    }

    public function selectChat($chatId): void
    {
        $this->activeChatId = $chatId;
        $this->newMessage = '';
        $this->dispatch('chat-switched');
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->newMessage)) || ! $this->activeChatId) {
            return;
        }

        $chat = Chat::where('id', $this->activeChatId)
            ->where('employer_id', Auth::id())
            ->first();

        if (! $chat) {
            $this->dispatch('app-notify', message: 'Không tìm thấy cuộc hội thoại.', type: 'error');
            return;
        }

        ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_type' => 'employer',
            'sender_id' => Auth::id(),
            'content' => trim($this->newMessage),
        ]);

        $chat->touch();

        $this->newMessage = '';
        $this->dispatch('message-sent');
        $this->dispatch('scroll-to-bottom');
    }

    public function applyTemplate(string $text): void
    {
        $this->newMessage = $text;
    }

    public function deleteChat($chatId): void
    {
        $chat = Chat::where('id', $chatId)
            ->where('employer_id', Auth::id())
            ->first();

        if (! $chat) {
            return;
        }

        $chat->messages()->delete();
        $chat->delete();

        if ($this->activeChatId == $chatId) {
            $this->activeChatId = null;
        }

        $this->dispatch('app-notify', message: 'Đã xóa cuộc hội thoại.');
    }

    public function startNewChat(): void
    {
        if (! $this->selectedCandidateId) {
            $this->dispatch('app-notify', message: 'Vui lòng chọn ứng viên để bắt đầu hội thoại.', type: 'warning');
            return;
        }

        $chat = Chat::firstOrCreate([
            'employer_id' => Auth::id(),
            'candidate_id' => $this->selectedCandidateId,
            'type' => 'employer_candidate',
        ], [
            'status' => 'active',
        ]);

        $this->activeChatId = $chat->id;
        $this->showNewChatModal = false;
        $this->selectedCandidateId = null;

        $this->dispatch('app-notify', message: 'Đã mở cuộc trò chuyện mới.');
    }

    #[Layout('layouts.employer')]
    public function render()
    {
        $chats = Chat::query()
            ->where('employer_id', Auth::id())
            ->with(['candidate.user', 'job', 'messages' => fn ($q) => $q->latest()])
            ->when(filled($this->search), function ($q) {
                $s = trim($this->search);
                $q->whereHas('candidate', function ($cq) use ($s) {
                    $cq->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('updated_at')
            ->get();

        $activeChat = null;
        if ($this->activeChatId) {
            $activeChat = $chats->firstWhere('id', $this->activeChatId);
        }

        if (! $activeChat && $chats->isNotEmpty() && ! filled($this->search)) {
            $activeChat = $chats->first();
            $this->activeChatId = $activeChat->id;
        }

        // Recent candidates who applied to employer's jobs for quick new chat
        $recentCandidates = Candidate::query()
            ->whereHas('applications.job', fn ($q) => $q->where('created_by', Auth::id()))
            ->with('user')
            ->take(15)
            ->get();

        return view('livewire.client.employers.message', [
            'chats' => $chats,
            'activeChat' => $activeChat,
            'recentCandidates' => $recentCandidates,
        ]);
    }
}
