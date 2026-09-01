<div class="space-y-5">
    <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex min-w-0 items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                <x-filament::icon icon="heroicon-o-user" class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <h2 class="text-xl font-semibold text-gray-950 dark:text-white">{{ $candidateName }}</h2>
                    <span class="text-sm font-medium tabular-nums text-gray-400">{{ $applicationCode }}</span>
                </div>
                <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $jobTitle }}</p>
                <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-building-office-2" class="h-4 w-4 shrink-0" />
                    <span class="truncate">{{ $branchName }}</span>
                </p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 shrink-0" />
            <span>Ứng tuyển lúc <strong class="font-medium tabular-nums text-gray-800 dark:text-gray-200">{{ $appliedAt }}</strong></span>
        </div>
    </div>

    <div class="flex items-start gap-3 border-t border-gray-200 pt-4 dark:border-white/10">
        <x-filament::icon icon="heroicon-o-information-circle" class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" />
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $statusLabel }}</p>
                <x-filament::badge :color="$stageColor">{{ $stageLabel }}</x-filament::badge>
            </div>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $statusDescription }}</p>
        </div>
    </div>
</div>
