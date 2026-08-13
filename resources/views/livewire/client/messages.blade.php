<div class="candidate-messages">
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
                    <main class="dashboard-right message-page-box">
                        <header class="messages-page-heading">
                            <p>HỘP THƯ</p>
                            <h3>Tin nhắn</h3>
                            <span>{{ $chats->count() }} cuộc trò chuyện</span>
                        </header>

                        <div class="messages-layout">
                            <aside class="messages-sidebar" aria-label="Danh sách cuộc trò chuyện">
                                <div class="messages-sidebar__header">Cuộc trò chuyện</div>
                                <div class="messages-sidebar__body">
                                    @forelse($chats as $chat)
                                        <button type="button" class="conversation-item {{ $chat->id == $activeChatId ? 'is-active' : '' }}" wire:click="selectChat({{ $chat->id }})">
                                            <span class="conversation-avatar {{ $chat->type === 'ai_mock_interview' ? 'is-ai' : '' }}" aria-hidden="true">
                                                {{ $chat->type === 'ai_mock_interview' ? 'AI' : mb_strtoupper(mb_substr($chat->employer->name ?? 'NTD', 0, 1)) }}
                                            </span>
                                            <span class="conversation-copy">
                                                <strong>{{ $chat->type === 'ai_mock_interview' ? 'Phỏng vấn AI' : ($chat->employer->name ?? 'Nhà tuyển dụng') }}</strong>
                                                <small>{{ $chat->type === 'ai_mock_interview' ? ($chat->job->title ?? 'Cuộc phỏng vấn') : 'Thông báo từ nhà tuyển dụng' }}</small>
                                            </span>
                                            <span class="conversation-status {{ $chat->status === 'completed' ? 'is-complete' : '' }}">
                                                {{ $chat->type === 'ai_mock_interview' ? ($chat->status === 'completed' ? 'Đã xong' : 'Đang diễn ra') : 'Mới' }}
                                            </span>
                                        </button>
                                    @empty
                                        <div class="messages-empty-list">Chưa có cuộc trò chuyện nào.</div>
                                    @endforelse
                                </div>
                            </aside>

                            <section class="messages-panel">
                                @if($activeChat)
                                    <header class="messages-panel__header">
                                        <span class="panel-avatar {{ $activeChat->type === 'ai_mock_interview' ? 'is-ai' : '' }}" aria-hidden="true">
                                            {{ $activeChat->type === 'ai_mock_interview' ? 'AI' : mb_strtoupper(mb_substr($activeChat->employer->name ?? 'NTD', 0, 1)) }}
                                        </span>
                                        <div>
                                            <h4>{{ $activeChat->type === 'ai_mock_interview' ? 'Phỏng vấn AI' : ($activeChat->employer->name ?? 'Nhà tuyển dụng') }}</h4>
                                            <p>{{ $activeChat->type === 'ai_mock_interview' ? ($activeChat->job->title ?? 'Cuộc phỏng vấn trực tuyến') : 'Thông báo một chiều từ nhà tuyển dụng' }}</p>
                                        </div>
                                    </header>

                                    <div class="messages-panel__content" wire:loading.class="is-loading" wire:target="selectChat,sendMessage">
                                        <div class="chat-day-label">Nội dung trao đổi</div>
                                        <div class="message-stream">
                                            @foreach($activeChat->messages->reverse() as $msg)
                                                <article class="message-row {{ $msg->sender_type === 'candidate' ? 'is-own' : '' }}">
                                                    @if($msg->sender_type !== 'candidate')
                                                        <span class="message-avatar {{ $msg->sender_type === 'ai' ? 'is-ai' : '' }}" aria-hidden="true">{{ $msg->sender_type === 'ai' ? 'AI' : mb_strtoupper(mb_substr($activeChat->employer->name ?? 'NTD', 0, 1)) }}</span>
                                                    @endif
                                                    <div class="message-stack">
                                                        <div class="message-bubble">{{ $msg->content }}</div>
                                                        @if($msg->sender_type === 'ai' && !empty($msg->metadata['score']))
                                                            <div class="message-feedback">
                                                                <strong>Đánh giá {{ $msg->metadata['score'] }}/10</strong>
                                                                <p>{{ $msg->metadata['feedback'] ?? '' }}</p>
                                                                @if(!empty($msg->metadata['suggested_answer']))
                                                                    <p><b>Gợi ý:</b> {{ $msg->metadata['suggested_answer'] }}</p>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <time>{{ $msg->created_at->format('H:i') }}</time>
                                                    </div>
                                                </article>
                                            @endforeach

                                            @if($activeChat->type === 'ai_mock_interview' && $activeChat->status === 'completed')
                                                <div class="interview-summary">
                                                    <p>KẾT QUẢ PHỎNG VẤN</p>
                                                    <strong>{{ $activeChat->metadata['total_score'] ?? 0 }}<small>/100</small></strong>
                                                    <span>{{ $activeChat->metadata['summary_feedback']['recommendation'] ?? '' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <footer class="messages-panel__footer">
                                        @if($activeChat->type === 'ai_mock_interview' && $activeChat->status !== 'completed')
                                            <form wire:submit.prevent="sendMessage" class="message-compose">
                                                <textarea wire:model="newMessage" placeholder="Nhập câu trả lời của bạn..." aria-label="Câu trả lời"></textarea>
                                                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage">
                                                    <span wire:loading.remove wire:target="sendMessage">Gửi</span>
                                                    <span wire:loading wire:target="sendMessage">Đang gửi</span>
                                                </button>
                                            </form>
                                        @elseif($activeChat->type === 'employer_candidate')
                                            <p class="read-only-note">Đây là tin nhắn thông báo. Bạn không thể phản hồi tại đây.</p>
                                        @endif
                                    </footer>
                                @else
                                    <div class="messages-empty-state">
                                        <span aria-hidden="true">...</span>
                                        <h5>Chọn một cuộc trò chuyện</h5>
                                        <p>Nội dung trao đổi sẽ hiển thị ở đây.</p>
                                    </div>
                                @endif
                            </section>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </section>

    <style>
        .candidate-messages { --message-ink:#172033; --message-muted:#687386; --message-line:#e5eaf0; --message-surface:#fff; --message-canvas:#f7f9fb; --message-accent:#e85d18; }
        .candidate-messages .message-page-box { padding:28px; background:transparent; }
        .messages-page-heading { display:flex; align-items:baseline; gap:12px; margin:0 0 18px; }
        .messages-page-heading p { color:var(--message-accent); font-size:11px; font-weight:800; letter-spacing:.1em; margin:0; }
        .messages-page-heading h3 { color:var(--message-ink); font-size:24px; font-weight:750; letter-spacing:-.03em; margin:0; }
        .messages-page-heading span { color:var(--message-muted); font-size:13px; }
        .messages-layout { background:var(--message-surface); border:1px solid var(--message-line); border-radius:16px; box-shadow:0 16px 42px rgba(20,35,55,.06); display:grid; grid-template-columns:minmax(235px,31%) 1fr; min-height:600px; overflow:hidden; }
        .messages-sidebar { border-right:1px solid var(--message-line); min-width:0; }
        .messages-sidebar__header { border-bottom:1px solid var(--message-line); color:#465266; font-size:12px; font-weight:750; padding:18px 18px 15px; }
        .messages-sidebar__body { max-height:540px; overflow:auto; padding:8px; }
        .conversation-item { align-items:center; background:transparent; border:0; border-radius:10px; color:inherit; cursor:pointer; display:flex; gap:10px; padding:12px 10px; text-align:left; transition:background .16s ease; width:100%; }
        .conversation-item:hover { background:#f7f9fb; }.conversation-item.is-active { background:#fff2eb; }
        .conversation-avatar,.panel-avatar,.message-avatar { align-items:center; background:#e8edf3; border-radius:50%; color:#526073; display:inline-flex; flex:0 0 auto; font-size:11px; font-weight:800; justify-content:center; }
        .conversation-avatar { height:36px; width:36px; }.panel-avatar { height:38px; width:38px; }.message-avatar { height:28px; margin-top:2px; width:28px; }
        .conversation-avatar.is-ai,.panel-avatar.is-ai,.message-avatar.is-ai { background:#fbe8dc; color:#c6470d; }
        .conversation-copy { display:flex; flex:1; flex-direction:column; min-width:0; }.conversation-copy strong { color:var(--message-ink); font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }.conversation-copy small { color:var(--message-muted); font-size:11px; margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .conversation-status { color:var(--message-accent); font-size:10px; font-weight:750; white-space:nowrap; }.conversation-status.is-complete { color:#758195; }.messages-empty-list { color:var(--message-muted); font-size:13px; padding:26px 14px; text-align:center; }
        .messages-panel { background:var(--message-canvas); display:flex; flex-direction:column; min-width:0; }.messages-panel__header { align-items:center; background:#fff; border-bottom:1px solid var(--message-line); display:flex; gap:11px; min-height:70px; padding:14px 20px; }.messages-panel__header h4 { color:var(--message-ink); font-size:14px; font-weight:750; margin:0; }.messages-panel__header p { color:var(--message-muted); font-size:12px; margin:3px 0 0; }
        .messages-panel__content { flex:1; min-height:0; overflow:auto; padding:22px 24px; }.messages-panel__content.is-loading { opacity:.55; }.chat-day-label { color:#8993a2; font-size:10px; font-weight:750; letter-spacing:.08em; margin-bottom:20px; text-align:center; text-transform:uppercase; }.message-stream { display:flex; flex-direction:column; gap:14px; }.message-row { align-items:flex-start; display:flex; gap:8px; }.message-row.is-own { justify-content:flex-end; }.message-stack { max-width:min(82%,560px); }.message-bubble { background:#fff; border:1px solid #e6ebf0; border-radius:4px 13px 13px; color:#354052; font-size:13px; line-height:1.55; padding:10px 13px; white-space:pre-wrap; word-break:break-word; }.is-own .message-bubble { background:var(--message-accent); border-color:var(--message-accent); border-radius:13px 4px 13px 13px; color:#fff; }.message-stack time { color:#929cab; display:block; font-size:10px; margin-top:4px; }.is-own time { text-align:right; }.message-feedback { background:#fff; border-left:2px solid #f1a275; color:#566174; font-size:12px; line-height:1.45; margin-top:7px; padding:9px 11px; }.message-feedback strong { color:#394457; }.message-feedback p { margin:5px 0 0; }
        .interview-summary { background:#fff; border:1px solid #f3c8b0; margin:10px auto 4px; max-width:350px; padding:20px; text-align:center; width:100%; }.interview-summary p { color:#b44a18; font-size:10px; font-weight:800; letter-spacing:.1em; margin:0 0 7px; }.interview-summary strong { color:#bd430b; display:block; font-size:38px; line-height:1; }.interview-summary strong small { font-size:16px; font-weight:600; }.interview-summary span { color:#5d6879; display:block; font-size:12px; line-height:1.45; margin-top:10px; }
        .messages-panel__footer { background:#fff; border-top:1px solid var(--message-line); padding:14px 18px; }.message-compose { align-items:flex-end; display:flex; gap:10px; }.message-compose textarea { background:#f8fafc; border:1px solid #dfe5ec; border-radius:10px; color:var(--message-ink); flex:1; font:inherit; font-size:13px; line-height:1.45; min-height:46px; outline:none; padding:12px; resize:vertical; }.message-compose textarea:focus { background:#fff; border-color:#ed955f; box-shadow:0 0 0 3px rgba(232,93,24,.1); }.message-compose button { background:var(--message-accent); border:0; border-radius:9px; color:#fff; cursor:pointer; font-size:13px; font-weight:750; min-height:46px; padding:0 18px; transition:background .16s ease,transform .16s ease; }.message-compose button:hover { background:#cf4c0d; }.message-compose button:active { transform:translateY(1px); }.message-compose button:disabled { cursor:wait; opacity:.65; }.read-only-note { color:var(--message-muted); font-size:12px; margin:1px 0; text-align:center; }.messages-empty-state { align-items:center; background:#fff; color:var(--message-muted); display:flex; flex:1; flex-direction:column; justify-content:center; min-height:430px; padding:30px; text-align:center; }.messages-empty-state span { color:#aab3bf; font-size:25px; font-weight:800; letter-spacing:3px; line-height:1; }.messages-empty-state h5 { color:var(--message-ink); font-size:15px; margin:13px 0 4px; }.messages-empty-state p { font-size:13px; margin:0; }
        @media (max-width:991px) { .candidate-messages .message-page-box { padding:20px 0; }.messages-layout { grid-template-columns:1fr; }.messages-sidebar { border-bottom:1px solid var(--message-line); border-right:0; }.messages-sidebar__body { max-height:220px; }.messages-panel { min-height:520px; } }
        @media (max-width:575px) { .messages-page-heading { flex-wrap:wrap; gap:6px 10px; }.messages-layout { border-left:0; border-radius:0; border-right:0; margin-left:-15px; margin-right:-15px; }.messages-panel__header,.messages-panel__content { padding-left:15px; padding-right:15px; }.message-stack { max-width:88%; }.conversation-item { padding-left:8px; padding-right:8px; }.conversation-status { display:none; }.message-compose { align-items:stretch; flex-direction:column; }.message-compose button { min-height:42px; }.messages-panel { min-height:500px; } }
    </style>
</div>
