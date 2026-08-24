<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Việc cần xử lý
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                    {{ $scopeLabel }}
                </p>
            </div>
            <div class="flex w-fit items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5">
                <span class="text-lg font-semibold leading-none tabular-nums text-gray-950 dark:text-white">
                    {{ number_format($totalOpenItems) }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    đang chờ
                </span>
            </div>
        </div>

        @if ($totalOpenItems > 0)
            <div class="mt-4 grid gap-x-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($activeItems as $item)
                    @php
                        $colorClasses = match ($item['color']) {
                            'danger' => [
                                'icon' => 'text-danger-600 bg-danger-50 dark:text-danger-300 dark:bg-danger-500/10',
                                'count' => 'text-danger-700 dark:text-danger-300',
                            ],
                            'warning' => [
                                'icon' => 'text-warning-600 bg-warning-50 dark:text-warning-300 dark:bg-warning-500/10',
                                'count' => 'text-warning-700 dark:text-warning-300',
                            ],
                            'info' => [
                                'icon' => 'text-info-600 bg-info-50 dark:text-info-300 dark:bg-info-500/10',
                                'count' => 'text-info-700 dark:text-info-300',
                            ],
                            default => [
                                'icon' => 'text-gray-600 bg-gray-100 dark:text-gray-300 dark:bg-white/10',
                                'count' => 'text-gray-900 dark:text-white',
                            ],
                        };
                    @endphp

                    <button
                        type="button"
                        wire:click="mountAction('viewWorkload', { key: '{{ $item['key'] }}' })"
                        title="{{ $item['description'] }}"
                        class="group flex min-h-24 items-start gap-3 border-t border-gray-200 py-4 text-left transition first:border-t-0 hover:text-primary-600 sm:[&:nth-child(2)]:border-t-0 dark:border-white/10 dark:hover:text-primary-400 xl:[&:nth-child(3)]:border-t-0"
                    >
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $colorClasses['icon'] }}">
                            <x-filament::icon :icon="$item['icon']" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-sm font-semibold text-gray-950 transition group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                    {{ $item['label'] }}
                                </h3>
                                <span class="shrink-0 text-lg font-semibold leading-none tabular-nums {{ $colorClasses['count'] }}">
                                    {{ number_format($item['count']) }}
                                </span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-sm leading-5 text-gray-500 dark:text-gray-400">
                                {{ $item['description'] }}
                            </p>
                            <div class="mt-2 flex items-center gap-1 text-xs font-medium text-gray-500 transition group-hover:text-primary-600 dark:text-gray-400 dark:group-hover:text-primary-400">
                                <span>{{ $item['priority'] }}</span>
                                <x-filament::icon icon="heroicon-m-chevron-right" class="h-3.5 w-3.5" />
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="mt-4 flex items-center gap-3 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5 shrink-0" />
                <span>Hiện không có việc tuyển dụng cần xử lý.</span>
            </div>
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
