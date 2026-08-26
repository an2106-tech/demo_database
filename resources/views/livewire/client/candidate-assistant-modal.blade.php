<div class="candidate-assistant-root">
    <style>
        /* Floating Assistant Bubble & Full AI Chatbox Styling */
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

        /* Menu Button */
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

        .cd-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 700;
            color: #64748b;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: color 0.2s ease;
        }

        .cd-back-btn:hover {
            color: #f37021;
        }
    </style>

    <!-- Floating Trigger Bubble -->
    <button 
        type="button" 
        class="cd-assistant-trigger" 
        wire:click="toggleOpen"
        title="Trợ lý AI & Lối tắt tài khoản FPT"
        aria-label="Trợ lý AI"
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
            <!-- Header -->
            <div class="cd-assistant-header">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, #f37021, #ea580c); display: flex; align-items: center; justify-content: center; font-size: 14px; color: #fff;">
                        <i class="fa fa-magic"></i>
                    </div>
                    <div>
                        <h6>Trợ lý AI FPT Careers</h6>
                        <div style="font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 5px;">
                            <span class="cd-status-indicator"></span> Trực tuyến 24/7
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($mainMode === 'ai_chat')
                        <button type="button" class="cd-header-close-btn" wire:click="clearAiChat" title="Làm mới cuộc trò chuyện">
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
                <div class="cd-assistant-body" id="cdChatBody" x-data x-init="$el.scrollTop = $el.scrollHeight" x-effect="$el.scrollTop = $el.scrollHeight">
                    <!-- Chat Stream Messages -->
                    @foreach($chatMessages as $msg)
                        @if($msg['role'] === 'user')
                            <div class="cd-msg-bubble-user">
                                {{ $msg['content'] }}
                                <div class="text-end text-white-50 mt-1" style="font-size: 10px;">{{ $msg['created_at'] }}</div>
                            </div>
                        @else
                            <div class="cd-msg-bubble-bot">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge" style="background: rgba(243, 112, 33, 0.1); color: #f37021; font-size: 10px; padding: 2px 6px;">AI Assistant</span>
                                    <span class="text-muted" style="font-size: 10px;">{{ $msg['created_at'] }}</span>
                                </div>

                                <div style="white-space: pre-line;">
                                    {!! nl2br(e($msg['content'])) !!}
                                </div>

                                <!-- Rich Sources -->
                                @if(!empty($msg['sources']))
                                    <div class="mt-2 pt-2 border-top">
                                        <div style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px;">Liên kết liên quan:</div>
                                        <div class="d-flex flex-wrap">
                                            @foreach($msg['sources'] as $src)
                                                <a href="{{ $src['url'] ?? '#' }}" target="_blank" class="cd-msg-source-tag">
                                                    <i class="fa fa-link"></i> {{ $src['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- AI Suggestions Chips -->
                                @if(!empty($msg['suggestions']))
                                    <div class="mt-2 pt-1">
                                        <div class="d-flex flex-wrap">
                                            @foreach($msg['suggestions'] as $sug)
                                                <button type="button" class="cd-msg-suggestion-chip" wire:click="sendQuickPrompt('{{ addslashes($sug) }}')">
                                                    <i class="fa fa-lightbulb-o text-warning"></i> {{ $sug }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <!-- Loading Indicator -->
                    <div wire:loading wire:target="sendAiMessage, sendQuickPrompt" class="cd-msg-bubble-bot align-self-start">
                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 12.5px;">
                            <i class="fa fa-circle-o-notch fa-spin text-primary"></i> Trợ lý AI đang soạn câu trả lời...
                        </div>
                    </div>

                    @if($aiError)
                        <div class="alert alert-danger p-2 mb-0" style="font-size: 11.5px; border-radius: 8px;">
                            <i class="fa fa-exclamation-triangle me-1"></i> {{ $aiError }}
                        </div>
                    @endif

                    <!-- Quick Starter Prompts if chat is short -->
                    @if(count($chatMessages) <= 1)
                        <div class="mt-2">
                            <div style="font-size: 11.5px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">
                                💡 Gợi ý câu hỏi nhanh:
                            </div>
                            <div class="d-flex flex-column gap-1">
                                @foreach($quickPrompts as $prompt)
                                    <button type="button" class="btn btn-sm text-start p-2 rounded-3 bg-white border" style="font-size: 12px; color: #334155;" wire:click="sendQuickPrompt('{{ addslashes($prompt) }}')">
                                        <i class="fa fa-bolt text-warning me-1"></i> {{ $prompt }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Chat Input Bar -->
                <div class="cd-chat-footer">
                    <input 
                        type="text" 
                        class="cd-chat-input" 
                        placeholder="Nhập câu hỏi cho Trợ lý AI..." 
                        wire:model="aiInput"
                        wire:keydown.enter="sendAiMessage"
                    >
                    <button 
                        type="button" 
                        class="cd-chat-send-btn" 
                        wire:click="sendAiMessage"
                        wire:loading.attr="disabled"
                        wire:target="sendAiMessage, sendQuickPrompt"
                        title="Gửi câu hỏi"
                    >
                        <span wire:loading.remove wire:target="sendAiMessage, sendQuickPrompt">
                            <i class="fa fa-paper-plane"></i>
                        </span>
                        <span wire:loading wire:target="sendAiMessage, sendQuickPrompt">
                            <i class="fa fa-circle-o-notch fa-spin"></i>
                        </span>
                    </button>
                </div>

            <!-- MODE 2: QUICK SHORTCUTS -->
            @else
                <div class="cd-assistant-body">
                    @if($currentShortcutView === 'menu')
                        <div class="cd-msg-bubble-bot mb-1">
                            Chọn nhanh các tiện ích tài khoản bên dưới để thao tác ngay mà không cần rời trang:
                        </div>

                        <!-- 4 Main Shortcuts -->
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="cd-menu-btn" wire:click="setShortcutView('manage_cv')">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #e0f2fe; color: #0284c7;">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </div>
                                    <div>
                                        <div>Quản lý CV đã tải lên</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            {{ $attachments->count() }} file CV đính kèm
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </button>

                            <button type="button" class="cd-menu-btn" wire:click="setShortcutView('messages')">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #ede9fe; color: #7c3aed;">
                                        <i class="fa fa-comments-o"></i>
                                    </div>
                                    <div>
                                        <div>Hộp thư tuyển dụng</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            {{ $chats->count() }} cuộc hội thoại
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </button>



                            <button type="button" class="cd-menu-btn" wire:click="setShortcutView('security')">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cd-menu-icon" style="background: #f1f5f9; color: #475569;">
                                        <i class="fa fa-shield"></i>
                                    </div>
                                    <div>
                                        <div>Bảo mật & Mật khẩu</div>
                                        <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                                            Đổi mật khẩu & trạng thái
                                        </div>
                                    </div>
                                </div>
                                <i class="fa fa-angle-right text-muted"></i>
                            </button>
                        </div>

                    @elseif($currentShortcutView === 'manage_cv')
                        <!-- In-Modal CV Manager -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <button type="button" class="cd-back-btn" wire:click="backToShortcutMenu">
                                <i class="fa fa-arrow-left"></i> Quay lại Menu
                            </button>
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">📄 Quản lý CV</span>
                        </div>

                        <!-- Upload form -->
                        <div class="p-3 bg-white border rounded-3">
                            <div style="font-size: 12.5px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                                <i class="fa fa-cloud-upload text-primary me-1"></i> Tải lên CV mới
                            </div>
                            
                            <div class="mb-2">
                                <input 
                                    type="file" 
                                    wire:model="newCvUpload" 
                                    class="form-control form-control-sm" 
                                    accept=".pdf,.doc,.docx"
                                    style="font-size: 12px;"
                                >
                                @error('newCvUpload')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <input 
                                    type="text" 
                                    wire:model="newCvTitle" 
                                    placeholder="Tên gợi nhớ (VD: CV Senior Backend 2026)" 
                                    class="form-control form-control-sm" 
                                    style="font-size: 12px;"
                                >
                            </div>

                            <button 
                                type="button" 
                                wire:click="uploadCv" 
                                class="btn btn-sm w-100" 
                                style="background: #f37021; color: white; font-weight: 700; font-size: 12px; border-radius: 8px;"
                                wire:loading.attr="disabled"
                                wire:target="uploadCv, newCvUpload"
                            >
                                <span wire:loading.remove wire:target="uploadCv"><i class="fa fa-upload me-1"></i> Lưu CV vào tài khoản</span>
                                <span wire:loading wire:target="uploadCv"><i class="fa fa-circle-o-notch fa-spin"></i> Đang tải lên...</span>
                            </button>
                        </div>

                        <!-- Uploaded list -->
                        <div class="d-flex flex-column gap-2">
                            <div style="font-size: 12px; font-weight: 700; color: #64748b;">Danh sách file CV:</div>
                            @forelse($attachments as $att)
                                <div class="p-2 bg-white border rounded-3 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <div style="width: 30px; height: 30px; border-radius: 6px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </div>
                                        <div class="text-truncate">
                                            <div style="font-size: 12.5px; font-weight: 700; color: #0f172a;" class="text-truncate">
                                                {{ $att->original_filename }}
                                            </div>
                                            <div style="font-size: 11px; color: #94a3b8;">
                                                {{ $att->created_at?->format('d/m/Y') }} • {{ round(($att->size_bytes ?? 0) / 1024) }} KB
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        <a 
                                            href="{{ Storage::disk('public')->url($att->path) }}" 
                                            target="_blank" 
                                            class="btn btn-sm btn-light p-1 px-2" 
                                            style="font-size: 11px; border: 1px solid #e2e8f0;"
                                            title="Xem / Tải file"
                                        >
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <button 
                                            type="button" 
                                            wire:click="deleteCv({{ $att->id }})" 
                                            wire:confirm="Bạn có chắc chắn muốn xóa file CV này?" 
                                            class="btn btn-sm btn-light text-danger p-1 px-2" 
                                            style="font-size: 11px; border: 1px solid #e2e8f0;"
                                            title="Xóa CV"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 bg-white border rounded-3">
                                    <p class="text-muted m-0" style="font-size: 12px;">Bạn chưa tải lên file CV nào.</p>
                                </div>
                            @endforelse
                        </div>

                    @elseif($currentShortcutView === 'messages')
                        <!-- In-Modal Messages List -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <button type="button" class="cd-back-btn" wire:click="backToShortcutMenu">
                                <i class="fa fa-arrow-left"></i> Quay lại Menu
                            </button>
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">💬 Hộp thư tuyển dụng</span>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            @forelse($chats as $chat)
                                @php
                                    $lastMessage = $chat->messages->first();
                                @endphp
                                <div 
                                    class="p-3 bg-white border rounded-3 cursor-pointer" 
                                    wire:click="openChatDetail({{ $chat->id }})"
                                    style="cursor: pointer; transition: all 0.2s ease;"
                                >
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">
                                            {{ $chat->employer?->name ?? ($chat->job?->title ?? 'Thông báo tuyển dụng') }}
                                        </div>
                                        <span style="font-size: 10.5px; color: #94a3b8;">
                                            {{ $chat->updated_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-muted text-truncate m-0" style="font-size: 12px;">
                                        {{ $lastMessage?->content ?? 'Chưa có nội dung trao đổi.' }}
                                    </p>
                                </div>
                            @empty
                                <div class="text-center py-4 bg-white border rounded-3">
                                    <div style="font-size: 28px; color: #cbd5e1; margin-bottom: 6px;"><i class="fa fa-inbox"></i></div>
                                    <p class="text-muted m-0" style="font-size: 12.5px;">Chưa có tin nhắn hoặc lời mời nào.</p>
                                </div>
                            @endforelse
                        </div>

                    @elseif($currentShortcutView === 'chat_detail')
                        <!-- Chat Thread View -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <button type="button" class="cd-back-btn" wire:click="setShortcutView('messages')">
                                <i class="fa fa-arrow-left"></i> Danh sách tin nhắn
                            </button>
                            <span style="font-size: 12.5px; font-weight: 700; color: #0f172a;">Chi tiết hội thoại</span>
                        </div>

                        @if($activeChat)
                            <div class="p-3 bg-white border rounded-3">
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                                    {{ $activeChat->employer?->name ?? 'Hệ thống tuyển dụng' }}
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; margin-bottom: 12px;">
                                    {{ $activeChat->job?->title ?? 'Trao đổi cơ hội nghề nghiệp' }}
                                </div>

                                <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                                    @foreach($activeChat->messages->reverse() as $msg)
                                        <div class="p-2 rounded-3" style="background: {{ $msg->sender_type === 'candidate' ? '#eff6ff' : '#f8fafc' }}; border: 1px solid #e2e8f0; font-size: 12.5px;">
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 10.5px; color: #94a3b8;">
                                                <strong>{{ $msg->sender_type === 'candidate' ? 'Bạn' : ($activeChat->employer?->name ?? 'Nhà tuyển dụng') }}</strong>
                                                <span>{{ $msg->created_at?->format('H:i d/m') }}</span>
                                            </div>
                                            <div style="color: #1e293b; white-space: pre-line;">{{ $msg->content }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    @elseif($currentShortcutView === 'earnings')
                        <!-- In-Modal Earnings -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <button type="button" class="cd-back-btn" wire:click="backToShortcutMenu">
                                <i class="fa fa-arrow-left"></i> Quay lại Menu
                            </button>
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">🏆 Thu nhập & Thưởng</span>
                        </div>

                        <div class="p-3 bg-white border rounded-3 text-center">
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Số dư khả dụng hiện tại</div>
                            <h3 class="fw-bold mb-1" style="color: #f37021; font-size: 24px;">0 VNĐ</h3>
                            <p style="font-size: 11.5px; color: #94a3b8; margin: 0;">Chương trình Referral & Điểm thưởng ứng viên FPT</p>
                        </div>

                        <div class="p-3 bg-white border rounded-3">
                            <div style="font-size: 12.5px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                                <i class="fa fa-gift text-warning me-1"></i> Cơ hội nhận thưởng
                            </div>
                            <ul style="padding-left: 18px; margin: 0; font-size: 12px; color: #475569; line-height: 1.6;">
                                <li>Giới thiệu bạn bè ứng tuyển các vị trí Hot.</li>
                                <li>Nhận thưởng trực tiếp khi ứng viên được onboard thành công.</li>
                                <li>Tích điểm đổi quà công nghệ và vouchers.</li>
                            </ul>
                        </div>

                    @elseif($currentShortcutView === 'security')
                        <!-- In-Modal Security Form -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <button type="button" class="cd-back-btn" wire:click="backToShortcutMenu">
                                <i class="fa fa-arrow-left"></i> Quay lại Menu
                            </button>
                            <span style="font-size: 13px; font-weight: 700; color: #0f172a;">🛡️ Bảo mật & Mật khẩu</span>
                        </div>

                        <div class="p-3 bg-white border rounded-3">
                            <div style="font-size: 12.5px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                                <i class="fa fa-lock text-primary me-1"></i> Đổi mật khẩu
                            </div>

                            @if($passwordStatus)
                                <div class="alert alert-success p-2 mb-2" style="font-size: 11.5px; border-radius: 8px;">
                                    {{ $passwordStatus }}
                                </div>
                            @endif

                            <div class="mb-2">
                                <input 
                                    type="password" 
                                    wire:model="current_password" 
                                    placeholder="Mật khẩu hiện tại" 
                                    class="form-control form-control-sm" 
                                    style="font-size: 12px;"
                                >
                                @error('current_password')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <input 
                                    type="password" 
                                    wire:model="password" 
                                    placeholder="Mật khẩu mới (tối thiểu 8 ký tự)" 
                                    class="form-control form-control-sm" 
                                    style="font-size: 12px;"
                                >
                                @error('password')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <input 
                                    type="password" 
                                    wire:model="password_confirmation" 
                                    placeholder="Xác nhận mật khẩu mới" 
                                    class="form-control form-control-sm" 
                                    style="font-size: 12px;"
                                >
                            </div>

                            <button 
                                type="button" 
                                wire:click="updatePassword" 
                                class="btn btn-sm w-100" 
                                style="background: #0f172a; color: white; font-weight: 700; font-size: 12px; border-radius: 8px;"
                                wire:loading.attr="disabled"
                                wire:target="updatePassword"
                            >
                                <span wire:loading.remove wire:target="updatePassword">Cập nhật mật khẩu</span>
                                <span wire:loading wire:target="updatePassword"><i class="fa fa-circle-o-notch fa-spin"></i> Đang lưu...</span>
                            </button>
                        </div>

                        <div class="p-2 bg-white border rounded-3" style="font-size: 12px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">Email tài khoản:</span>
                                <strong class="text-dark">{{ Auth::user()?->email }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Trạng thái bảo vệ:</span>
                                <span class="badge bg-success" style="font-size: 10px;">Hoạt động</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
