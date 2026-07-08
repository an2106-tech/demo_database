<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Việc cần xử lý
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                    {{ $scopeLabel }}
                </p>
            </div>
            <div class="flex w-fit items-baseline gap-2 rounded-xl bg-gray-50 px-3 py-2 dark:bg-white/5">
                <span class="text-2xl font-semibold leading-none tabular-nums text-gray-950 dark:text-white">
                    {{ number_format($totalOpenItems) }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    việc cần xử lý
                </span>
            </div>
        </div>

        @if ($totalOpenItems > 0)
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($activeItems as $item)
                    @php
                        $colorClasses = match ($item['color']) {
                            'danger' => [
                                'accent' => 'bg-danger-500',
                                'icon' => 'text-danger-600 bg-danger-50 dark:text-danger-300 dark:bg-danger-500/10',
                                'count' => 'text-danger-700 dark:text-danger-300',
                            ],
                            'warning' => [
                                'accent' => 'bg-warning-500',
                                'icon' => 'text-warning-600 bg-warning-50 dark:text-warning-300 dark:bg-warning-500/10',
                                'count' => 'text-warning-700 dark:text-warning-300',
                            ],
                            'info' => [
                                'accent' => 'bg-info-500',
                                'icon' => 'text-info-600 bg-info-50 dark:text-info-300 dark:bg-info-500/10',
                                'count' => 'text-info-700 dark:text-info-300',
                            ],
                            default => [
                                'accent' => 'bg-gray-400',
                                'icon' => 'text-gray-600 bg-gray-100 dark:text-gray-300 dark:bg-white/10',
                                'count' => 'text-gray-900 dark:text-white',
                            ],
                        };
                    @endphp

                    <button
                        type="button"
                        wire:click="mountAction('viewWorkload', { key: '{{ $item['key'] }}' })"
                        title="{{ $item['description'] }}"
                        class="group relative overflow-hidden rounded-xl bg-gray-50 p-4 text-left transition hover:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10"
                    >
                        <span class="absolute inset-x-0 top-0 h-0.5 {{ $colorClasses['accent'] }}"></span>

                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $colorClasses['icon'] }}">
                                <x-filament::icon :icon="$item['icon']" class="h-5 w-5" />
                            </span>

                            <span class="text-2xl font-semibold leading-none tabular-nums {{ $colorClasses['count'] }}">
                                {{ number_format($item['count']) }}
                            </span>
                        </div>

                        <div class="mt-3">
                            <div class="flex items-center gap-2">
                                <h3 class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                    {{ $item['label'] }}
                                </h3>
                                <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['priority'] }}
                                </span>
                            </div>
                            <p class="mt-1 line-clamp-2 min-h-10 text-sm leading-5 text-gray-500 dark:text-gray-400">
                                {{ $item['description'] }}
                            </p>
                        </div>

                        <div class="mt-3 text-sm font-medium text-primary-600 opacity-0 transition group-hover:opacity-100 dark:text-primary-400">
                            Xem nhanh
                        </div>
                    </button>
                @endforeach
            </div>

            @if ($idleCount > 0)
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ $idleCount }} nhóm việc còn lại đang ổn định.
                </div>
            @endif
        @else
            <div class="mt-4 rounded-xl bg-success-50 px-4 py-4 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">
                Hàng đợi đang trống. Chưa có việc tuyển dụng cần xử lý ngay trong phạm vi hiện tại.
            </div>
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
