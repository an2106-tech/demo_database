<div>
    <div class="dashboard-breadcrumb">
        <ul>
            <li><a href="{{ route('home') }}">Trang chủ</a></li>
            <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
            <li class="active">Tin nhắn</li>
        </ul>
    </div>

    <section class="candidate-dashboard-area section_70">
        <div class="container-fluid px-lg-5">
            <div class="row">
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                <div class="col-lg-9 col-md-8 mx-auto">
                    <div class="dashboard-right message-page-box" style="padding: 28px;">
                        <div class="manage-jobs-heading" style="margin-bottom: 22px;">
                            <span style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(243,112,33,.08);color:#9a3412;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Trò chuyện</span>
                            <h3 style="margin: 10px 0 0; color: #0f172a;">Tin nhắn mới</h3>
                        </div>

                        <div class="row g-4">
                            <!-- Sidebar Chat List -->
                            <div class="col-lg-4 col-md-12">
                                <div class="chat-list-left" style="background:#fff; border:1px solid #e5ebf3; border-radius:24px; box-shadow:0 18px 42px rgba(15,23,42,.05); overflow:hidden;">
                                    <div class="chat-list-body" style="max-height: 540px; overflow:auto;">
                                        <ul class="chat-list">
                                            @forelse($chats as $chat)
                                                <li class="clearfix {{ $chat->id == $activeChatId ? 'active' : '' }}" wire:click="selectChat({{ $chat->id }})" style="cursor: pointer;">
                                                    <a href="javascript:void(0)">
                                                        <div class="chat-avatar-img">
                                                            @if($chat->type === 'ai_mock_interview')
                                                                <img src="{{ asset('assets/img/fe-logo.png') }}" alt="AI" />
                                                            @else
                                                                <img src="{{ asset('assets/img/company-logo-1.png') }}" alt="Company" />
                                                            @endif
                                                        </div>
                                                        <div class="chat-about">
                                                            @if($chat->type === 'ai_mock_interview')
                                                                <h4>AI Phỏng vấn: {{ $chat->job->title ?? 'Công việc' }}</h4>
                                                                <small class="{{ $chat->status === 'completed' ? 'busy' : 'online' }}">{{ $chat->status === 'completed' ? 'Đã hoàn thành' : 'Đang phỏng vấn' }}</small>
                                                            @else
                                                                <h4>{{ $chat->employer->name ?? 'Nhà tuyển dụng' }}</h4>
                                                                <small class="online">Thông báo</small>
                                                            @endif
                                                        </div>
                                                    </a>
                                                </li>
                                            @empty
                                                <li style="padding: 20px; text-align: center; color: #64748b;">Chưa có cuộc trò chuyện nào.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Board -->
                            <div class="col-lg-8 col-md-12">
                                <div class="chat-board-right" style="background:#fff; border:1px solid #e5ebf3; border-radius:24px; box-shadow:0 18px 42px rgba(15,23,42,.05); overflow:hidden;">
                                    @if($activeChat)
                                        <div class="chat-board-header" style="border-bottom:1px solid #edf2f7; padding: 18px 20px;">
                                            <div class="navbar">
                                                <div class="user-details-nav">
                                                    <div class="user-info pull-left">
                                                        <h4>
                                                            @if($activeChat->type === 'ai_mock_interview')
                                                                🎙️ AI Mock Interview: {{ $activeChat->job->title ?? 'N/A' }}
                                                            @else
                                                                🏢 {{ $activeChat->employer->name ?? 'Nhà tuyển dụng' }}
                                                            @endif
                                                        </h4>
                                                        <small>{{ $activeChat->type === 'ai_mock_interview' ? 'Hệ thống phỏng vấn tự động' : 'Thông báo từ nhà tuyển dụng' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="chat-board-content" style="background:linear-gradient(180deg,#f8fbff 0%, #f7f8fb 100%);">
                                            <div class="chat-box-wrapper">
                                                <div class="chat-box-inner" style="max-height: 400px; overflow-y: auto;">
                                                    <ul class="chat-list">
                                                        @foreach($activeChat->messages->reverse() as $msg)
                                                            @if($msg->sender_type === 'candidate')
                                                                <li class="chat-list-right">
                                                                    <div class="chat-content" style="max-width: 80%;">
                                                                        <div class="chat-text" style="background: linear-gradient(135deg, #ea580c, #f97316); color: white;">
                                                                            {{ $msg->content }}
                                                                        </div>
                                                                        <div class="chat-time">{{ $msg->created_at->format('H:i') }}</div>
                                                                    </div>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <div class="chat-img">
                                                                        @if($msg->sender_type === 'ai')
                                                                            <img src="{{ asset('assets/img/fe-logo.png') }}" alt="AI">
                                                                        @else
                                                                            <img src="{{ asset('assets/img/company-logo-1.png') }}" alt="Employer">
                                                                        @endif
                                                                    </div>
                                                                    <div class="chat-content" style="max-width: 80%;">
                                                                        <div class="chat-text">
                                                                            {{ $msg->content }}
                                                                        </div>
                                                                        @if($msg->sender_type === 'ai' && !empty($msg->metadata['score']))
                                                                            <div style="background: #f1f5f9; padding: 10px; border-radius: 8px; margin-top: 8px; font-size: 0.85rem;">
                                                                                <strong><i class="fa fa-star text-warning"></i> Đánh giá ({{ $msg->metadata['score'] }}/10):</strong><br>
                                                                                {{ $msg->metadata['feedback'] ?? '' }}
                                                                                <br><br>
                                                                                <strong>Gợi ý:</strong><br>
                                                                                {{ $msg->metadata['suggested_answer'] ?? '' }}
                                                                            </div>
                                                                        @endif
                                                                        <div class="chat-time">{{ $msg->created_at->format('H:i') }}</div>
                                                                    </div>
                                                                </li>
                                                            @endif
                                                        @endforeach

                                                        @if($activeChat->type === 'ai_mock_interview' && $activeChat->status === 'completed')
                                                            <li>
                                                                <div class="chat-content" style="max-width: 90%; margin: 20px auto; background: #fff8f1; padding: 20px; border-radius: 12px; border: 1px solid #fed7aa; text-align: center;">
                                                                    <h4 style="color: #ea580c;">🎉 Hoàn thành phỏng vấn!</h4>
                                                                    <h1 style="font-size: 3rem; color: #c2410c;">{{ $activeChat->metadata['total_score'] ?? 0 }}<small>/100</small></h1>
                                                                    <p>{{ $activeChat->metadata['summary_feedback']['recommendation'] ?? '' }}</p>
                                                                </div>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="chat-footer" style="border-top:1px solid #edf2f7; background:#fff; padding: 20px;">
                                            @if($activeChat->type === 'ai_mock_interview' && $activeChat->status !== 'completed')
                                                <div class="message-bar">
                                                    <div class="message-bar-inner">
                                                        <div class="message-text-area" style="width: 100%;">
                                                            <form wire:submit.prevent="sendMessage" style="position: relative; display: flex; align-items: center; gap: 12px;">
                                                                <textarea wire:model="newMessage" placeholder="Nhập câu trả lời của bạn..." style="flex: 1; border-radius: 24px; border:1px solid #d8e1eb; min-height: 54px; height: 54px; max-height: 120px; resize:none; padding: 14px 20px; outline: none; line-height: 1.5;"></textarea>
                                                                <button type="submit" style="border-radius: 50%; width: 50px; height: 50px; flex-shrink: 0; background: #ea580c; border: none; color: white; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);">
                                                                    <i wire:loading.remove wire:target="sendMessage" class="fa fa-paper-plane" style="margin-right: 2px;"></i>
                                                                    <i wire:loading wire:target="sendMessage" class="fa fa-spinner fa-spin"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($activeChat->type === 'employer_candidate')
                                                <div style="padding: 20px; text-align: center; color: #64748b; font-size: 0.9rem;">
                                                    Bạn không thể trả lời lại tin nhắn hệ thống này (Chống spam).
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div style="padding: 100px 20px; text-align: center; color: #94a3b8;">
                                            <i class="fa fa-comments-o" style="font-size: 4rem; margin-bottom: 20px;"></i>
                                            <h5>Chọn một cuộc trò chuyện để bắt đầu</h5>
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
</div>
