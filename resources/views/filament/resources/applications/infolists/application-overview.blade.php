<div class="space-y-7">
    <div class="grid gap-7 xl:grid-cols-2">
        <section class="min-w-0">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-identification" class="h-5 w-5 text-primary-500" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Thông tin ứng viên</h3>
            </div>
            <dl class="mt-4 divide-y divide-gray-100 border-y border-gray-200 dark:divide-white/5 dark:border-white/10">
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Email</dt><dd class="break-words text-sm font-medium text-gray-950 dark:text-white">{{ $candidate['email'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Số điện thoại</dt><dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $candidate['phone'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Kinh nghiệm</dt><dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $candidate['experience'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Tiêu đề hồ sơ</dt><dd class="break-words text-sm font-medium text-gray-950 dark:text-white">{{ $candidate['profileTitle'] }}</dd></div>
            </dl>
        </section>
        <section class="min-w-0 border-t border-gray-200 pt-6 dark:border-white/10 xl:border-l xl:border-t-0 xl:pl-6 xl:pt-0">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-briefcase" class="h-5 w-5 text-primary-500" />
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Thông tin ứng tuyển</h3>
            </div>
            <dl class="mt-4 divide-y divide-gray-100 border-y border-gray-200 dark:divide-white/5 dark:border-white/10">
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Vị trí</dt><dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $application['job'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Phòng ban</dt><dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $application['department'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Chi nhánh</dt><dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $application['branch'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Nguồn hồ sơ</dt><dd class="text-sm font-medium text-gray-950 dark:text-white">{{ $application['source'] }}</dd></div>
                <div class="grid gap-1 py-3 sm:grid-cols-[9rem_minmax(0,1fr)]"><dt class="text-sm text-gray-500">Ngày nộp</dt><dd class="text-sm font-medium tabular-nums text-gray-950 dark:text-white">{{ $application['appliedAt'] }}</dd></div>
            </dl>
        </section>
    </div>
    <section class="flex min-w-0 flex-col gap-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 dark:border-white/10 dark:bg-white/5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-gray-500 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase text-gray-500">CV được nộp cùng ứng tuyển</p>
                <p class="mt-1 truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $cv['name'] }}</p>
            </div>
        </div>
        @if ($cv['url'])
            <x-filament::button tag="a" :href="$cv['url']" target="_blank" color="gray" icon="heroicon-o-arrow-top-right-on-square">Mở CV</x-filament::button>
        @else
            <span class="text-sm text-gray-500">Chưa có file CV</span>
        @endif
    </section>
    <section>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-start gap-2">
                <x-filament::icon icon="heroicon-o-sparkles" class="mt-0.5 h-5 w-5 shrink-0 text-primary-500" />
                <div>
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Căn cứ sàng lọc từ AI</h3>
                    <p class="mt-1 text-sm text-gray-500">Thông tin tham khảo, quyết định tuyển dụng vẫn do người phụ trách xác nhận.</p>
                </div>
            </div>
            @if ($ai['available'])
                <div class="flex items-center gap-2">
                    @if ($ai['score'] !== null)<x-filament::badge :color="$ai['tone']">{{ $ai['score'] }}/100</x-filament::badge>@endif
                    <x-filament::badge color="gray">{{ $ai['recommendation'] }}</x-filament::badge>
                </div>
            @endif
        </div>
        @if ($ai['available'])
            <p class="mt-4 text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $ai['summary'] }}</p>
            <div class="mt-4 grid gap-5 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Điểm phù hợp</p>
                    <ul class="mt-2 space-y-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        @forelse ($ai['strengths'] as $item)<li class="flex gap-2"><span class="text-success-500">•</span><span>{{ $item }}</span></li>@empty<li>Chưa có nội dung.</li>@endforelse
                    </ul>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Nội dung cần làm rõ</p>
                    <ul class="mt-2 space-y-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        @forelse ($ai['gaps'] as $item)<li class="flex gap-2"><span class="text-warning-500">•</span><span>{{ $item }}</span></li>@empty<li>Chưa có nội dung.</li>@endforelse
                    </ul>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-400">Phân tích lúc {{ $ai['analyzedAt'] }}</p>
        @else
            <div class="mt-4 flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
                <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 shrink-0 text-gray-400" />
                <span>Chưa có kết quả phân tích CV hoàn tất.</span>
            </div>
        @endif
    </section>
</div>
