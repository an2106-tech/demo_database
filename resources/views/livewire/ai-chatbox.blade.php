<div class="candidate-assistant-root">
    @if ($enabled)
        <style>
            /* Unified Floating Assistant & AI Chatbox Styling */
            .candidate-assistant-root {
                position: relative;
                z-index: 10000;
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            }

            /* Floating Trigger Button */
            .cd-assistant-trigger {
                position: fixed;
                bottom: 28px;
                right: 28px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
                color: #ffffff !important;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                box-shadow: 0 8px 24px -4px rgba(243, 112, 33, 0.45);
                border: 2px solid rgba(255, 255, 255, 0.85);
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                outline: none;
                z-index: 10001;
            }

            .cd-assistant-trigger:hover {
                transform: scale(1.08) translateY(-2px);
                box-shadow: 0 12px 28px -4px rgba(243, 112, 33, 0.55);
            }

            .cd-assistant-trigger:active {
                transform: scale(0.95);
            }

            .cd-assistant-pulse {
                position: absolute;
                inset: -4px;
                border-radius: 50%;
                background: rgba(243, 112, 33, 0.4);
                animation: cdPulse 2.5s infinite;
                pointer-events: none;
                z-index: -1;
            }

            @keyframes cdPulse {
                0% { transform: scale(1); opacity: 0.8; }
                50% { transform: scale(1.25); opacity: 0; }
                100% { transform: scale(1); opacity: 0; }
            }

            /* Floating Chat Window */
            .cd-assistant-window {
                position: fixed;
                bottom: 98px;
                right: 28px;
                width: 410px;
                max-width: calc(100vw - 32px);
                height: 610px;
                max-height: calc(100vh - 120px);
                background: #ffffff;
                border-radius: 20px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.22), 0 4px 16px -2px rgba(15, 23, 42, 0.08);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                z-index: 10000;
                transform-origin: bottom right;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            @media (max-width: 575px) {
                .cd-assistant-window {
                    right: 12px;
                    left: 12px;
                    bottom: 92px;
                    width: auto;
                    height: 80vh;
                    max-height: 620px;
                }
                .cd-assistant-trigger {
                    bottom: 20px;
                    right: 20px;
                    width: 54px;
                    height: 54px;
                    font-size: 20px;
                }
            }

            /* Header */
            .cd-assistant-header {
                padding: 14px 18px;
                background: #ffffff;
                color: #0f172a;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid #e2e8f0;
                flex-shrink: 0;
                min-height: 68px;
            }

            .cd-assistant-header h6 {
                color: #0f172a !important;
                margin: 0;
                font-size: 15px;
                font-weight: 700;
                letter-spacing: -0.01em;
            }

            .cd-status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #10b981;
                display: inline-block;
                box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
            }

            .cd-header-close-btn {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                color: #64748b;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s ease;
                font-size: 13px;
            }

            .cd-header-close-btn:hover {
                background: #f1f5f9;
                color: #0f172a;
                border-color: #cbd5e1;
                transform: rotate(90deg);
            }

            /* Mode Switcher Tabs */
            .cd-mode-switcher {
                display: flex;
                background: #f1f5f9;
                padding: 4px;
                gap: 4px;
                border-bottom: 1px solid #e2e8f0;
                flex-shrink: 0;
            }

            .cd-mode-tab {
                flex: 1;
                border: none;
                background: none;
                padding: 7px 10px;
                border-radius: 10px;
                font-size: 12.5px;
                font-weight: 700;
                color: #64748b;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .cd-mode-tab.is-active {
                background: #ffffff;
                color: #0f172a;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            }

            .cd-mode-tab:not(.is-active):hover {
                color: #0f172a;
            }

            /* Body Chat Stream */
            .cd-assistant-body {
                padding: 16px;
                overflow-y: auto;
                flex: 1;
                background: #f8fafc;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            /* Message Bubbles */
            .cd-msg-bubble-bot {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px 16px 16px 2px;
                padding: 12px 14px;
                font-size: 13.5px;
                color: #1e293b;
                line-height: 1.55;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
                max-width: 92%;
                align-self: flex-start;
            }

            .cd-msg-bubble-user,
            body.employer-app .cd-msg-bubble-user,
            body.client-app .cd-msg-bubble-user,
            .cd-msg-bubble-user * {
                color: #ffffff !important;
            }

            .cd-msg-bubble-user {
                background: linear-gradient(135deg, #f37021 0%, #ea580c 100%);
                color: #ffffff !important;
                border-radius: 16px 16px 2px 16px;
                padding: 10px 14px;
                font-size: 13.5px;
                line-height: 1.5;
                box-shadow: 0 4px 12px rgba(243, 112, 33, 0.2);
                max-width: 85%;
                align-self: flex-end;
                word-break: break-word;
            }

            .cd-msg-bubble-user .text-white-50,
            body.employer-app .cd-msg-bubble-user .text-white-50,
            body.client-app .cd-msg-bubble-user .text-white-50 {
                color: rgba(255, 255, 255, 0.75) !important;
            }

            .cd-msg-source-tag {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                background: #eff6ff;
                color: #2563eb;
                border: 1px solid #bfdbfe;
                border-radius: 6px;
                padding: 3px 8px;
                font-size: 11px;
                font-weight: 600;
                text-decoration: none !important;
                margin-top: 6px;
                margin-right: 4px;
                transition: all 0.2s ease;
            }

            .cd-msg-source-tag:hover {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .cd-msg-suggestion-chip {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                color: #334155;
                border-radius: 999px;
                padding: 5px 11px;
                font-size: 11.5px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
                margin-top: 4px;
                margin-right: 4px;
            }

            .cd-msg-suggestion-chip:hover {
                background: #f8fafc;
                border-color: #f37021;
                color: #f37021;
                transform: translateY(-1px);
            }

            /* Chat Input Footer */
            .cd-chat-footer {
                padding: 10px 14px;
                background: #ffffff;
                border-top: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
            }

            .cd-chat-input {
                flex: 1;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 8px 12px;
                font-size: 13px;
                outline: none;
                transition: border-color 0.2s ease;
                background: #f8fafc;
            }

            .cd-chat-input:focus {
                border-color: #f37021;
                background: #ffffff;
                box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.1);
            }

            .cd-chat-send-btn {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: #f37021;
                color: #ffffff;
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.2s ease;
                flex-shrink: 0;
            }

            .cd-chat-send-btn:hover:not(:disabled) {
                background: #e05f12;
                transform: scale(1.05);
            }

            .cd-chat-send-btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            /* Menu Button for Shortcuts */
            .cd-menu-btn {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 14px;
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                color: #0f172a;
                font-size: 13.5px;
                font-weight: 600;
                text-decoration: none !important;
                transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                cursor: pointer;
                width: 100%;
                text-align: left;
            }

            .cd-menu-btn:hover {
                border-color: #f37021;
                transform: translateY(-1.5px);
                box-shadow: 0 4px 12px rgba(243, 112, 33, 0.12);
                color: #f37021;
            }

            .cd-menu-icon {
                width: 34px;
                height: 34px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 15px;
                flex-shrink: 0;
            }
        </style>

        <!-- Floating Trigger Bubble (100% Matching Candidate) -->
        <button 
            type="button" 
            class="cd-assistant-trigger" 
            wire:click="toggle"
            title="{{ $assistantTitle }}"
            aria-label="{{ $assistantTitle }}"
        >
            <span class="cd-assistant-pulse"></span>
            @if($isOpen)
                <i class="fa fa-times"></i>
            @else
                <i class="fa fa-comments"></i>
            @endif
        </button>

        <!-- Floating Chatbox Window -->
        @if($isOpen)
            <div class="cd-assistant-window" wire:transition>
                <!-- Header (100% Matching Candidate) -->
                <div class="cd-assistant-header">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, #f37021, #ea580c); display: flex; align-items: center; justify-content: center; font-size: 14px; color: #fff;">
                            <i class="fa fa-magic"></i>
                        </div>
                        <div>
                            <h6>{{ $assistantTitle }}</h6>
                            <div style="font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 5px;">
                                <span class="cd-status-indicator"></span> {{ $assistantSubtitle }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($mainMode === 'ai_chat')
                            <button type="button" class="cd-header-close-btn" wire:click="newConversation" title="Cuộc trò chuyện mới">
                                <i class="fa fa-refresh"></i>
                            </button>
                        @endif
                        <button type="button" class="cd-header-close-btn" wire:click="close" title="Đóng">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Mode Switcher Tabs -->
                <div class="cd-mode-switcher">
                    <button 
                        type="button" 
                        class="cd-mode-tab {{ $mainMode === 'ai_chat' ? 'is-active' : '' }}"
                        wire:click="switchMainMode('ai_chat')"
                    >
                        <i class="fa fa-comments text-primary"></i> Trò chuyện AI
                    </button>
                    <button 
                        type="button" 
                        class="cd-mode-tab {{ $mainMode === 'shortcuts' ? 'is-active' : '' }}"
                        wire:click="switchMainMode('shortcuts')"
                    >
                        <i class="fa fa-th-large text-warning"></i> Lối tắt nhanh
                    </button>
                </div>

                <!-- MODE 1: AI CHATBOX -->
                @if($mainMode === 'ai_chat')
                    <div class="cd-assistant-body" id="aiChatMessages" x-data x-init="$el.scrollTop = $el.scrollHeight" x-effect="$el.scrollTop = $el.scrollHeight">
                        @if (empty($messages))
                            <div class="cd-msg-bubble-bot">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 10px; padding: 2px 6px;">AI Assistant</span>
                                    <span class="text-muted" style="font-size: 10px;">{{ now()->format('H:i') }}</span>
                                </div>
                                <div style="white-space: pre-line;">
                                    Xin chào **{{ auth()->user()->name }}**! 👋 Tôi là **{{ $assistantTitle }}**.
                                    {{ $assistantDescription }}
                                </div>
                            </div>

                            <!-- Quick Starter Prompts -->
                            <div class="mt-2">
                                <div style="font-size: 11.5px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">
                                    💡 Gợi ý câu hỏi nhanh:
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    @foreach ($quickPrompts as $prompt)
                                        <button 
                                            type="button" 
                                            class="btn btn-sm text-start p-2 rounded-3 bg-white border" 
                                            style="font-size: 12px; color: #334155;"
                                            wire:click="useSuggestion('{{ addslashes($prompt) }}')"
                                        >
                                            <i class="fa fa-bolt text-warning me-1"></i> {{ $prompt }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            @foreach ($messages as $chatMessage)
                                @if ($chatMessage['role'] === 'user')
                                    <div class="cd-msg-bubble-user">
                                        {{ $chatMessage['content'] }}
                                        <div class="text-end text-white-50 mt-1" style="font-size: 10px;">{{ $chatMessage['time'] }}</div>
                                    </div>
                                @else
                                    <div class="cd-msg-bubble-bot">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 10px; padding: 2px 6px;">AI Assistant</span>
                                            <span class="text-muted" style="font-size: 10px;">{{ $chatMessage['time'] }}</span>
                                        </div>

                                        <div style="white-space: pre-line;">
                                            {!! nl2br(e($chatMessage['content'])) !!}
                                        </div>

                                        <!-- Sources -->
                                        @if (! empty($chatMessage['sources']))
                                            <div class="mt-2 pt-2 border-top">
                                                <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px;">Liên kết liên quan:</div>
                                                <div class="d-flex flex-wrap">
                                                    @foreach ($chatMessage['sources'] as $source)
                                                        @if (! empty($source['url']))
                                                            <a href="{{ $source['url'] }}" target="_blank" class="cd-msg-source-tag">
                                                                <i class="fa fa-link"></i> {{ $source['label'] }}
                                                            </a>
                                                        @else
                                                            <span class="cd-msg-source-tag"><i class="fa fa-tag"></i> {{ $source['label'] }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Suggestions -->
                                        @if ($loop->last && ! empty($chatMessage['suggestions']))
                                            <div class="mt-2 pt-1">
                                                <div class="d-flex flex-wrap">
                                                    @foreach ($chatMessage['suggestions'] as $suggestion)
                                                        <button 
                                                            type="button" 
                                                            class="cd-msg-suggestion-chip" 
                                                            wire:click="useSuggestion('{{ addslashes($suggestion) }}')"
                                                        >
                                                            <i class="fa fa-lightbulb-o text-warning"></i> {{ $suggestion }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Loading Indicator -->
                        <div wire:loading.flex wire:target="sendMessage" class="cd-msg-bubble-bot align-self-start">
                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 12.5px;">
                                <i class="fa fa-circle-o-notch fa-spin text-primary"></i> {{ $assistantTitle }} đang tổng hợp dữ liệu...
                            </div>
                        </div>

                        @if ($error)
                            <div class="alert alert-danger p-2 mb-0" style="font-size: 11.5px; border-radius: 8px;">
                                <i class="fa fa-exclamation-triangle me-1"></i> {{ $error }}
                            </div>
                        @endif
                    </div>

                    <!-- Chat Input Footer -->
                    <div class="cd-chat-footer">
                        <input 
                            type="text" 
                            class="cd-chat-input" 
                            placeholder="Nhập câu hỏi cho {{ $assistantTitle }}..." 
                            wire:model="message"
                            wire:keydown.enter="sendMessage"
                        >
                        <button 
                            type="button" 
                            class="cd-chat-send-btn" 
                            wire:click="sendMessage"
                            wire:loading.attr="disabled"
                            wire:target="sendMessage"
                            title="Gửi câu hỏi"
                        >
                            <span wire:loading.remove wire:target="sendMessage">
                                <i class="fa fa-paper-plane"></i>
                            </span>
                            <span wire:loading wire:target="sendMessage">
                                <i class="fa fa-circle-o-notch fa-spin"></i>
                            </span>
                        </button>
                    </div>

                <!-- MODE 2: QUICK SHORTCUTS (EMPLOYER HUB) -->
                @else
                    <div class="cd-assistant-body">
                        <div class="cd-msg-bubble-bot mb-1">
                            Chọn nhanh các nghiệp vụ tuyển dụng bên dưới để thao tác ngay:
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('employers.manage_candidates') }}" class="cd-menu-btn">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #e0f2fe; color: #0284c7;">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <div>
                                        <div>Quản lý ứng viên</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            Xem hồ sơ & lọc trạng thái
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </a>

                            <a href="{{ route('employers.post_job') }}" class="cd-menu-btn">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #ede9fe; color: #7c3aed;">
                                        <i class="fa fa-plus-circle"></i>
                                    </div>
                                    <div>
                                        <div>Đăng tin tuyển dụng mới</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            Tạo tin tuyển dụng nhanh
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </a>

                            <a href="{{ route('employers.application_pipeline') }}" class="cd-menu-btn">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #ecfdf5; color: #059669;">
                                        <i class="fa fa-sitemap"></i>
                                    </div>
                                    <div>
                                        <div>Pipeline tuyển dụng</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            Theo dõi luồng ứng tuyển
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </a>

                            <a href="{{ route('employers.change_password') }}" class="cd-menu-btn">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #f1f5f9; color: #475569;">
                                        <i class="fa fa-shield"></i>
                                    </div>
                                    <div>
                                        <div>Bảo mật & Đổi mật khẩu</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            Bảo vệ tài khoản tuyển dụng
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif
</div>
