<div class="{{ $enabled ? 'ai-chatbox' : '' }}" @if ($enabled) data-ai-chatbox @endif>
    @if ($enabled)
        @if ($isOpen)
            <section class="ai-chatbox__panel" aria-label="Trợ lý AI">
                <span class="ai-chatbox__panel-accent" aria-hidden="true"></span>
                <header class="ai-chatbox__header">
                    <div class="ai-chatbox__identity">
                        <span class="ai-chatbox__avatar" aria-hidden="true">
                            <span class="ai-chatbox__avatar-ring"></span>
                            <svg viewBox="0 0 24 24" fill="none"><path d="M8 8.5h8a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3Z" stroke="currentColor" stroke-width="1.7"/><path d="M12 5v3.5M9.2 13h.1m5.4 0h.1M9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="4" r="1.2" fill="currentColor"/></svg>
                            <i class="ai-chatbox__spark ai-chatbox__spark--one"></i>
                            <i class="ai-chatbox__spark ai-chatbox__spark--two"></i>
                        </span>
                        <div>
                            <strong>{{ $assistantTitle }}</strong>
                            <span><i></i> {{ $assistantSubtitle }}</span>
                        </div>
                    </div>
                    <div class="ai-chatbox__actions">
                        <button type="button" wire:click="newConversation" title="Cuộc trò chuyện mới" aria-label="Cuộc trò chuyện mới">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                        <button type="button" wire:click="toggle" title="Đóng" aria-label="Đóng chatbox">
                            <svg viewBox="0 0 24 24" fill="none"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                </header>

                <div class="ai-chatbox__messages" data-ai-chat-messages>
                    @if (empty($messages))
                        <div class="ai-chatbox__welcome">
                            <span class="ai-chatbox__welcome-icon">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M8 10h8M8 14h5M5 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7l-4 3v-3H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                            </span>
                            <h3>Xin chào {{ auth()->user()->name }}!</h3>
                            <p>{{ $assistantDescription }}</p>
                            <div class="ai-chatbox__quick-prompts">
                                @foreach ($quickPrompts as $prompt)
                                    <button type="button" wire:click="useSuggestion({{ \Illuminate\Support\Js::from($prompt) }})">
                                        <span aria-hidden="true">→</span>
                                        {{ $prompt }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @foreach ($messages as $chatMessage)
                            <article wire:key="ai-message-{{ $chatMessage['id'] }}" class="ai-chatbox__message ai-chatbox__message--{{ $chatMessage['role'] }} {{ $chatMessage['status'] === 'failed' ? 'is-failed' : '' }}">
                                @if ($chatMessage['role'] === 'assistant')
                                    <span class="ai-chatbox__bot-mark">AI</span>
                                @endif
                                <div class="ai-chatbox__bubble">
                                    <div class="ai-chatbox__content">{!! nl2br(e($chatMessage['content'])) !!}</div>

                                    @if (! empty($chatMessage['sources']))
                                        <div class="ai-chatbox__sources">
                                            <strong>Nguồn tham chiếu</strong>
                                            @foreach ($chatMessage['sources'] as $source)
                                                @if (! empty($source['url']))
                                                    <a href="{{ $source['url'] }}">{{ $source['label'] }} <span>↗</span></a>
                                                @else
                                                    <span>{{ $source['label'] }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="ai-chatbox__message-meta">
                                        <time>{{ $chatMessage['time'] }}</time>
                                        @if ($chatMessage['role'] === 'assistant' && $chatMessage['status'] === 'completed')
                                            <span>{{ $chatMessage['provider'] === 'local' ? 'Dữ liệu hệ thống' : 'Gemini AI' }}</span>
                                            <div class="ai-chatbox__feedback" aria-label="Đánh giá câu trả lời">
                                                <button type="button" class="{{ $chatMessage['feedback'] === 'helpful' ? 'is-active' : '' }}" wire:click="rateMessage({{ $chatMessage['id'] }}, 'helpful')" title="Hữu ích" aria-label="Câu trả lời hữu ích">
                                                    <svg viewBox="0 0 24 24" fill="none"><path d="M7 10v10H4V10h3Zm0 8h10.2a2 2 0 0 0 1.95-1.56l1.2-5.25A1.8 1.8 0 0 0 18.6 9H14l.7-3.2A2.3 2.3 0 0 0 12.45 3L7 10v8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                                </button>
                                                <button type="button" class="{{ $chatMessage['feedback'] === 'not_helpful' ? 'is-active' : '' }}" wire:click="rateMessage({{ $chatMessage['id'] }}, 'not_helpful')" title="Chưa hữu ích" aria-label="Câu trả lời chưa hữu ích">
                                                    <svg viewBox="0 0 24 24" fill="none"><path d="M7 14V4H4v10h3Zm0-8h10.2a2 2 0 0 1 1.95 1.56l1.2 5.25A1.8 1.8 0 0 1 18.6 15H14l.7 3.2A2.3 2.3 0 0 1 12.45 21L7 14V6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>

                            @if ($loop->last && $chatMessage['role'] === 'assistant' && ! empty($chatMessage['suggestions']))
                                <div class="ai-chatbox__suggestions">
                                    @foreach ($chatMessage['suggestions'] as $suggestion)
                                        <button type="button" wire:click="useSuggestion({{ \Illuminate\Support\Js::from($suggestion) }})">
                                            {{ $suggestion }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif

                    <div wire:loading.flex wire:target="sendMessage" class="ai-chatbox__typing">
                        <span></span><span></span><span></span> AI đang tổng hợp dữ liệu
                    </div>
                </div>

                <footer class="ai-chatbox__footer">
                    @if ($error)
                        <div class="ai-chatbox__error" role="alert">{{ $error }}</div>
                    @endif
                    @error('message')
                        <div class="ai-chatbox__error" role="alert">{{ $message }}</div>
                    @enderror

                    <form wire:submit="sendMessage" class="ai-chatbox__form">
                        <textarea
                            wire:model="message"
                            rows="1"
                            maxlength="1000"
                            placeholder="{{ $audience === 'employer' ? 'Hỏi về pipeline, ứng viên…' : 'Hỏi về việc làm, hồ sơ, CV…' }}"
                            aria-label="Nhập câu hỏi cho trợ lý AI"
                        ></textarea>
                        <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage" aria-label="Gửi câu hỏi">
                            <svg viewBox="0 0 24 24" fill="none"><path d="m21 3-7.5 18-4-7-6.5-4 18-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9.5 14 21 3" stroke="currentColor" stroke-width="1.8"/></svg>
                        </button>
                    </form>
                    <p>AI có thể trả lời chưa chính xác. Hãy kiểm tra nguồn trước khi quyết định.</p>
                </footer>
            </section>
        @endif

        <button type="button" wire:click="toggle" wire:loading.class="is-thinking" class="ai-chatbox__launcher {{ $isOpen ? 'is-open' : '' }}" aria-label="{{ $isOpen ? 'Đóng trợ lý AI' : 'Mở trợ lý AI' }}">
            @if ($isOpen)
                <svg viewBox="0 0 24 24" fill="none"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            @else
                <span class="ai-chatbox__launcher-orb" aria-hidden="true">
                    <span class="ai-chatbox__launcher-ring"></span>
                    <svg viewBox="0 0 24 24" fill="none"><path d="M8 8.5h8a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3Z" stroke="currentColor" stroke-width="1.7"/><path d="M12 5v3.5M9.2 13h.1m5.4 0h.1M9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="4" r="1.2" fill="currentColor"/></svg>
                    <i></i><i></i>
                </span>
                <span class="ai-chatbox__launcher-label"><small>Trợ lý đang trực tuyến</small>Hỏi AI</span>
            @endif
        </button>
        <style>
        .ai-chatbox { --ai-primary:#f37021; --ai-primary-dark:#d9570b; --ai-ink:#172033; --ai-muted:#657186; font-family:"Plus Jakarta Sans","Inter",sans-serif; position:relative; z-index:1200 }
        .ai-chatbox *, .ai-chatbox *::before, .ai-chatbox *::after { box-sizing:border-box }
        .ai-chatbox__launcher { align-items:center; background:linear-gradient(135deg,#d95d14,var(--ai-primary)); border:1px solid rgba(255,255,255,.35); border-radius:999px; bottom:24px; box-shadow:0 14px 34px rgba(137,58,12,.28),inset 0 1px 0 rgba(255,255,255,.28); color:#fff; cursor:pointer; display:flex; font-size:14px; font-weight:800; gap:10px; min-height:60px; overflow:hidden; padding:5px 18px 5px 6px; position:fixed; right:24px; transition:transform .28s cubic-bezier(.16,1,.3,1),box-shadow .28s ease; z-index:1202 }
        .ai-chatbox__launcher:hover { box-shadow:0 18px 40px rgba(210,80,8,.38); transform:translateY(-2px) }
        .ai-chatbox__launcher:active { transform:translateY(0) scale(.97) }
        .ai-chatbox__launcher.is-open { border-radius:50%; height:50px; justify-content:center; min-height:50px; padding:0; width:50px }
        .ai-chatbox__launcher svg { height:23px; width:23px }
        .ai-chatbox__launcher-orb { align-items:center; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); border-radius:50%; display:flex; flex:0 0 48px; height:48px; justify-content:center; position:relative; width:48px }
        .ai-chatbox__launcher-orb svg { animation:aiBotFloat 2.8s ease-in-out infinite; position:relative; z-index:2 }
        .ai-chatbox__launcher-ring { animation:aiRingBreath 2.2s ease-out infinite; border:1px solid rgba(255,255,255,.65); border-radius:50%; inset:5px; position:absolute }
        .ai-chatbox__launcher-orb>i { animation:aiSpark 1.8s ease-in-out infinite; background:#fff5df; border-radius:50%; height:4px; position:absolute; right:3px; top:5px; width:4px }
        .ai-chatbox__launcher-orb>i:last-child { animation-delay:.8s; bottom:6px; height:3px; left:4px; right:auto; top:auto; width:3px }
        .ai-chatbox__launcher-label { display:flex; flex-direction:column; line-height:1.15; text-align:left }
        .ai-chatbox__launcher-label small { color:#ffe3d2; font-size:9px; font-weight:600; letter-spacing:.02em; margin-bottom:3px }
        .ai-chatbox__launcher.is-thinking .ai-chatbox__launcher-ring { animation-duration:.7s }.ai-chatbox__launcher.is-thinking .ai-chatbox__launcher-orb svg { animation-duration:.8s }
        .ai-chatbox__panel { animation:aiChatEnter .28s cubic-bezier(.16,1,.3,1); background:#fff; border:1px solid #e5e9f0; border-radius:22px; bottom:94px; box-shadow:0 24px 70px rgba(24,32,51,.2); display:flex; flex-direction:column; height:min(660px,calc(100vh - 126px)); overflow:hidden; position:fixed; right:24px; width:min(410px,calc(100vw - 32px)); z-index:1201 }
        .ai-chatbox__panel-accent { animation:aiAccentTravel 3.8s ease-in-out infinite; background:linear-gradient(90deg,transparent,var(--ai-primary),transparent); height:2px; left:0; opacity:.65; position:absolute; top:0; transform:translateX(-65%); width:65%; z-index:2 }
        .ai-chatbox__header { align-items:center; background:linear-gradient(135deg,#fff8f3,#fff); border-bottom:1px solid #f0e7e1; display:flex; justify-content:space-between; min-height:76px; padding:14px 16px }
        .ai-chatbox__identity { align-items:center; display:flex; gap:11px; min-width:0 }
        .ai-chatbox__identity strong { color:var(--ai-ink); display:block; font-size:14px; line-height:1.4 }
        .ai-chatbox__identity span:not(.ai-chatbox__avatar) { align-items:center; color:var(--ai-muted); display:flex; font-size:11px; gap:6px; margin-top:3px }
        .ai-chatbox__identity span i { background:#21a366; border-radius:50%; height:7px; width:7px }
        .ai-chatbox__avatar { align-items:center; background:linear-gradient(135deg,var(--ai-primary),#ff9d60); border-radius:13px; color:#fff; display:flex; flex:0 0 42px; height:42px; justify-content:center; position:relative; width:42px }
        .ai-chatbox__avatar svg { animation:aiBotFloat 3s ease-in-out infinite; height:25px; position:relative; width:25px; z-index:2 }
        .ai-chatbox__avatar-ring { animation:aiRingBreath 2.4s ease-out infinite; border:1px solid rgba(255,255,255,.65); border-radius:10px; inset:4px; position:absolute }
        .ai-chatbox__spark { animation:aiSpark 2s ease-in-out infinite; background:#fff4d7; border-radius:50%; height:3px; position:absolute; width:3px; z-index:3 }.ai-chatbox__spark--one { right:3px; top:4px }.ai-chatbox__spark--two { animation-delay:.9s; bottom:4px; left:4px }
        .ai-chatbox__actions { display:flex; gap:5px }
        .ai-chatbox__actions button { align-items:center; background:transparent; border:0; border-radius:9px; color:#6b7280; cursor:pointer; display:flex; height:34px; justify-content:center; padding:0; width:34px }
        .ai-chatbox__actions button:hover { background:#f3f4f6; color:var(--ai-ink) }
        .ai-chatbox__actions svg { height:19px; width:19px }
        .ai-chatbox__messages { background:#f7f8fb; flex:1; overflow-y:auto; padding:18px 16px; scroll-behavior:smooth }
        .ai-chatbox__welcome { color:var(--ai-muted); padding:18px 8px; text-align:center }
        .ai-chatbox__welcome-icon { align-items:center; background:#fff1e8; border-radius:18px; color:var(--ai-primary); display:inline-flex; height:58px; justify-content:center; width:58px }
        .ai-chatbox__welcome-icon svg { height:30px; width:30px }
        .ai-chatbox__welcome h3 { color:var(--ai-ink); font-size:18px; margin:14px 0 7px }
        .ai-chatbox__welcome p { font-size:13px; line-height:1.65; margin:0 auto 17px; max-width:330px }
        .ai-chatbox__quick-prompts { display:grid; gap:8px }
        .ai-chatbox__quick-prompts button,.ai-chatbox__suggestions button { align-items:center; background:#fff; border:1px solid #e2e7ee; border-radius:12px; color:#39445a; cursor:pointer; display:flex; font-size:12px; gap:8px; line-height:1.45; padding:10px 12px; text-align:left; transition:transform .2s ease,border-color .2s ease,color .2s ease }
        .ai-chatbox__quick-prompts button:hover,.ai-chatbox__suggestions button:hover { border-color:#f6a677; color:#b84b0a }
        .ai-chatbox__quick-prompts button:hover { transform:translateX(3px) }.ai-chatbox__quick-prompts button:active,.ai-chatbox__suggestions button:active { transform:scale(.98) }
        .ai-chatbox__quick-prompts button span { color:var(--ai-primary); font-size:15px }
        .ai-chatbox__message { align-items:flex-end; display:flex; gap:7px; margin:0 0 14px; max-width:92% }
        .ai-chatbox__message--user { justify-content:flex-end; margin-left:auto }
        .ai-chatbox__bot-mark { align-items:center; background:#fff1e8; border-radius:9px; color:var(--ai-primary-dark); display:flex; flex:0 0 28px; font-size:9px; font-weight:900; height:28px; justify-content:center; width:28px }
        .ai-chatbox__bubble { min-width:0 }
        .ai-chatbox__content { background:#fff; border:1px solid #e7eaf0; border-radius:15px 15px 15px 4px; color:#283247; font-size:13px; line-height:1.65; padding:10px 12px; word-break:break-word }
        .ai-chatbox__message--user .ai-chatbox__content { background:linear-gradient(135deg,var(--ai-primary),#ef7e38); border:0; border-radius:15px 15px 4px 15px; color:#fff }
        .ai-chatbox__message.is-failed .ai-chatbox__content { background:#fff7f7; border-color:#f2caca; color:#8d3131 }
        .ai-chatbox__message-meta { align-items:center; color:#929aaa; display:flex; font-size:9px; gap:7px; margin-top:4px; min-height:18px }.ai-chatbox__message-meta time { font-size:9px }.ai-chatbox__message--user .ai-chatbox__message-meta { justify-content:flex-end }
        .ai-chatbox__feedback { display:flex; gap:2px; margin-left:auto }.ai-chatbox__feedback button { align-items:center; background:transparent; border:0; border-radius:6px; color:#9aa2af; cursor:pointer; display:flex; height:22px; justify-content:center; padding:0; width:22px }.ai-chatbox__feedback button:hover,.ai-chatbox__feedback button.is-active { background:#fff0e7; color:var(--ai-primary-dark) }.ai-chatbox__feedback svg { height:13px; width:13px }
        .ai-chatbox__sources { border-top:1px solid #edf0f4; display:flex; flex-direction:column; gap:4px; margin-top:8px; padding-top:8px }
        .ai-chatbox__sources strong { color:#7b8494; font-size:9px; letter-spacing:.06em; text-transform:uppercase }
        .ai-chatbox__sources a,.ai-chatbox__sources>span { color:#b84b0a; font-size:10px; line-height:1.4; text-decoration:none }
        .ai-chatbox__suggestions { display:flex; flex-wrap:wrap; gap:6px; margin:0 0 16px 35px }
        .ai-chatbox__suggestions button { border-radius:999px; padding:7px 10px }
        .ai-chatbox__typing { align-items:center; color:#758094; font-size:11px; gap:4px; margin:5px 0 12px 36px }
        .ai-chatbox__typing span { animation:aiChatPulse 1s infinite; background:#f37021; border-radius:50%; height:5px; width:5px }
        .ai-chatbox__typing span:nth-child(2) { animation-delay:.15s }.ai-chatbox__typing span:nth-child(3) { animation-delay:.3s;margin-right:4px }
        .ai-chatbox__footer { background:#fff; border-top:1px solid #e9edf2; padding:12px 14px 10px }
        .ai-chatbox__form { align-items:flex-end; background:#f7f8fa; border:1px solid #dfe4eb; border-radius:15px; display:flex; gap:8px; padding:7px 7px 7px 12px }
        .ai-chatbox__form:focus-within { border-color:#f3a06f; box-shadow:0 0 0 3px rgba(243,112,33,.08) }
        .ai-chatbox__form textarea { background:transparent; border:0; color:var(--ai-ink); flex:1; font:inherit; font-size:13px; line-height:1.5; max-height:96px; min-height:38px; outline:0; padding:9px 0; resize:none }
        .ai-chatbox__form button { align-items:center; background:var(--ai-primary); border:0; border-radius:11px; color:#fff; cursor:pointer; display:flex; flex:0 0 40px; height:40px; justify-content:center; width:40px }
        .ai-chatbox__form button:disabled { cursor:wait; opacity:.6 }
        .ai-chatbox__form button svg { height:20px; width:20px }
        .ai-chatbox__footer>p { color:#929aaa; font-size:9px; margin:7px 0 0; text-align:center }
        .ai-chatbox__error { background:#fff1f1; border-radius:9px; color:#a33a3a; font-size:11px; margin-bottom:8px; padding:8px 10px }
        @keyframes aiChatEnter { from { opacity:0; transform:translateY(12px) scale(.98) } to { opacity:1; transform:none } }
        @keyframes aiChatPulse { 0%,80%,100% { opacity:.3; transform:scale(.8) } 40% { opacity:1; transform:scale(1) } }
        @keyframes aiBotFloat { 0%,100% { transform:translateY(0) rotate(0) } 50% { transform:translateY(-2px) rotate(2deg) } }
        @keyframes aiRingBreath { 0% { opacity:.35; transform:scale(.82) } 55% { opacity:.85 } 100% { opacity:0; transform:scale(1.18) } }
        @keyframes aiSpark { 0%,100% { opacity:.2; transform:scale(.7) } 50% { opacity:1; transform:scale(1.5) } }
        @keyframes aiAccentTravel { 0%,100% { opacity:0; transform:translateX(-65%) } 25%,70% { opacity:.7 } 85% { opacity:0; transform:translateX(155%) } }
        @media (max-width:575px) { .ai-chatbox__panel { border-radius:18px; bottom:78px; height:calc(100vh - 94px); right:8px; width:calc(100vw - 16px) }.ai-chatbox__launcher { bottom:16px; right:16px }.ai-chatbox__launcher:not(.is-open) .ai-chatbox__launcher-label { display:none }.ai-chatbox__launcher:not(.is-open) { border-radius:50%; height:58px; justify-content:center; padding:4px; width:58px }.ai-chatbox__launcher-orb { flex-basis:48px } }
        @media (prefers-reduced-motion:reduce) { .ai-chatbox__panel,.ai-chatbox__launcher,.ai-chatbox__typing span,.ai-chatbox__launcher-orb svg,.ai-chatbox__launcher-ring,.ai-chatbox__launcher-orb>i,.ai-chatbox__avatar svg,.ai-chatbox__avatar-ring,.ai-chatbox__spark,.ai-chatbox__panel-accent { animation:none; transition:none } }
        </style>

        <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('ai-chat-updated', () => window.setTimeout(() => {
                const messages = document.querySelector('[data-ai-chat-messages]');
                if (messages) messages.scrollTop = messages.scrollHeight;
            }, 50));
        });
        </script>
    @endif
</div>
