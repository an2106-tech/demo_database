<div class="space-y-5">
    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
            <dd class="mt-1 break-words text-sm font-medium text-gray-950 dark:text-white">{{ $email }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Số điện thoại</dt>
            <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $phone }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Kinh nghiệm đã khai báo</dt>
            <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $experienceCount }} mục</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Học vấn đã khai báo</dt>
            <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $educationCount }} mục</dd>
        </div>
    </dl>

    <div class="border-t border-gray-200 pt-4 dark:border-white/10">
        <p class="text-sm text-gray-500 dark:text-gray-400">Mục tiêu nghề nghiệp</p>
        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-800 dark:text-gray-200">{{ $careerObjective }}</p>
    </div>

    <div class="border-t border-gray-200 pt-4 dark:border-white/10">
        <p class="text-sm text-gray-500 dark:text-gray-400">Kỹ năng</p>
        @if (count($skills))
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($skills as $skill)
                    <x-filament::badge color="gray">{{ $skill }}</x-filament::badge>
                @endforeach
            </div>
        @else
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Chưa cập nhật</p>
        @endif
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-white/10">
        <div class="min-w-0">
            <p class="text-sm text-gray-500 dark:text-gray-400">CV hồ sơ hiện tại</p>
            <p class="mt-1 truncate text-sm font-medium text-gray-950 dark:text-white">{{ $currentCvName ?: 'Chưa có CV' }}</p>
        </div>
        @if ($currentCvUrl)
            <x-filament::button tag="a" :href="$currentCvUrl" target="_blank" color="gray" icon="heroicon-o-arrow-top-right-on-square">
                Mở CV
            </x-filament::button>
        @endif
    </div>
</div>
