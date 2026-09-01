<div class="divide-y divide-gray-200 dark:divide-white/10">
    @forelse ($applications as $application)
        <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $application['detailUrl'] }}" class="font-semibold text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400">
                        {{ $application['jobTitle'] }}
                    </a>
                    <span class="text-xs font-medium tabular-nums text-gray-400">{{ $application['code'] }}</span>
                    <x-filament::badge :color="$application['stageColor']">{{ $application['stage'] }}</x-filament::badge>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $application['branchName'] }} · {{ $application['appliedAt'] }}
                </p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $application['status'] }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if ($application['cvUrl'])
                    <x-filament::button tag="a" :href="$application['cvUrl']" target="_blank" color="gray" size="sm" icon="heroicon-o-document-text">
                        CV đã nộp
                    </x-filament::button>
                @endif
                <x-filament::button tag="a" :href="$application['detailUrl']" color="gray" size="sm" icon="heroicon-o-arrow-right">
                    Mở hồ sơ
                </x-filament::button>
            </div>
        </div>
    @empty
        <div class="py-8 text-center">
            <x-filament::icon icon="heroicon-o-inbox" class="mx-auto h-8 w-8 text-gray-400" />
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Chưa có lượt ứng tuyển trong phạm vi quản lý.</p>
        </div>
    @endforelse

    @if ($total > count($applications))
        <p class="pt-4 text-sm text-gray-500 dark:text-gray-400">Đang hiển thị {{ count($applications) }}/{{ $total }} lượt gần nhất.</p>
    @endif
</div>
