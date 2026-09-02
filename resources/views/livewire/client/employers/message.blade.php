<div class="premium-dashboard-container">
    <section class="candidate-dashboard-area section_70" style="padding: 28px 0 60px 0; background: #f8fafc; min-height: 85vh;">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <div class="col-md-4 col-lg-3 dashboard-left-border">
                    @include('livewire.client.partials.employer-sidebar')
                </div>

                <div class="col-md-8 col-lg-9">
                    <div class="dashboard-right d-flex flex-column gap-4">
                        <!-- Main Chat Outer Double-Bezel Shell -->
                        <div class="bg-white rounded-4 shadow-sm border overflow-hidden" style="min-height: 680px; display: flex; flex-direction: column;">
                            <div class="row g-0 flex-grow-1" style="min-height: 680px;">
                                
                                <!-- Left Column: Conversations List (4/12) -->
                                <div class="col-lg-4 border-end d-flex flex-column" style="background: #fafbfc;">
                                    <!-- Search & New Chat Header -->
                                    <div class="p-3.5 border-bottom bg-white d-flex align-items-center justify-content-between gap-2">
                                        <div class="position-relative flex-grow-1">
                                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm hội thoại..." class="form-control form-control-sm rounded-pill ps-4 pe-3" style="font-size: 12.5px; height: 36px; border-color: #e2e8f0;">
                                            <i class="fa fa-search position-absolute text-muted" style="left: 12px; top: 11px; font-size: 12px;"></i>
                                        </div>
                                        <button type="button" wire:click="$set('showNewChatModal', true)" class="btn btn-sm text-white fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none;" title="Tạo cuộc hội thoại mới">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>

                                    <!-- Conversation Items List -->
                                    <div class="flex-grow-1 overflow-y-auto" style="max-height: 620px;">
                                        @forelse($chats as $chat)
                                            @php
                                                $candidate = $chat->candidate;
                                                $candidateUser = $candidate?->user;
                                                $lastMessage = $chat->messages->first();
                                                $isActive = $activeChatId == $chat->id;
                                            @endphp
                                            <div wire:click="selectChat({{ $chat->id }})" 
                                                 class="p-3 border-bottom d-flex align-items-start gap-3 cursor-pointer {{ $isActive ? 'bg-white shadow-sm' : '' }}" 
                                                 style="cursor: pointer; transition: background 0.15s ease; border-left: {{ $isActive ? '4px solid #f37021' : '4px solid transparent' }};">
                                                <div class="position-relative flex-shrink-0">
                                                    <img src="{{ $candidateUser?->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                                         alt="{{ $candidate?->name ?? 'Ứng viên' }}" 
                                                         class="rounded-circle object-fit-cover border" 
                                                         style="width: 44px; height: 44px; background: #fff;">
                                                    <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle"></span>
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="fw-bold text-truncate text-dark" style="font-size: 13.5px;">
                                                            {{ $candidate?->name ?? 'Ứng viên' }}
                                                        </span>
                                                        <span class="text-muted" style="font-size: 10.5px;">
                                                            {{ optional($lastMessage?->created_at ?? $chat->updated_at)->format('H:i') }}
                                                        </span>
                                                    </div>
                                                    <div class="text-muted text-truncate" style="font-size: 12px;">
                                                        {{ $lastMessage?->content ?? 'Bắt đầu cuộc hội thoại...' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-4 text-center text-muted">
                                                <i class="fa fa-comments-o" style="font-size: 32px; color: #cbd5e1; margin-bottom: 8px;"></i>
                                                <p class="m-0" style="font-size: 12.5px;">Chưa có cuộc trò chuyện nào.</p>
                                                <button type="button" wire:click="$set('showNewChatModal', true)" class="btn btn-sm btn-link text-primary mt-1 p-0 fw-bold" style="font-size: 12px;">
                                                    Nhắn tin cho ứng viên mới
                                                </button>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Right Column: Active Chat Thread (8/12) -->
                                <div class="col-lg-8 d-flex flex-column bg-white">
                                    @if($activeChat)
                                        @php
                                            $activeCandidate = $activeChat->candidate;
                                            $activeUser = $activeCandidate?->user;
                                            $chatMessages = $activeChat->messages()->orderBy('created_at', 'asc')->get();
                                        @endphp
                                        <!-- Chat Thread Header -->
                                        <div class="p-3.5 border-bottom d-flex align-items-center justify-content-between gap-3 bg-white">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $activeUser?->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                                     alt="{{ $activeCandidate?->name ?? 'Ứng viên' }}" 
                                                     class="rounded-circle object-fit-cover border flex-shrink-0" 
                                                     style="width: 44px; height: 44px; background: #fff;">
                                                <div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="{{ route('employers.candidate_detail', $activeCandidate?->id ?? 0) }}" class="fw-bold text-dark text-decoration-none" style="font-size: 14.5px;">
                                                            {{ $activeCandidate?->name ?? 'Ứng viên' }}
                                                        </a>
                                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;">Ứng viên</span>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 11.5px;">
                                                        <i class="fa fa-envelope-o me-1"></i> {{ $activeCandidate?->email ?? 'Chưa có email' }}
                                                        @if($activeCandidate?->phone)
                                                            <span class="mx-1">•</span> <i class="fa fa-phone me-1"></i> {{ $activeCandidate->phone }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('employers.candidate_detail', $activeCandidate?->id ?? 0) }}" class="btn btn-sm btn-light border px-2.5 py-1 rounded-pill fw-bold text-secondary" style="font-size: 11.5px;">
                                                    <i class="fa fa-user me-1"></i> Hồ sơ
                                                </a>
                                                <button type="button" wire:click="deleteChat({{ $activeChat->id }})" class="btn btn-sm btn-light border border-danger-subtle text-danger px-2.5 py-1 rounded-pill" title="Xóa đoạn chat" style="font-size: 11.5px;">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Messages Bubble Container -->
                                        <div id="employer-chat-box" class="p-4 flex-grow-1 overflow-y-auto d-flex flex-column gap-3" style="max-height: 480px; min-height: 380px; background: #f8fafc;">
                                            @forelse($chatMessages as $msg)
                                                @php
                                                    $isMe = $msg->sender_type === 'employer';
                                                @endphp
                                                <div class="d-flex gap-2.5 {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">
                                                    @if(! $isMe)
                                                        <img src="{{ $activeUser?->avatar_url ?? asset('assets/img/candidate-default.png') }}" 
                                                             class="rounded-circle object-fit-cover flex-shrink-0" 
                                                             style="width: 32px; height: 32px; margin-top: 4px;" alt="">
                                                    @endif
                                                    <div style="max-width: 75%;">
                                                        <div class="p-3 rounded-4 shadow-sm" 
                                                             style="{{ $isMe ? 'background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); color: #fff; border-bottom-right-radius: 4px;' : 'background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px;' }}">
                                                            <div style="font-size: 13.5px; line-height: 1.5; white-space: pre-wrap;">{{ $msg->content }}</div>
                                                        </div>
                                                        <div class="text-muted mt-1 {{ $isMe ? 'text-end' : 'text-start' }}" style="font-size: 10.5px;">
                                                            {{ $msg->created_at->format('H:i - d/m/Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5 text-muted m-auto">
                                                    <i class="fa fa-paper-plane-o" style="font-size: 36px; color: #cbd5e1; margin-bottom: 8px;"></i>
                                                    <p class="m-0" style="font-size: 13px;">Hãy gửi tin nhắn đầu tiên để liên hệ trực tiếp với ứng viên.</p>
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- Quick Templates Pill Bar -->
                                        <div class="px-3.5 py-2 border-top bg-light d-flex align-items-center gap-2 overflow-x-auto" style="scrollbar-width: none;">
                                            <span class="text-muted text-nowrap fw-bold" style="font-size: 11px;">Mẫu nhanh:</span>
                                            <button type="button" wire:click="applyTemplate('Chào bạn, chúng tôi đã xem hồ sơ của bạn và rất ấn tượng. Chúng tôi muốn mời bạn tham gia một buổi phỏng vấn.')" class="btn btn-sm btn-white bg-white border rounded-pill text-nowrap text-secondary py-0.5 px-2.5" style="font-size: 11.5px;">
                                                Mời phỏng vấn
                                            </button>
                                            <button type="button" wire:click="applyTemplate('Chào bạn, hồ sơ của bạn đã vượt qua vòng sơ tuyển FPT Education. Vui lòng kiểm tra email để nhận thông tin chi tiết.')" class="btn btn-sm btn-white bg-white border rounded-pill text-nowrap text-secondary py-0.5 px-2.5" style="font-size: 11.5px;">
                                                Thông báo đạt sơ tuyển
                                            </button>
                                            <button type="button" wire:click="applyTemplate('Chào bạn, để hoàn tất thủ tục xét duyệt, bạn vui lòng bổ sung thêm CV bản cập nhật mới nhất nhé.')" class="btn btn-sm btn-white bg-white border rounded-pill text-nowrap text-secondary py-0.5 px-2.5" style="font-size: 11.5px;">
                                                Yêu cầu bổ sung CV
                                            </button>
                                        </div>

                                        <!-- Send Message Input Bar -->
                                        <div class="p-3 bg-white border-top">
                                            <form wire:submit.prevent="sendMessage" class="d-flex align-items-center gap-2">
                                                <input type="text" wire:model="newMessage" placeholder="Nhập tin nhắn trao đổi với ứng viên..." class="form-control rounded-pill ps-4 pe-4" style="font-size: 13px; height: 44px; border-color: #e2e8f0;">
                                                <button type="submit" wire:loading.attr="disabled" class="btn px-4 py-2 text-white fw-bold rounded-pill d-inline-flex align-items-center gap-2 flex-shrink-0 shadow-sm" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; height: 44px; font-size: 13px;">
                                                    <span wire:loading.remove wire:target="sendMessage">Gửi <i class="fa fa-paper-plane ms-1"></i></span>
                                                    <span wire:loading wire:target="sendMessage"><i class="fa fa-spinner fa-spin"></i></span>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <!-- No Active Chat Selected -->
                                        <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1 p-5 text-center text-muted">
                                            <div style="width: 72px; height: 72px; border-radius: 20px; background: rgba(243, 112, 33, 0.1); color: #f37021; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                                                <i class="fa fa-comments"></i>
                                            </div>
                                            <h4 class="fw-bold text-dark mb-1" style="font-size: 18px;">Chọn hoặc bắt đầu cuộc trò chuyện</h4>
                                            <p class="mb-3" style="font-size: 13px; max-width: 360px;">
                                                Trao đổi trực tiếp với các ứng viên đã nộp hồ sơ vào các vị trí tuyển dụng của bạn.
                                            </p>
                                            <button type="button" wire:click="$set('showNewChatModal', true)" class="btn text-white fw-bold px-4 py-2 rounded-pill shadow-sm" style="background: linear-gradient(135deg, #f37021 0%, #ea580c 100%); border: none; font-size: 13px;">
                                                <i class="fa fa-plus me-1"></i> Chọn ứng viên nhắn tin
                                            </button>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Start New Chat -->
    @if($showNewChatModal)
        <div class="interview-modal-backdrop" wire:click.self="$set('showNewChatModal', false)">
            <div class="interview-modal p-4 rounded-4 bg-white shadow-lg border" style="max-width: 480px;">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                    <h4 class="fw-bold mb-0" style="font-size: 16px; color: #0f172a;">
                        <i class="fa fa-comment-o text-primary me-1.5"></i> Chọn ứng viên nhắn tin
                    </h4>
                    <button type="button" wire:click="$set('showNewChatModal', false)" class="btn btn-sm btn-link text-muted p-0" style="font-size: 18px;">&times;</button>
                </div>

                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="fw-bold text-dark mb-1.5" style="font-size: 12.5px;">Danh sách ứng viên gần đây</label>
                        <select wire:model="selectedCandidateId" class="form-select rounded-3" style="font-size: 13px;">
                            <option value="">-- Chọn ứng viên --</option>
                            @foreach($recentCandidates as $cand)
                                <option value="{{ $cand->id }}">{{ $cand->name }} ({{ $cand->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" wire:click="$set('showNewChatModal', false)" class="btn btn-sm btn-light border px-3 rounded-pill">Hủy</button>
                        <button type="button" wire:click="startNewChat" class="btn btn-sm text-white fw-bold px-4 rounded-pill" style="background: #f37021; border: none;">
                            Bắt đầu nhắn tin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function scrollChatToBottom() {
            const chatBox = document.getElementById('employer-chat-box');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            scrollChatToBottom();
            window.addEventListener('message-sent', () => setTimeout(scrollChatToBottom, 100));
            window.addEventListener('chat-switched', () => setTimeout(scrollChatToBottom, 100));
            window.addEventListener('scroll-to-bottom', () => setTimeout(scrollChatToBottom, 100));
        });
    </script>
</div>
