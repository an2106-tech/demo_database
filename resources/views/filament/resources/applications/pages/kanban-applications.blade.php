<x-filament-panels::page>
    @php
        $toneClasses = [
            'gray' => 'is-gray',
            'info' => 'is-info',
            'warning' => 'is-warning',
            'primary' => 'is-primary',
            'success' => 'is-success',
            'danger' => 'is-danger',
        ];
    @endphp

    <style>
        .recruitment-kanban {
            --kanban-border: rgb(229 231 235);
            --kanban-card: #ffffff;
            --kanban-muted: rgb(100 116 139);
            --kanban-soft: rgb(248 250 252);
            --kanban-text: rgb(15 23 42);
            --kanban-orange: rgb(234 88 12);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .recruitment-kanban [x-cloak] {
            display: none !important;
        }

        .dark .recruitment-kanban {
            --kanban-border: rgb(39 39 42);
            --kanban-card: rgb(24 24 27);
            --kanban-muted: rgb(161 161 170);
            --kanban-soft: rgb(18 18 22);
            --kanban-text: rgb(250 250 250);
        }

        .kanban-panel {
            border: 1px solid var(--kanban-border);
            border-radius: 16px;
            background: var(--kanban-card);
            box-shadow: 0 10px 28px rgb(15 23 42 / 0.05);
        }

        .kanban-controls {
            display: grid;
            gap: 12px;
            padding: 14px;
        }

        .kanban-control-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
        }

        .kanban-summary {
            min-width: 0;
        }

        .kanban-summary__title {
            margin: 0;
            color: var(--kanban-text);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.25;
        }

        .kanban-summary__meta {
            margin: 4px 0 0;
            color: var(--kanban-muted);
            font-size: 13px;
            line-height: 1.35;
        }

        .kanban-search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: min(420px, 42vw);
        }

        .kanban-search {
            position: relative;
            min-width: 0;
            flex: 1;
        }

        .kanban-search__icon {
            position: absolute;
            left: 12px;
            top: 50%;
            width: 18px;
            height: 18px;
            color: var(--kanban-muted);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .kanban-search__input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            font-size: 13.5px;
            outline: none;
            padding: 0 38px 0 40px;
            transition: border-color 140ms ease, box-shadow 140ms ease, background 140ms ease;
        }

        .kanban-search__input:focus {
            border-color: rgb(251 146 60);
            background: var(--kanban-card);
            box-shadow: 0 0 0 3px rgb(251 146 60 / 0.14);
        }

        .kanban-search__clear,
        .kanban-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            cursor: pointer;
        }

        .kanban-search__clear {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: transparent;
            color: var(--kanban-muted);
            transform: translateY(-50%);
        }

        .kanban-search__clear:hover {
            background: rgb(241 245 249);
            color: var(--kanban-orange);
        }

        .kanban-reset {
            height: 42px;
            border-radius: 13px;
            background: rgb(255 247 237);
            color: rgb(194 65 12);
            font-size: 13px;
            font-weight: 750;
            padding: 0 14px;
            white-space: nowrap;
        }

        .kanban-reset:hover {
            background: rgb(254 215 170);
        }

        .kanban-work-queues {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
        }

        .kanban-work-queue {
            min-width: 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            align-items: center;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            cursor: pointer;
            padding: 10px 11px;
            text-align: left;
            transition: border-color 140ms ease, box-shadow 140ms ease, transform 140ms ease, background 140ms ease;
        }

        .kanban-work-queue:hover,
        .kanban-work-queue.is-active {
            border-color: rgb(251 146 60);
            background: rgb(255 247 237);
            box-shadow: 0 8px 18px rgb(234 88 12 / 0.08);
            transform: translateY(-1px);
        }

        .dark .kanban-work-queue:hover,
        .dark .kanban-work-queue.is-active {
            background: rgb(39 39 42);
        }

        .kanban-work-queue__label {
            display: block;
            overflow: hidden;
            color: inherit;
            font-size: 13.25px;
            font-weight: 800;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-work-queue__description {
            display: block;
            margin-top: 3px;
            overflow: hidden;
            color: var(--kanban-muted);
            font-size: 11.75px;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-work-queue__count {
            min-width: 30px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--kanban-card);
            color: rgb(194 65 12);
            font-size: 12.5px;
            font-weight: 850;
        }

        .kanban-stage-tabs {
            display: flex;
            gap: 8px;
            justify-content: center;
            overflow-x: auto;
            width: fit-content;
            max-width: 100%;
            margin-inline: auto;
            padding: 8px 10px;
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: var(--kanban-card);
            box-shadow: 0 8px 22px rgb(15 23 42 / 0.035);
        }

        .kanban-stage-tabs__button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex: none;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: rgb(71 85 105);
            cursor: pointer;
            font-size: 13.25px;
            font-weight: 700;
            line-height: 1;
            padding: 10px 14px;
            transition: background 140ms ease, color 140ms ease;
        }

        .kanban-stage-tabs__button:hover,
        .kanban-stage-tabs__button.is-active {
            background: rgb(255 247 237);
            color: rgb(194 65 12);
        }

        .kanban-stage-tabs__count {
            min-width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgb(253 186 116);
            background: rgb(255 247 237);
            color: rgb(194 65 12);
            font-size: 11.5px;
            font-weight: 800;
        }

        .kanban-board {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(260px, 300px);
            gap: 12px;
            min-height: clamp(520px, calc(100vh - 260px), 680px);
            overflow-x: auto;
            padding-bottom: 12px;
            scroll-snap-type: x proximity;
        }

        .kanban-column {
            min-height: 100%;
            overflow: hidden;
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: linear-gradient(180deg, var(--kanban-card), var(--kanban-soft));
            scroll-snap-align: start;
        }

        .kanban-column__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 12px;
            border-bottom: 1px solid var(--kanban-border);
            background: color-mix(in srgb, var(--kanban-card) 88%, transparent);
        }

        .kanban-column__title {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            color: var(--kanban-text);
            font-size: 13.5px;
            font-weight: 750;
            line-height: 1.3;
        }

        .kanban-column__dot {
            width: 8px;
            height: 8px;
            flex: none;
            border-radius: 999px;
            background: var(--kanban-orange);
            box-shadow: 0 0 0 4px rgb(234 88 12 / 0.12);
        }

        .kanban-column__count {
            min-width: 30px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgb(241 245 249);
            color: rgb(51 65 85);
            font-size: 13px;
            font-weight: 750;
        }

        .dark .kanban-column__count {
            background: rgb(39 39 42);
            color: rgb(228 228 231);
        }

        .kanban-column__body {
            display: flex;
            flex-direction: column;
            gap: 7px;
            max-height: calc(clamp(520px, calc(100vh - 260px), 680px) - 50px);
            overflow-y: auto;
            padding: 9px;
        }

        .kanban-card {
            display: block;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-card);
            padding: 9px 10px;
            box-shadow: 0 6px 14px rgb(15 23 42 / 0.045);
            transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            border-color: rgb(251 146 60);
            box-shadow: 0 12px 24px rgb(15 23 42 / 0.09);
        }

        .kanban-card__content {
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .kanban-card__top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 7px;
            align-items: start;
        }

        .kanban-card__candidate {
            margin: 0;
            color: var(--kanban-text);
            font-size: 13.25px;
            font-weight: 750;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .kanban-card__id {
            color: var(--kanban-muted);
            font-size: 12px;
            font-weight: 650;
        }

        .kanban-card__toggle {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--kanban-border);
            border-radius: 999px;
            background: transparent;
            color: var(--kanban-muted);
            cursor: pointer;
            transition: background 140ms ease, color 140ms ease, transform 140ms ease;
        }

        .kanban-card__toggle:hover {
            background: rgb(255 247 237);
            color: var(--kanban-orange);
        }

        .kanban-card__toggle svg {
            width: 13px;
            height: 13px;
            transition: transform 140ms ease;
        }

        .kanban-card.is-compact .kanban-card__toggle svg {
            transform: rotate(180deg);
        }

        .kanban-card__actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 7px;
        }

        .kanban-card__job {
            margin: 3px 0 0;
            color: rgb(71 85 105);
            font-size: 12.75px;
            line-height: 1.32;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .dark .kanban-card__job {
            color: rgb(212 212 216);
        }

        .kanban-card__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 7px;
        }

        .kanban-badge {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            border-radius: 999px;
            padding: 4px 6px;
            font-size: 11.25px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .kanban-badge.is-gray { background: rgb(241 245 249); color: rgb(51 65 85); }
        .kanban-badge.is-info { background: rgb(224 242 254); color: rgb(3 105 161); }
        .kanban-badge.is-warning { background: rgb(254 243 199); color: rgb(146 64 14); }
        .kanban-badge.is-primary { background: rgb(255 237 213); color: rgb(194 65 12); }
        .kanban-badge.is-success { background: rgb(220 252 231); color: rgb(21 128 61); }
        .kanban-badge.is-danger { background: rgb(254 226 226); color: rgb(185 28 28); }
        .kanban-badge.is-ai { background: rgb(238 242 255); color: rgb(67 56 202); }

        .kanban-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            border: 1px dashed var(--kanban-border);
            border-radius: 12px;
            background: color-mix(in srgb, var(--kanban-card) 70%, transparent);
            color: var(--kanban-muted);
            font-size: 13px;
            text-align: center;
        }

        .kanban-card__description {
            margin: 7px 0 0;
            color: var(--kanban-muted);
            font-size: 12.25px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .kanban-card__details {
            overflow: hidden;
            transform-origin: top;
        }

        .kanban-card__footer {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-top: 7px;
            padding-top: 7px;
            border-top: 1px solid color-mix(in srgb, var(--kanban-border) 72%, transparent);
            color: var(--kanban-muted);
            font-size: 11.75px;
            line-height: 1.3;
        }

        .kanban-card__footer span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-card__date {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 11.75px;
            line-height: 1.3;
        }

        .kanban-transition-enter,
        .kanban-transition-leave {
            transition: opacity 180ms ease, transform 180ms ease, max-height 180ms ease;
        }

        .kanban-transition-enter-start,
        .kanban-transition-leave-end {
            max-height: 0;
            opacity: 0;
            transform: translateY(-4px) scaleY(0.98);
        }

        .kanban-transition-enter-end,
        .kanban-transition-leave-start {
            max-height: 180px;
            opacity: 1;
            transform: translateY(0) scaleY(1);
        }

        @media (max-width: 900px) {
            .kanban-control-top {
                grid-template-columns: 1fr;
            }

            .kanban-search-wrap {
                min-width: 0;
            }
        }

        @media (max-width: 768px) {
            .kanban-board {
                grid-auto-columns: minmax(280px, 86vw);
            }

            .kanban-work-queues {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div
        class="recruitment-kanban"
        x-data="{
            activeStage: 'all',
            scrollToStage(stage) {
                document.getElementById(`kanban-stage-${stage}`)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'start',
                });
            },
        }"
    >
        <section class="kanban-panel kanban-controls" aria-label="Bộ lọc Kanban">
            <div class="kanban-control-top">
                <div class="kanban-summary">
                    <h2 class="kanban-summary__title">Bảng điều phối ứng tuyển</h2>
                    <p class="kanban-summary__meta">
                        Đang theo dõi {{ $totalApplications }} hồ sơ ứng tuyển.
                        @if ($quickFilter !== 'all')
                            Hàng đợi: {{ $activeQueue['label'] }}.
                        @endif
                    </p>
                </div>

                <div class="kanban-search-wrap">
                    <div class="kanban-search">
                        <svg class="kanban-search__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.631 2.631a.75.75 0 1 0 1.061-1.06l-2.631-2.632A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>

                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="kanban-search__input"
                            placeholder="Tìm ứng viên, vị trí, email hoặc mã hồ sơ..."
                        >

                        @if (filled($search))
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="kanban-search__clear"
                                aria-label="Xóa từ khóa tìm kiếm"
                            >
                                ×
                            </button>
                        @endif
                    </div>

                    @if ($quickFilter !== 'all' || filled($search))
                        <button
                            type="button"
                            class="kanban-reset"
                            wire:click="$set('quickFilter', 'all'); $set('search', '')"
                        >
                            Đặt lại
                        </button>
                    @endif
                </div>
            </div>

            <div class="kanban-work-queues" aria-label="Hàng đợi xử lý">
                @foreach ($workQueues as $queueKey => $queue)
                    <button
                        type="button"
                        wire:click="$set('quickFilter', '{{ $queueKey }}')"
                        class="kanban-work-queue {{ $quickFilter === $queueKey ? 'is-active' : '' }}"
                    >
                        <span>
                            <span class="kanban-work-queue__label">{{ $queue['label'] }}</span>
                            <span class="kanban-work-queue__description">{{ $queue['description'] }}</span>
                        </span>

                        <span class="kanban-work-queue__count">{{ $queue['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <nav class="kanban-stage-tabs" aria-label="Điều hướng giai đoạn">
                <button
                    type="button"
                    class="kanban-stage-tabs__button"
                    x-bind:class="{ 'is-active': activeStage === 'all' }"
                    x-on:click="activeStage = 'all'; scrollToStage('{{ $columns[0]['key'] ?? '' }}')"
                >
                    <span>Tất cả</span>
                    <span class="kanban-stage-tabs__count">{{ $totalApplications }}</span>
                </button>

                @foreach ($columns as $column)
                    <button
                        type="button"
                        class="kanban-stage-tabs__button"
                        x-bind:class="{ 'is-active': activeStage === '{{ $column['key'] }}' }"
                        x-on:click="activeStage = '{{ $column['key'] }}'; scrollToStage('{{ $column['key'] }}')"
                    >
                        <span>{{ $column['label'] }}</span>
                        <span class="kanban-stage-tabs__count">{{ $column['count'] }}</span>
                    </button>
                @endforeach
            </nav>
        </section>

        <div class="kanban-board" aria-label="Bảng Kanban ứng tuyển">
            @foreach ($columns as $column)
                <section class="kanban-column" id="kanban-stage-{{ $column['key'] }}">
                    <header class="kanban-column__header">
                        <div class="kanban-column__title">
                            <span class="kanban-column__dot" aria-hidden="true"></span>
                            <span>{{ $column['label'] }}</span>
                        </div>

                        <span class="kanban-column__count">{{ $column['count'] }}</span>
                    </header>

                    <div class="kanban-column__body">
                        @forelse ($column['cards'] as $card)
                            <article
                                x-data="{ expanded: false }"
                                x-bind:class="{ 'is-compact': ! expanded }"
                                class="kanban-card"
                            >
                                <a href="{{ $card['url'] }}" class="kanban-card__content" aria-label="Xem chi tiết hồ sơ {{ $card['candidate'] }}">
                                    <div class="kanban-card__top">
                                        <div>
                                            <h3 class="kanban-card__candidate">{{ $card['candidate'] }}</h3>
                                            <p class="kanban-card__job">{{ $card['job'] }}</p>
                                        </div>

                                        <span class="kanban-card__id">#{{ $card['id'] }}</span>
                                    </div>

                                    <div class="kanban-card__badges">
                                        <span class="kanban-badge {{ $toneClasses[$card['color']] ?? $toneClasses['gray'] }}">
                                            {{ $card['status'] }}
                                        </span>

                                        @if ($card['has_ai'])
                                            <span class="kanban-badge is-ai">AI {{ $card['ai_score'] }}/100</span>
                                        @endif
                                    </div>

                                    <div
                                        x-show="expanded"
                                        x-transition:enter="kanban-transition-enter"
                                        x-transition:enter-start="kanban-transition-enter-start"
                                        x-transition:enter-end="kanban-transition-enter-end"
                                        x-transition:leave="kanban-transition-leave"
                                        x-transition:leave-start="kanban-transition-leave-start"
                                        x-transition:leave-end="kanban-transition-leave-end"
                                        class="kanban-card__details"
                                    >
                                        @if ($card['description'])
                                            <p class="kanban-card__description">{{ $card['description'] }}</p>
                                        @endif

                                        <div class="kanban-card__footer">
                                            @if ($card['branch'])
                                                <span>{{ $card['branch'] }}</span>
                                            @endif

                                            @if ($card['department'])
                                                <span>{{ $card['department'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>

                                <div class="kanban-card__actions">
                                    @if ($card['applied_at'])
                                        <p class="kanban-card__date">Nộp: {{ $card['applied_at'] }}</p>
                                    @else
                                        <span></span>
                                    @endif

                                    <button
                                        type="button"
                                        class="kanban-card__toggle"
                                        x-on:click="expanded = ! expanded"
                                        x-bind:aria-label="expanded ? 'Thu gọn thẻ hồ sơ' : 'Mở rộng thẻ hồ sơ'"
                                    >
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 0 1-1.06-.02L10 8.83l-3.71 3.94a.75.75 0 1 1-1.08-1.04l4.25-4.5a.75.75 0 0 1 1.08 0l4.25 4.5a.75.75 0 0 1-.02 1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="kanban-empty">
                                Chưa có hồ sơ ở giai đoạn này.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
