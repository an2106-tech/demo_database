<div wire:poll.30s class="relative">
    <style>
        @keyframes admin-notification-bell-ring {
            0%, 100% { transform: rotate(0deg); }
            15% { transform: rotate(12deg); }
            30% { transform: rotate(-10deg); }
            45% { transform: rotate(7deg); }
            60% { transform: rotate(-5deg); }
            75% { transform: rotate(2deg); }
        }

        .admin-notification-bell-ring {
            animation: admin-notification-bell-ring .9s ease-in-out 1;
            transform-origin: 50% 10%;
        }
    </style>

    <x-filament::dropdown placement="bottom-end" teleport width="md">
        <x-slot name="trigger">
            <button
                type="button"
                class="relative inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-200"
                aria-label="Mở thông báo"
            >
                <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5 {{ $unreadCount > 0 ? 'admin-notification-bell-ring' : '' }}" />

                @if ($unreadCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 inline-flex min-w-4 items-center justify-center rounded-full bg-danger-600 px-1 text-[10px] font-bold leading-4 text-white ring-2 ring-white dark:ring-gray-900">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>
        </x-slot>

        <div class="w-80 overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-white/10">
                <div>
                    <div class="text-base font-bold text-gray-950 dark:text-white">Thông báo</div>
                </div>

                @if ($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400"
                    >
                        Đánh dấu đã xử lý
                    </button>
                @endif
            </div>

            <div style="display:flex;justify-content:center;border-bottom:1px solid #e5e7eb;padding:0 16px;">
                <div style="display:inline-flex;align-items:center;gap:10px;">
                <button
                    type="button"
                    wire:click="setTab('pending')"
                    style="display:inline-flex;align-items:center;justify-content:center;gap:6px;border:0;border-bottom:2px solid {{ $tab === 'pending' ? '#ea580c' : 'transparent' }};background:transparent;padding:12px 2px 10px;font-size:14px;font-weight:700;color:{{ $tab === 'pending' ? '#c2410c' : '#6b7280' }};cursor:pointer;"
                >
                    Cần xử lý
                    @if ($unreadCount > 0)
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;border-radius:999px;background:#ea580c;color:#ffffff;padding:0 5px;font-size:10px;font-weight:800;line-height:1;">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </button>

                <span style="color:#d1d5db;font-size:14px;font-weight:600;">|</span>

                <button
                    type="button"
                    wire:click="setTab('done')"
                    style="display:inline-flex;align-items:center;justify-content:center;border:0;border-bottom:2px solid {{ $tab === 'done' ? '#ea580c' : 'transparent' }};background:transparent;padding:12px 2px 10px;font-size:14px;font-weight:700;color:{{ $tab === 'done' ? '#c2410c' : '#6b7280' }};cursor:pointer;"
                >
                    Đã xử lý
                </button>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto p-2">
                @forelse ($notifications as $notification)
                    <button
                        type="button"
                        wire:click="openNotification({{ $notification->id }})"
                        class="group flex w-full gap-3 rounded-lg px-3 py-2.5 text-left transition {{ $notification->read_at ? 'hover:bg-gray-50 dark:hover:bg-white/5' : 'bg-primary-50/80 hover:bg-primary-50 dark:bg-primary-500/10 dark:hover:bg-primary-500/15' }}"
                    >
                        <span class="mt-1 flex h-2 w-2 flex-none rounded-full {{ $notification->read_at ? 'bg-gray-300 dark:bg-gray-600' : 'bg-primary-500' }}"></span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $notification->title }}
                            </span>

                            @if ($notification->message)
                                <span class="mt-1 block line-clamp-2 text-xs leading-5 text-gray-600 dark:text-gray-400">
                                    {{ $notification->message }}
                                </span>
                            @endif

                            <span class="mt-1 block text-[11px] text-gray-400 dark:text-gray-500">
                                {{ $notification->created_at?->diffForHumans() }}
                            </span>
                        </span>
                    </button>
                @empty
                    <div style="padding:34px 24px;text-align:center;">
                        <div style="width:46px;height:46px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;border-radius:999px;background:#f3f4f6;color:#9ca3af;border:1px solid #e5e7eb;">
                            <x-filament::icon icon="heroicon-o-bell-slash" style="width:20px;height:20px;" />
                        </div>
                        @if ($tab === 'pending')
                            <div style="font-size:14px;font-weight:700;color:#111827;">Không có việc cần xử lý</div>
                            <div style="max-width:260px;margin:6px auto 0;font-size:13px;line-height:1.55;color:#6b7280;">Các cập nhật mới trong quy trình tuyển dụng sẽ hiển thị tại đây.</div>
                        @else
                            <div style="font-size:14px;font-weight:700;color:#111827;">Chưa có thông báo đã xử lý</div>
                            <div style="max-width:260px;margin:6px auto 0;font-size:13px;line-height:1.55;color:#6b7280;">Các thông báo đã mở gần đây sẽ hiển thị tại đây.</div>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </x-filament::dropdown>
</div>
