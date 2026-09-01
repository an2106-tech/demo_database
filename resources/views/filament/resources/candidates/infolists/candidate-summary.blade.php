<div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="flex min-w-0 items-start gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
            <x-filament::icon icon="heroicon-o-user" class="h-6 w-6" />
        </div>
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-semibold text-gray-950 dark:text-white">{{ $candidateName }}</h2>
                @if ($isRestricted)
                    <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle">Hạn chế tuyển dụng</x-filament::badge>
                @endif
            </div>
            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $profileTitle }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $experience }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <x-filament::badge color="info">{{ $applicationCount }} lượt ứng tuyển</x-filament::badge>
        <x-filament::badge :color="$latestStageColor">{{ $latestStage }}</x-filament::badge>
    </div>
</div>
