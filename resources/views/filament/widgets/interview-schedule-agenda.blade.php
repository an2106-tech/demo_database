<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Điều phối lịch phỏng vấn</x-slot>
        <x-slot name="description">{{ $scopeLabel }}</x-slot>

        <x-slot name="afterHeader">
            <a
                href="{{ $kanbanUrl }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 transition hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            >
                <span>Mở Kanban</span>
                <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" />
            </a>
        </x-slot>

        @if (count($interviews) > 0)
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($interviews as $interview)
                    <a
                        href="{{ $interview['url'] }}"
                        class="group grid gap-3 py-3 first:pt-0 last:pb-0 md:grid-cols-[7rem_minmax(0,1fr)_auto] md:items-center"
                    >
                        <div class="flex items-baseline gap-2 md:block">
                            <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $interview['dayLabel'] }}</p>
                            <p class="text-sm tabular-nums text-gray-500 dark:text-gray-400">{{ $interview['time'] }}</p>
                        </div>

                        <div class="min-w-0">
                            <div class="flex min-w-0 items-center gap-2">
                                <p class="truncate text-sm font-semibold text-gray-950 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                    {{ $interview['candidate'] }}
                                </p>
                                <x-filament::badge :color="$interview['statusColor']">
                                    {{ $interview['status'] }}
                                </x-filament::badge>
                            </div>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                {{ $interview['round'] }} · {{ $interview['job'] }} · {{ $interview['branch'] }}
                            </p>
                        </div>

                        <span class="inline-flex items-center justify-center gap-1.5 text-sm font-semibold text-primary-600 dark:text-primary-400">
                            {{ $interview['action'] }}
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                        </span>
                    </a>
                @endforeach
            </div>

            @if ($hasMore)
                <div class="mt-4 border-t border-gray-200 pt-3 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                    Còn lịch cần theo dõi trong hàng đợi Kanban.
                </div>
            @endif
        @else
            <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
                <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5 shrink-0 text-gray-400" />
                <span>Chưa có lịch cần xử lý, lịch sắp tới hoặc kết quả trong 7 ngày gần nhất.</span>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
