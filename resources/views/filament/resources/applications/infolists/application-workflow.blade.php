<div class="space-y-7">
    <section>
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-o-arrow-path-rounded-square" class="h-5 w-5 text-primary-500" />
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Tiến độ tuyển dụng</h3>
        </div>
        <ol class="mx-auto mt-5 max-w-6xl sm:grid sm:grid-cols-5">
            @foreach ($stages as $stage)
                <li class="relative flex min-w-0 items-start gap-3 pb-4 last:pb-0 sm:block sm:px-1 sm:pb-0 sm:text-center">
                    @if (! $loop->last)
                        <span @class([
                            'absolute left-4 top-8 h-[calc(100%-2rem)] w-px sm:left-1/2 sm:top-4 sm:h-0.5 sm:w-full',
                            'bg-success-300 dark:bg-success-500/50' => $stage['state'] === 'completed',
                            'bg-gray-200 dark:bg-white/10' => $stage['state'] !== 'completed',
                        ])></span>
                    @endif
                    <span @class([
                        'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-xs font-bold transition sm:mx-auto',
                        'border-success-500 bg-success-500 text-white dark:border-success-500 dark:bg-success-500' => $stage['state'] === 'completed',
                        'border-primary-500 bg-primary-500 text-white shadow-sm ring-4 ring-primary-50 dark:border-primary-500 dark:bg-primary-500 dark:ring-primary-500/10' => $stage['state'] === 'current',
                        'border-gray-200 bg-white text-gray-400 dark:border-white/10 dark:bg-gray-900' => $stage['state'] === 'pending',
                    ])>
                        @if ($stage['state'] === 'completed')
                            <x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <div class="min-w-0 pt-1 sm:mt-2 sm:pt-0">
                        <span @class([
                            'block text-sm font-medium leading-5',
                            'text-gray-950 dark:text-white' => $stage['state'] === 'completed',
                            'text-primary-700 dark:text-primary-300' => $stage['state'] === 'current',
                            'text-gray-400' => $stage['state'] === 'pending',
                        ])>{{ $stage['label'] }}</span>
                        @if ($stage['state'] === 'current')
                            <span class="mt-0.5 block text-xs font-medium text-primary-600 dark:text-primary-400">Hiện tại</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
        @if ($isRejected)
            <div class="mt-4 flex items-center gap-2 rounded-lg bg-danger-50 px-3 py-2.5 text-sm font-medium text-danger-700 dark:bg-danger-500/10 dark:text-danger-300">
                <x-filament::icon icon="heroicon-o-x-circle" class="h-5 w-5 shrink-0" />
                <span>{{ $finalLabel }}</span>
            </div>
        @endif
    </section>

    @if ($preScreenings !== [])
        <section>
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-phone" class="h-5 w-5 text-primary-500" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Liên hệ sơ tuyển</h3>
            </div>
            <div class="mt-4 space-y-3">
                @foreach ($preScreenings as $item)
                    <article class="grid gap-4 rounded-lg bg-gray-50 px-4 py-4 dark:bg-white/5 md:grid-cols-[minmax(10rem,0.8fr)_minmax(11rem,0.8fr)_minmax(0,1.4fr)]">
                        <div>
                            <p class="text-xs tabular-nums text-gray-500">{{ $item['contactedAt'] }}</p>
                            <p class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $item['channel'] }}</p>
                        </div>
                        <div>
                            <x-filament::badge :color="$item['outcomeTone']">{{ $item['outcome'] }}</x-filament::badge>
                            <p class="mt-1.5 text-xs text-gray-500">Ghi nhận bởi {{ $item['handler'] }}</p>
                        </div>
                        <div class="min-w-0 md:pl-2">
                            <p class="text-xs font-medium uppercase text-gray-500">Ghi chú trao đổi</p>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $item['note'] ?: 'Không có ghi chú bổ sung.' }}</p>
                            @if ($item['followUpAt'] !== '-')<p class="mt-1 text-xs font-medium text-warning-600">Liên hệ lại: {{ $item['followUpAt'] }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-primary-500" />
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Lịch sử xử lý</h3>
            @if ($historyCount > 0)<x-filament::badge color="gray">{{ $historyCount }} cập nhật</x-filament::badge>@endif
        </div>
        <div class="relative mt-4 ml-2 border-l border-gray-200 pl-5 dark:border-white/10">
            @forelse ($recentHistories as $history)
                @include('filament.resources.applications.infolists.application-history-entry', ['history' => $history])
            @empty
                <p class="text-sm text-gray-500">Chưa có nhật ký xử lý.</p>
            @endforelse
        </div>
        @if ($olderHistories !== [])
            <details class="group mt-4">
                <summary class="flex cursor-pointer list-none items-center gap-2 text-sm font-medium text-gray-600 outline-none hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4 transition group-open:rotate-180" />
                    <span>Xem thêm {{ count($olderHistories) }} cập nhật trước đó</span>
                </summary>
                <div class="relative mt-4 ml-2 border-l border-gray-200 pl-5 dark:border-white/10">
                    @foreach ($olderHistories as $history)
                        @include('filament.resources.applications.infolists.application-history-entry', ['history' => $history])
                    @endforeach
                </div>
            </details>
        @endif
    </section>
</div>
