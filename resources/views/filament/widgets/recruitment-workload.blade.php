<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    Hàng đợi tuyển dụng
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                    {{ $scopeLabel }}
                </p>
            </div>
            <span class="inline-flex w-fit shrink-0 items-center rounded-md bg-gray-50 px-2.5 py-1 text-sm font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">
                {{ number_format($totalOpenItems) }} việc đang mở
            </span>
        </div>

        @if ($totalOpenItems > 0)
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($activeItems as $item)
                    @php
                        $colorClasses = match ($item['color']) {
                            'danger' => [
                                'card' => 'bg-danger-50/70 ring-danger-100 hover:bg-danger-50 dark:bg-danger-500/10 dark:ring-danger-500/20 dark:hover:bg-danger-500/15',
                                'count' => 'text-danger-700 dark:text-danger-300',
                            ],
                            'warning' => [
                                'card' => 'bg-warning-50/70 ring-warning-100 hover:bg-warning-50 dark:bg-warning-500/10 dark:ring-warning-500/20 dark:hover:bg-warning-500/15',
                                'count' => 'text-warning-700 dark:text-warning-300',
                            ],
                            'info' => [
                                'card' => 'bg-info-50/70 ring-info-100 hover:bg-info-50 dark:bg-info-500/10 dark:ring-info-500/20 dark:hover:bg-info-500/15',
                                'count' => 'text-info-700 dark:text-info-300',
                            ],
                            default => [
                                'card' => 'bg-gray-50 ring-gray-200 hover:bg-gray-100/70 dark:bg-gray-800/70 dark:ring-gray-700 dark:hover:bg-gray-800',
                                'count' => 'text-gray-700 dark:text-gray-200',
                            ],
                        };
                    @endphp

                    <a
                        href="{{ $item['url'] }}"
                        title="{{ $item['description'] }}"
                        class="group rounded-lg p-4 ring-1 transition {{ $colorClasses['card'] }}"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold leading-5 text-gray-950 dark:text-white">
                                    {{ $item['label'] }}
                                </h3>
                                <p class="mt-1 line-clamp-2 text-sm leading-5 text-gray-600 dark:text-gray-400">
                                    {{ $item['description'] }}
                                </p>
                            </div>
                            <span class="shrink-0 text-xl font-semibold leading-6 tabular-nums {{ $colorClasses['count'] }}">
                                {{ number_format($item['count']) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($idleCount > 0)
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ $idleCount }} nhóm việc còn lại đang ổn định, chưa cần thao tác.
                </div>
            @endif
        @else
            <div class="mt-4 rounded-xl border border-success-200 bg-success-50 px-4 py-4 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
                Hàng đợi đang trống. Chưa có việc tuyển dụng cần xử lý ngay trong phạm vi hiện tại.
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
