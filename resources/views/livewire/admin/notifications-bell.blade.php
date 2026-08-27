<div wire:poll.30s class="relative">
    <style>
        .admin-notification-trigger {
            position: relative;
            display: inline-flex;
            height: 2.25rem;
            width: 2.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #6b7280;
            transition: background-color 160ms ease, color 160ms ease, box-shadow 160ms ease;
        }

        .admin-notification-trigger:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .admin-notification-trigger.has-unread {
            background: #fff7ed;
            color: #ea580c;
            box-shadow: 0 0 0 1px #fed7aa;
        }

        .dark .admin-notification-trigger {
            color: #9ca3af;
        }

        .dark .admin-notification-trigger:hover {
            background: rgb(255 255 255 / 0.08);
            color: #f3f4f6;
        }

        .dark .admin-notification-trigger.has-unread {
            background: rgb(234 88 12 / 0.18);
            color: #fdba74;
            box-shadow: 0 0 0 1px rgb(251 146 60 / 0.35);
        }

        .admin-notification-badge {
            position: absolute;
            z-index: 2;
            top: -0.3rem;
            right: -0.38rem;
            display: inline-flex;
            min-width: 1.15rem;
            height: 1.15rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #ea580c;
            padding: 0 0.3rem;
            color: #fff;
            font-size: 0.625rem;
            font-weight: 800;
            line-height: 1;
            box-shadow: 0 0 0 2px #fff;
        }

        .dark .admin-notification-badge {
            box-shadow: 0 0 0 2px #111827;
        }

        .admin-notifications-tabs {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 42px;
        }

        .admin-notifications-tab {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 42px;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            color: #6b7280;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
            padding: 0 2px;
            outline: none !important;
            box-shadow: none !important;
        }

        .admin-notifications-tab:hover {
            color: #111827;
        }

        .admin-notifications-tab.is-active {
            color: #c2410c;
        }

        .admin-notifications-tab.is-active::after {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 2px;
            border-radius: 999px;
            background: #ea580c;
            content: '';
        }

        .dark .admin-notifications-tab {
            color: #9ca3af;
        }

        .dark .admin-notifications-tab:hover {
            color: #f3f4f6;
        }

        .dark .admin-notifications-tab.is-active {
            color: #fdba74;
        }

        .admin-notification-item {
            position: relative;
            display: flex;
            width: 100%;
            gap: 11px;
            border: 1px solid transparent !important;
            border-radius: 10px !important;
            background: transparent;
            cursor: pointer;
            outline: none !important;
            box-shadow: none !important;
            padding: 11px 12px;
            text-align: left;
            transition: background 140ms ease, transform 140ms ease;
        }

        .admin-notification-item:hover {
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .admin-notification-item.is-unread {
            background: #fff8f1;
            box-shadow: inset 3px 0 0 #f97316 !important;
        }

        .admin-notification-item.is-unread::after {
            position: absolute;
            top: 14px;
            right: 13px;
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 999px;
            background: #f97316;
            content: '';
        }

        .admin-notification-item.is-unread > span:last-child {
            padding-right: 0.8rem;
        }

        .admin-notification-item:focus,
        .admin-notification-item:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }

        .dark .admin-notification-item:hover {
            background: rgb(255 255 255 / 0.05);
        }

        .dark .admin-notification-item.is-unread {
            background: rgb(234 88 12 / 0.12);
            box-shadow: inset 3px 0 0 #fb923c !important;
        }

        .admin-notification-item__message {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .admin-notification-item__meta {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }

    </style>

    <x-filament::dropdown placement="bottom-end" teleport width="sm">
        <x-slot name="trigger">
            <button
                type="button"
                class="admin-notification-trigger {{ $unreadCount > 0 ? 'has-unread' : '' }} focus:outline-none focus:ring-2 focus:ring-primary-500"
                aria-label="Mở thông báo"
            >
                <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />

                @if ($unreadCount > 0)
                    <span class="admin-notification-badge">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>
        </x-slot>

        <div class="w-[23rem] max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between gap-3 px-4 py-3 dark:border-white/10">
                <div class="min-w-0">
                    <div class="text-base font-bold leading-5 text-gray-950 dark:text-white">Thông báo</div>
                </div>

                @if ($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="shrink-0 text-xs font-semibold text-primary-600 transition hover:text-primary-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-primary-400"
                    >
                        Đánh dấu đã xem
                    </button>
                @endif
            </div>

            <div class="flex justify-center border-y border-gray-200 px-4 dark:border-white/10">
                <div class="admin-notifications-tabs">
                    <button
                        type="button"
                        wire:click="setTab('pending')"
                        class="admin-notifications-tab {{ $tab === 'pending' ? 'is-active' : '' }}"
                    >
                        Mới
                        @if ($unreadCount > 0)
                            <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-primary-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <span class="text-sm font-medium text-gray-300 dark:text-gray-600" aria-hidden="true">|</span>
                    <button
                        type="button"
                        wire:click="setTab('done')"
                        class="admin-notifications-tab {{ $tab === 'done' ? 'is-active' : '' }}"
                    >
                        Đã xem
                    </button>
                </div>
            </div>

            <div class="max-h-80 overflow-y-auto p-2">
                @forelse ($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $icon = match ($notification->type) {
                            'offer_approval_requested' => 'heroicon-o-document-text',
                            'offer_rejected_by_director' => 'heroicon-o-arrow-path',
                            'offer_accepted_by_candidate' => 'heroicon-o-check-circle',
                            'offer_declined_by_candidate' => 'heroicon-o-x-circle',
                            'interview_panel_assigned' => 'heroicon-o-calendar-days',
                            'interview_panel_ready' => 'heroicon-o-clipboard-document-check',
                            default => 'heroicon-o-bell',
                        };
                        $iconTone = match ($notification->type) {
                            'offer_approval_requested' => 'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300',
                            'offer_rejected_by_director', 'offer_declined_by_candidate' => 'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300',
                            'offer_accepted_by_candidate' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
                            'interview_panel_assigned' => 'bg-sky-50 text-sky-700 dark:bg-sky-400/10 dark:text-sky-300',
                            'interview_panel_ready' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300',
                            default => 'bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-300',
                        };
                    @endphp
                    <button
                        type="button"
                        wire:click="openNotification({{ $notification->id }})"
                        class="admin-notification-item group {{ $notification->read_at ? '' : 'is-unread' }}"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $iconTone }}">
                            <x-filament::icon :icon="$icon" class="h-4 w-4" />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold leading-5 text-gray-950 dark:text-white">
                                {{ $notification->title }}
                            </span>

                            @if (filled(data_get($data, 'subject')) || filled(data_get($data, 'context')))
                                <span class="admin-notification-item__meta mt-1 block text-xs leading-5 text-gray-600 dark:text-gray-300">
                                    @if (filled(data_get($data, 'subject')))
                                        <strong class="font-semibold text-gray-800 dark:text-gray-100">{{ data_get($data, 'subject') }}</strong>
                                    @endif
                                    @if (filled(data_get($data, 'subject')) && filled(data_get($data, 'context')))
                                        <span class="px-1 text-gray-300 dark:text-gray-600">·</span>
                                    @endif
                                    {{ data_get($data, 'context') }}
                                </span>
                            @endif

                            @if (filled($notification->message))
                                <span class="admin-notification-item__message mt-1.5 text-xs leading-5 text-gray-600 dark:text-gray-400">
                                    {{ $notification->message }}
                                </span>
                            @endif

                            <span class="mt-2 flex items-center justify-between gap-3 text-[11px] text-gray-400 dark:text-gray-500">
                                <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                @if (filled(data_get($data, 'action_label')))
                                    <span class="font-semibold text-primary-600 group-hover:text-primary-700 dark:text-primary-400">{{ data_get($data, 'action_label') }}</span>
                                @endif
                            </span>
                        </span>
                    </button>
                @empty
                    <div style="padding:24px 20px 26px;text-align:center;">
                        <div style="width:36px;height:36px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;border-radius:999px;background:#f3f4f6;color:#9ca3af;border:1px solid #e5e7eb;">
                            <x-filament::icon icon="heroicon-o-bell-slash" style="width:18px;height:18px;" />
                        </div>
                        @if ($tab === 'pending')
                            <div style="font-size:14px;font-weight:700;color:#111827;">Không có thông báo mới</div>
                        @else
                            <div style="font-size:14px;font-weight:700;color:#111827;">Chưa có thông báo đã xem</div>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </x-filament::dropdown>
</div>
