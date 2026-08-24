<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Lịch phỏng vấn sắp tới</x-slot>
        <x-slot name="description">{{ $scopeLabel }}</x-slot>

        <x-slot name="afterHeader">
            <a
                href="{{ $calendarUrl }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 transition hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            >
                <span>Xem toàn bộ lịch</span>
                <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4" />
            </a>
        </x-slot>

        @if ($total > 0)
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($interviews as $interview)
                    <a
                        href="{{ $interview['url'] }}"
                        class="group grid gap-3 py-3 first:pt-0 last:pb-0 sm:grid-cols-[7rem_minmax(0,1.25fr)_minmax(0,1fr)_auto] sm:items-center"
                    >
                        <div class="flex items-center gap-3 sm:block">
                            <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $interview['dayLabel'] }}</p>
                            <p class="text-sm tabular-nums text-gray-500 dark:text-gray-400">{{ $interview['time'] }}</p>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-950 transition group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                {{ $interview['candidate'] }}
                            </p>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">
                                {{ $interview['job'] }} · {{ $interview['branch'] }}
                            </p>
                        </div>

                        <div class="min-w-0 text-sm">
                            <p class="truncate text-gray-700 dark:text-gray-300">{{ $interview['type'] }}</p>
                            <p class="truncate text-gray-500 dark:text-gray-400">{{ $interview['interviewer'] }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:justify-end">
                            <x-filament::badge :color="$interview['inviteColor']">
                                {{ $interview['inviteStatus'] }}
                            </x-filament::badge>
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-primary-500" />
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($total > count($interviews))
                <div class="mt-4 border-t border-gray-200 pt-3 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                    Còn {{ number_format($total - count($interviews)) }} lịch khác trong 7 ngày tới.
                </div>
            @endif
        @else
            <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
                <x-filament::icon icon="heroicon-o-calendar-days" class="h-5 w-5 shrink-0 text-gray-400" />
                <span>Chưa có lịch phỏng vấn nào trong 7 ngày tới.</span>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
