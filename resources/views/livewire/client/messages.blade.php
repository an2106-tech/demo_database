<div class="candidate-messages-page" x-data="{
    scrollToBottom() {
        this.$nextTick(() => {
            const stream = this.$refs.chatStream;
            if (stream) {
                stream.scrollTop = stream.scrollHeight;
            }
        });
    }
}" x-init="scrollToBottom()">
    {{-- Top Unified Breadcrumb --}}
    <div class="fpt-breadcrumb-bar">
        <div class="container-fluid px-lg-5">
            <div class="fpt-breadcrumb-inner">
                <ul class="fpt-breadcrumb-trail">
                    <li><a href="{{ route('home') }}"><i class="fa fa-home"></i> Trang chủ</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{ route('candidates.candidate_dashboard') }}">Ứng viên</a></li>
                    <li class="sep"><i class="fa fa-angle-right"></i></li>
                    <li class="current">Hộp thư & Phỏng vấn AI</li>
                </ul>

                <a href="{{ route('candidates.candidate_dashboard') }}" class="fpt-back-btn">
                    <i class="fa fa-arrow-left"></i> Bảng điều khiển
                </a>
            </div>
        </div>
    </div>

    {{-- Main Workspace Area --}}
    <section class="candidate-dashboard-area section_70" style="padding-top: 30px; padding-bottom: 70px;">
        <div class="container-fluid px-lg-5">
            <div class="row">
                {{-- Left Navigation Sidebar --}}
                <div class="col-lg-3 col-md-4 mx-auto dashboard-left-border">
                    @include('livewire.client.partials.candidate-sidebar')
                </div>

                {{-- Right Main Messages Hub --}}
                <div class="col-lg-9 col-md-8 mx-auto">
                    <main class="fpt-messages-hub">
                        {{-- Page Heading --}}
                        <div class="fpt-hub-header">
                            <div>
                                <span class="fpt-hub-eyebrow"><i class="fa fa-comments-o"></i> Hộp thư ứng viên</span>
                                <h1 class="fpt-hub-title">Tin nhắn & Phỏng vấn AI</h1>
                            </div>
                            <span class="fpt-hub-count-badge">
                                <i class="fa fa-inbox me-1 text-primary"></i> {{ $chats->count() }} cuộc trò chuyện
                            </span>
                        </div>

                        {{-- Double-Bezel Chat Container --}}
                        <div class="fpt-chat-shell">
                            <div class="fpt-chat-core">
                                {{-- Left Conversation List --}}
                                <aside class="fpt-chat-sidebar">
                                    <div class="fpt-sidebar-title-bar">
                                        <span>Danh sách hội thoại</span>
                                        <span class="badge bg-light text-muted border">{{ $chats->count() }}</span>
                                    </div>

                                    <div class="fpt-sidebar-scroll custom-scrollbar">
                                        @forelse($chats as $chat)
                                            @php
                                                $isActive = ($chat->id == $activeChatId);
                                                $isAi = ($chat->type === 'ai_mock_interview');
                                                $isCompleted = ($chat->status === 'completed');
                                            @endphp

                                            <div class="fpt-conv-item-wrapper position-relative">
                                                <button
                                                    type="button"
                                                    class="fpt-conv-item {{ $isActive ? 'is-active' : '' }}"
                                                    wire:click="selectChat({{ $chat->id }})"
                                                    @click="scrollToBottom()"
                                                >
                                                    <div class="fpt-conv-avatar {{ $isAi ? 'is-ai' : '' }}">
                                                        @if($isAi)
                                                            <i class="fa fa-magic"></i>
                                                        @else
                                                            {{ mb_strtoupper(mb_substr($chat->employer->name ?? 'NTD', 0, 1)) }}
                                                        @endif
                                                    </div>

                                                    <div class="fpt-conv-info">
                                                        <div class="fpt-conv-top-line">
                                                            <strong class="fpt-conv-name">
                                                                {{ $isAi ? 'Phỏng vấn AI' : ($chat->employer->name ?? 'Nhà tuyển dụng') }}
                                                            </strong>
                                                            <span class="fpt-conv-time">
                                                                {{ $chat->updated_at?->diffForHumans(null, true, true) ?? '' }}
                                                            </span>
                                                        </div>

                                                        <p class="fpt-conv-preview">
                                                            {{ $isAi ? ($chat->job->title ?? 'Buổi phỏng vấn năng lực') : 'Thông báo từ hội đồng tuyển dụng' }}
                                                        </p>

                                                        <div class="fpt-conv-tags">
                                                            @if($isAi)
                                                                <span class="fpt-tag {{ $isCompleted ? 'completed' : 'ongoing' }}">
                                                                    <i class="fa {{ $isCompleted ? 'fa-check-circle' : 'fa-circle-o-notch fa-spin' }}"></i>
                                                                    {{ $isCompleted ? 'Đã hoàn thành' : 'Đang diễn ra' }}
                                                                </span>
                                                            @else
                                                                <span class="fpt-tag employer">
                                                                    <i class="fa fa-building-o"></i> Thông báo
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </button>

                                                {{-- Quick Delete Button on Hover --}}
                                                <button
                                                    type="button"
                                                    class="fpt-conv-quick-delete"
                                                    wire:click.stop="deleteChat({{ $chat->id }})"
                                                    wire:confirm="Bạn có chắc chắn muốn xóa cuộc trò chuyện này?"
                                                    title="Xóa hội thoại"
                                                >
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        @empty
                                            <div class="fpt-conv-empty">
                                                <i class="fa fa-comment-o mb-2" style="font-size: 28px; color: #cbd5e1;"></i>
                                                <p class="mb-0">Chưa có cuộc trò chuyện nào.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </aside>

                                {{-- Right Message Stream & Panel --}}
                                <section class="fpt-chat-main">
                                    @if($activeChat)
                                        @php
                                            $isAiActive = ($activeChat->type === 'ai_mock_interview');
                                        @endphp

                                        {{-- Chat Main Header --}}
                                        <header class="fpt-chat-header">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="fpt-chat-header-avatar {{ $isAiActive ? 'is-ai' : '' }}">
                                                    @if($isAiActive)
                                                        <i class="fa fa-magic"></i>
                                                    @else
                                                        {{ mb_strtoupper(mb_substr($activeChat->employer->name ?? 'NTD', 0, 1)) }}
                                                    @endif
                                                </div>

                                                <div>
                                                    <h3 class="fpt-chat-header-name">
                                                        {{ $isAiActive ? 'Trợ lý Phỏng vấn AI' : ($activeChat->employer->name ?? 'Nhà tuyển dụng') }}
                                                    </h3>
                                                    <p class="fpt-chat-header-desc">
                                                        @if($isAiActive)
                                                            <i class="fa fa-briefcase text-primary me-1"></i> {{ $activeChat->job->title ?? 'Vị trí ứng tuyển' }}
                                                        @else
                                                            <i class="fa fa-bell-o text-muted me-1"></i> Kênh thông báo trực tiếp từ nhà tuyển dụng
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Header Action Buttons Group --}}
                                            <div class="fpt-chat-header-actions d-flex align-items-center gap-2">
                                                @if($isAiActive)
                                                    @if($activeChat->status !== 'completed')
                                                        <button
                                                            type="button"
                                                            class="fpt-btn-end-interview"
                                                            wire:click="endInterview"
                                                            wire:confirm="Bạn có chắc chắn muốn kết thúc buổi phỏng vấn AI ngay bây giờ để nhận đánh giá và chấm điểm tổng kết không?"
                                                            wire:loading.attr="disabled"
                                                            wire:target="endInterview"
                                                            title="Kết thúc phỏng vấn sớm để nhận điểm"
                                                        >
                                                            <span wire:loading.remove wire:target="endInterview">
                                                                <i class="fa fa-flag-checkered me-1 text-danger"></i> Kết thúc phỏng vấn
                                                            </span>
                                                            <span wire:loading wire:target="endInterview">
                                                                <i class="fa fa-circle-o-notch fa-spin"></i> Đang tổng kết...
                                                            </span>
                                                        </button>
                                                    @else
                                                        <span class="fpt-status-pill completed">
                                                            <i class="fa fa-check-circle"></i> Đã hoàn thành
                                                        </span>
                                                    @endif
                                                @endif

                                                <button
                                                    type="button"
                                                    class="fpt-btn-delete-chat"
                                                    wire:click="deleteChat({{ $activeChat->id }})"
                                                    wire:confirm="Bạn có chắc chắn muốn xóa vĩnh viễn cuộc trò chuyện này không? Toàn bộ lịch sử tin nhắn sẽ bị xóa vĩnh viễn."
                                                    wire:loading.attr="disabled"
                                                    wire:target="deleteChat({{ $activeChat->id }})"
                                                    title="Xóa cuộc hội thoại này"
                                                >
                                                    <span wire:loading.remove wire:target="deleteChat({{ $activeChat->id }})">
                                                        <i class="fa fa-trash-o"></i>
                                                    </span>
                                                    <span wire:loading wire:target="deleteChat({{ $activeChat->id }})">
                                                        <i class="fa fa-circle-o-notch fa-spin"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </header>

                                        {{-- Messages Scroll Stream --}}
                                        <div
                                            class="fpt-chat-stream custom-scrollbar"
                                            x-ref="chatStream"
                                            wire:loading.class="is-loading"
                                            wire:target="selectChat,sendMessage,endInterview"
                                        >
                                            <div class="fpt-stream-divider">
                                                <span><i class="fa fa-lock me-1"></i> Cuộc trò chuyện được bảo mật & lưu trữ trên FPT Careers</span>
                                            </div>

                                            <div class="fpt-messages-list">
                                                @foreach($activeChat->messages->reverse() as $msg)
                                                    @php
                                                        $isOwn = ($msg->sender_type === 'candidate');
                                                        $isAiMsg = ($msg->sender_type === 'ai');
                                                    @endphp

                                                    <div class="fpt-msg-row {{ $isOwn ? 'is-own' : '' }}">
                                                        @if(!$isOwn)
                                                            <div class="fpt-msg-avatar {{ $isAiMsg ? 'is-ai' : '' }}">
                                                                @if($isAiMsg)
                                                                    <i class="fa fa-magic"></i>
                                                                @else
                                                                    {{ mb_strtoupper(mb_substr($activeChat->employer->name ?? 'NTD', 0, 1)) }}
                                                                @endif
                                                            </div>
                                                        @endif

                                                        <div class="fpt-msg-stack">
                                                            <div class="fpt-msg-bubble">
                                                                {!! nl2br(e($msg->content)) !!}
                                                            </div>

                                                            {{-- AI Scoring & Detailed Feedback Card --}}
                                                            @if($isAiMsg && !empty($msg->metadata['score']))
                                                                <div class="fpt-ai-feedback-card">
                                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                                        <span class="fpt-feedback-label">
                                                                            <i class="fa fa-star text-warning me-1"></i> Đánh giá câu trả lời
                                                                        </span>
                                                                        <span class="fpt-score-badge">
                                                                            {{ $msg->metadata['score'] }} / 10
                                                                        </span>
                                                                    </div>

                                                                    @if(!empty($msg->metadata['feedback']))
                                                                        <p class="fpt-feedback-text">
                                                                            {{ $msg->metadata['feedback'] }}
                                                                        </p>
                                                                    @endif

                                                                    @if(!empty($msg->metadata['suggested_answer']))
                                                                        <div class="fpt-suggested-answer">
                                                                            <strong><i class="fa fa-lightbulb-o me-1 text-primary"></i> Gợi ý hoàn thiện:</strong>
                                                                            <p class="mb-0 mt-1">{{ $msg->metadata['suggested_answer'] }}</p>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            <time class="fpt-msg-time">{{ $msg->created_at->format('H:i • d/m/Y') }}</time>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                {{-- Interview Final Result Summary Card --}}
                                                @if($isAiActive && $activeChat->status === 'completed')
                                                    @php
                                                        $summaryData = $activeChat->metadata['summary_feedback'] ?? [];
                                                        $totalScore = $activeChat->metadata['total_score'] ?? 0;
                                                        $pros = (array) ($summaryData['pros'] ?? []);
                                                        $cons = (array) ($summaryData['cons'] ?? []);
                                                        $recommendation = (string) ($summaryData['recommendation'] ?? 'Đã hoàn thành các vòng phỏng vấn thử nghiệm thành công.');
                                                    @endphp
                                                    <div class="fpt-final-summary-card">
                                                        <div class="fpt-summary-icon"><i class="fa fa-trophy"></i></div>
                                                        <span class="fpt-summary-eyebrow">TỔNG KẾT & ĐÁNH GIÁ PHỎNG VẤN AI</span>
                                                        <div class="fpt-summary-score">
                                                            {{ $totalScore }}<small>/100</small>
                                                        </div>

                                                        <div class="fpt-summary-rec-box text-start mb-3">
                                                            <strong class="d-block mb-1 text-dark" style="font-size: 13px;"><i class="fa fa-commenting-o me-1 text-primary"></i> Nhận xét từ Hội đồng Tuyển dụng:</strong>
                                                            <p class="fpt-summary-rec mb-0">{{ $recommendation }}</p>
                                                        </div>

                                                        @if(!empty($pros) && count(array_filter($pros, fn($p) => $p !== 'Không có')) > 0)
                                                            <div class="fpt-summary-section text-start mb-2">
                                                                <strong class="d-block text-success mb-1" style="font-size: 12px;"><i class="fa fa-check-circle me-1"></i> Điểm nổi bật:</strong>
                                                                <ul class="mb-0 ps-3" style="font-size: 12.5px; color: #334155;">
                                                                    @foreach(array_filter($pros, fn($p) => $p !== 'Không có') as $pro)
                                                                        <li>{{ $pro }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif

                                                        @if(!empty($cons))
                                                            <div class="fpt-summary-section text-start mb-0">
                                                                <strong class="d-block text-danger mb-1" style="font-size: 12px;"><i class="fa fa-exclamation-circle me-1"></i> Cần cải thiện:</strong>
                                                                <ul class="mb-0 ps-3" style="font-size: 12.5px; color: #334155;">
                                                                    @foreach($cons as $con)
                                                                        <li>{{ $con }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Chat Composer Footer --}}
                                        <footer class="fpt-chat-footer">
                                            @if($isAiActive && $activeChat->status !== 'completed')
                                                <form wire:submit.prevent="sendMessage" class="fpt-composer-form" @submit="scrollToBottom()">
                                                    <div class="fpt-composer-input-wrap">
                                                        <textarea
                                                            wire:model="newMessage"
                                                            placeholder="Nhập câu trả lời của bạn cho câu hỏi phỏng vấn... (Enter để gửi)"
                                                            rows="1"
                                                            class="fpt-composer-textarea"
                                                            wire:keydown.enter.prevent="sendMessage"
                                                            @keydown.enter="scrollToBottom()"
                                                        ></textarea>
                                                    </div>

                                                    <button
                                                        type="submit"
                                                        class="fpt-composer-send-btn"
                                                        wire:loading.attr="disabled"
                                                        wire:target="sendMessage"
                                                    >
                                                        <span wire:loading.remove wire:target="sendMessage">
                                                            <span>Gửi</span>
                                                            <i class="fa fa-paper-plane ms-1"></i>
                                                        </span>
                                                        <span wire:loading wire:target="sendMessage">
                                                            <i class="fa fa-circle-o-notch fa-spin"></i>
                                                        </span>
                                                    </button>
                                                </form>
                                            @elseif($activeChat->type === 'employer_candidate')
                                                <div class="fpt-readonly-callout">
                                                    <i class="fa fa-info-circle me-1 text-primary"></i>
                                                    <span>Đây là tin nhắn thông báo 1 chiều từ nhà tuyển dụng. Để liên hệ lại, vui lòng phản hồi qua email hoặc hotline liên hệ trong thông báo.</span>
                                                </div>
                                            @else
                                                <div class="fpt-readonly-callout text-success">
                                                    <i class="fa fa-check-circle me-1"></i>
                                                    <span>Phiên phỏng vấn AI đã kết thúc. Bạn có thể xem lại toàn bộ câu hỏi và phản hồi chi tiết ở trên.</span>
                                                </div>
                                            @endif
                                        </footer>
                                    @else
                                        {{-- Empty State --}}
                                        <div class="fpt-chat-empty-state">
                                            <div class="fpt-empty-icon-wrap">
                                                <i class="fa fa-comments-o"></i>
                                            </div>
                                            <h4 class="fpt-empty-title">Chọn một cuộc hội thoại</h4>
                                            <p class="fpt-empty-desc">
                                                Xem lại các phiên phỏng vấn thử nghiệm AI hoặc cập nhật thông báo từ nhà tuyển dụng FPT Education.
                                            </p>
                                        </div>
                                    @endif
                                </section>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </section>

    {{-- Scoped Luxury CSS for Messages Hub --}}
    <style>
        .candidate-messages-page {
            --fpt-bg: #f8fafc;
            --fpt-surface: #ffffff;
            --fpt-ink: #0f172a;
            --fpt-muted: #64748b;
            --fpt-line: #e2e8f0;
            --fpt-line-subtle: #f1f5f9;
            --fpt-primary: #f37021;
            --fpt-primary-hover: #ea580c;
            --fpt-primary-soft: rgba(243, 112, 33, 0.08);
            --fpt-primary-glow: rgba(243, 112, 33, 0.22);
            --fpt-ease: cubic-bezier(0.16, 1, 0.3, 1);

            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--fpt-ink);
        }

        .candidate-messages-page .fa {
            font-family: 'FontAwesome', FontAwesome !important;
            font-style: normal;
        }

        /* Top Hub Header */
        .fpt-messages-hub {
            padding: 0;
        }

        .fpt-hub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }

        .fpt-hub-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 8px;
            border: 1px solid rgba(243, 112, 33, 0.16);
        }

        .fpt-hub-title {
            font-size: 24px;
            font-weight: 850;
            color: var(--fpt-ink);
            letter-spacing: -0.02em;
            margin: 0;
        }

        .fpt-hub-count-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        /* Double-Bezel Hardware Chat Shell */
        .fpt-chat-shell {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 24px;
            padding: 6px;
            box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.06), 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .fpt-chat-core {
            display: grid;
            grid-template-columns: 320px 1fr;
            background: #f8fafc;
            border: 1px solid var(--fpt-line-subtle);
            border-radius: 18px;
            height: 680px;
            max-height: calc(100vh - 220px);
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .fpt-chat-core {
                grid-template-columns: 1fr;
                height: 720px;
            }
        }

        /* Sidebar Conversation List */
        .fpt-chat-sidebar {
            background: #ffffff;
            border-right: 1px solid var(--fpt-line);
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            min-width: 0;
        }

        .fpt-sidebar-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            border-bottom: 1px solid var(--fpt-line);
            font-size: 13px;
            font-weight: 800;
            color: var(--fpt-ink);
            flex-shrink: 0;
        }

        .fpt-sidebar-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .fpt-conv-item-wrapper {
            position: relative;
        }

        .fpt-conv-item {
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            padding-right: 34px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 14px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s var(--fpt-ease);
        }

        .fpt-conv-item:hover {
            background: #f8fafc;
            border-color: var(--fpt-line);
        }

        .fpt-conv-item.is-active {
            background: #fff8f3;
            border-color: rgba(243, 112, 33, 0.25);
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.08);
        }

        .fpt-conv-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            flex-shrink: 0;
            border: 1px solid var(--fpt-line);
            transition: transform 0.2s ease;
        }

        .fpt-conv-avatar.is-ai {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            color: var(--fpt-primary);
            border-color: #ffddc2;
            font-size: 16px;
        }

        .fpt-conv-item:hover .fpt-conv-avatar {
            transform: scale(1.05);
        }

        .fpt-conv-info {
            flex: 1;
            min-width: 0;
        }

        .fpt-conv-top-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 3px;
        }

        .fpt-conv-name {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--fpt-ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fpt-conv-time {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
        }

        .fpt-conv-preview {
            font-size: 12px;
            color: var(--fpt-muted);
            margin: 0 0 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4;
        }

        .fpt-conv-tags {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fpt-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 750;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .fpt-tag.ongoing {
            background: #fff7ed;
            color: var(--fpt-primary);
            border: 1px solid #ffedd5;
        }

        .fpt-tag.completed {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #dcfce7;
        }

        .fpt-tag.employer {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .fpt-conv-quick-delete {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            opacity: 0;
            transition: all 0.2s ease;
        }

        .fpt-conv-item-wrapper:hover .fpt-conv-quick-delete {
            opacity: 1;
        }

        .fpt-conv-quick-delete:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .fpt-conv-empty {
            text-align: center;
            padding: 40px 16px;
            color: var(--fpt-muted);
            font-size: 13px;
        }

        /* Right Chat Main Area */
        .fpt-chat-main {
            background: #ffffff;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            min-width: 0;
        }

        .fpt-chat-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--fpt-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            gap: 16px;
            flex-shrink: 0;
        }

        .fpt-chat-header-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            flex-shrink: 0;
            border: 1px solid var(--fpt-line);
        }

        .fpt-chat-header-avatar.is-ai {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            color: var(--fpt-primary);
            border-color: #ffddc2;
        }

        .fpt-chat-header-name {
            font-size: 15px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0 0 2px;
        }

        .fpt-chat-header-desc {
            font-size: 12.5px;
            color: var(--fpt-muted);
            margin: 0;
            line-height: 1.4;
        }

        /* Action Buttons in Header */
        .fpt-btn-end-interview {
            display: inline-flex;
            align-items: center;
            padding: 7px 14px;
            border-radius: 10px;
            background: #fff1f2;
            color: #e11d48 !important;
            border: 1px solid #ffe4e6;
            font-size: 12.5px;
            font-weight: 750;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-btn-end-interview:hover {
            background: #ffe4e6;
            border-color: #fecdd3;
            transform: translateY(-1px);
        }

        .fpt-btn-delete-chat {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f8fafc;
            color: #64748b;
            border: 1px solid var(--fpt-line);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fpt-btn-delete-chat:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
            transform: translateY(-1px);
        }

        .fpt-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 750;
            padding: 4px 12px;
            border-radius: 999px;
        }

        .fpt-status-pill.completed {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #dcfce7;
        }

        /* Message Stream with Smooth Independent Scrolling */
        .fpt-chat-stream {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            scroll-behavior: smooth;
        }

        .fpt-chat-stream.is-loading {
            opacity: 0.6;
        }

        .fpt-stream-divider {
            text-align: center;
            margin-bottom: 24px;
            flex-shrink: 0;
        }

        .fpt-stream-divider span {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            padding: 4px 14px;
            border-radius: 999px;
        }

        .fpt-messages-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex-grow: 1;
        }

        .fpt-msg-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            max-width: 85%;
        }

        .fpt-msg-row.is-own {
            margin-left: auto;
            justify-content: flex-end;
        }

        .fpt-msg-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            flex-shrink: 0;
            border: 1px solid var(--fpt-line);
            margin-top: 2px;
        }

        .fpt-msg-avatar.is-ai {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            color: var(--fpt-primary);
            border-color: #ffddc2;
        }

        .fpt-msg-stack {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .fpt-msg-bubble {
            background: #ffffff;
            border: 1px solid var(--fpt-line);
            border-radius: 18px 18px 18px 4px;
            padding: 13px 18px;
            color: #1e293b;
            font-size: 13.5px;
            line-height: 1.6;
            word-break: break-word;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .is-own .fpt-msg-bubble {
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            border-color: transparent;
            border-radius: 18px 18px 4px 18px;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(243, 112, 33, 0.22);
        }

        .fpt-msg-time {
            font-size: 10.5px;
            color: #94a3b8;
            font-weight: 600;
            margin-top: 5px;
            display: block;
        }

        .is-own .fpt-msg-time {
            text-align: right;
        }

        /* AI Feedback Card Inside Bubble */
        .fpt-ai-feedback-card {
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-radius: 14px;
            padding: 14px;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.06);
        }

        .fpt-feedback-label {
            font-size: 11.5px;
            font-weight: 800;
            color: #c2410c;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .fpt-score-badge {
            background: #ffedd5;
            color: #c2410c;
            font-size: 12px;
            font-weight: 850;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid #fed7aa;
        }

        .fpt-feedback-text {
            font-size: 13px;
            color: #334155;
            line-height: 1.55;
            margin: 0 0 10px;
        }

        .fpt-suggested-answer {
            background: #fffaf5;
            border: 1px dashed rgba(243, 112, 33, 0.3);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 12.5px;
            color: #475569;
            line-height: 1.5;
        }

        /* Final Summary Scorecard */
        .fpt-final-summary-card {
            background: #ffffff;
            border: 2px solid #fdba74;
            border-radius: 20px;
            padding: 28px 24px;
            text-align: center;
            max-width: 480px;
            margin: 20px auto 10px;
            box-shadow: 0 16px 36px -8px rgba(243, 112, 33, 0.14);
            flex-shrink: 0;
        }

        .fpt-summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 12px;
            border: 1px solid #fed7aa;
        }

        .fpt-summary-eyebrow {
            display: block;
            font-size: 11px;
            font-weight: 850;
            color: #ea580c;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
        }

        .fpt-summary-score {
            font-size: 42px;
            font-weight: 900;
            color: var(--fpt-ink);
            line-height: 1;
            margin-bottom: 10px;
        }

        .fpt-summary-score small {
            font-size: 18px;
            font-weight: 700;
            color: #94a3b8;
        }

        .fpt-summary-rec {
            font-size: 13.5px;
            color: #475569;
            line-height: 1.6;
            margin: 0;
        }

        /* Composer Footer */
        .fpt-chat-footer {
            background: #ffffff;
            border-top: 1px solid var(--fpt-line);
            padding: 16px 20px;
            flex-shrink: 0;
        }

        .fpt-composer-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fpt-composer-input-wrap {
            flex: 1;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 14px;
            padding: 4px 14px;
            transition: all 0.2s ease;
        }

        .fpt-composer-input-wrap:focus-within {
            background: #ffffff;
            border-color: var(--fpt-primary);
            box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.12);
        }

        .fpt-composer-textarea {
            width: 100%;
            border: none;
            background: transparent;
            font-size: 13.5px;
            color: var(--fpt-ink);
            outline: none;
            resize: none;
            padding: 8px 0;
            font-family: inherit;
            line-height: 1.4;
            max-height: 120px;
        }

        .fpt-composer-send-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 46px;
            padding: 0 22px;
            background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.25);
            transition: all 0.2s var(--fpt-ease);
            white-space: nowrap;
        }

        .fpt-composer-send-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(243, 112, 33, 0.35);
        }

        .fpt-composer-send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .fpt-readonly-callout {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--fpt-muted);
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid var(--fpt-line);
            border-radius: 12px;
            line-height: 1.5;
        }

        /* Empty State */
        .fpt-chat-empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            text-align: center;
        }

        .fpt-empty-icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: var(--fpt-primary-soft);
            color: var(--fpt-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
        }

        .fpt-empty-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--fpt-ink);
            margin: 0 0 8px;
        }

        .fpt-empty-desc {
            font-size: 13.5px;
            color: var(--fpt-muted);
            max-width: 380px;
            line-height: 1.6;
            margin: 0;
        }

        /* Custom Sleek Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scrollChat = () => {
                const stream = document.querySelector('.fpt-chat-stream');
                if (stream) {
                    stream.scrollTop = stream.scrollHeight;
                }
            };

            scrollChat();

            if (window.Livewire) {
                Livewire.hook('morph.updated', () => {
                    window.setTimeout(scrollChat, 60);
                });
                Livewire.hook('commit', () => {
                    window.setTimeout(scrollChat, 60);
                });
            }
        });
    </script>
</div>
