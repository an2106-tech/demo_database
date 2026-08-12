<?php

namespace App\Livewire\Client;

use App\Models\Candidate;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\AiMockInterviewService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Messages extends Component
{
    public $activeChatId = null;
    public $newMessage = '';
    protected $queryString = ['activeChatId' => ['as' => 'chat']];

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('candidates.login');
        }
        $this->activeChatId = request()->query('chat');
    }

    public function selectChat($chatId)
    {
        $this->activeChatId = $chatId;
        $this->newMessage = '';
    }

    public function sendMessage(AiMockInterviewService $mockService)
    {
        if (empty(trim($this->newMessage)) || !$this->activeChatId) return;

        $candidateId = Candidate::where('user_id', Auth::id())->value('id');
        $chat = Chat::where('id', $this->activeChatId)->where('candidate_id', $candidateId)->first();
        
        if (!$chat) return;

        if ($chat->type === 'ai_mock_interview' && $chat->status !== 'completed') {
            // Send answer to AI
            $mockService->submitAnswer($chat, $this->newMessage);
            $this->newMessage = '';
        } elseif ($chat->type === 'employer_candidate') {
            // Wait, we decided employer_candidate is 1-way.
            // Candidates CANNOT reply.
            $this->dispatch('app-notify', message: 'Bạn không thể phản hồi tin nhắn hệ thống/nhà tuyển dụng.');
            $this->newMessage = '';
        }
    }

    #[Layout('layouts.client')]
    public function render()
    {
        $candidateId = Candidate::where('user_id', Auth::id())->value('id');
        $chats = Chat::where('candidate_id', $candidateId)
            ->with(['employer', 'job', 'messages' => fn($q) => $q->latest()])
            ->orderByDesc('updated_at')
            ->get();

        $activeChat = $chats->firstWhere('id', $this->activeChatId);
        if (!$activeChat && $chats->isNotEmpty()) {
            $activeChat = $chats->first();
            $this->activeChatId = $activeChat->id;
        }

        return view('livewire.client.messages', [
            'chats' => $chats,
            'activeChat' => $activeChat
        ]);
    }
}
