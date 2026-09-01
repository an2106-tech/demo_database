<div class="space-y-4">
    @forelse ($interviews as $interview)
        <details class="group overflow-hidden rounded-lg border border-gray-200 bg-white transition open:border-primary-200 dark:border-white/10 dark:bg-transparent dark:open:border-primary-500/30" @if ($interview['isLatest']) open @endif>
            <summary class="flex cursor-pointer list-none flex-col gap-3 bg-gray-50 px-4 py-4 outline-none transition hover:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase text-gray-500">Vòng {{ $interview['roundNumber'] }}</span>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $interview['roundName'] }}</h3>
                        @if ($interview['isLatest'])<x-filament::badge color="gray">Vòng hiện tại</x-filament::badge>@endif
                    </div>
                    <p class="mt-1 text-sm tabular-nums text-gray-500">{{ $interview['scheduledAt'] }} · {{ $interview['type'] }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-filament::badge :color="$interview['statusTone']">{{ $interview['status'] }}</x-filament::badge>
                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-5 w-5 text-gray-400 transition group-open:rotate-180" />
                </div>
            </summary>
            <div class="space-y-6 px-4 py-5">
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div><dt class="text-xs text-gray-500">Thời lượng</dt><dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $interview['duration'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Địa điểm / liên kết</dt><dd class="mt-1 break-words text-sm font-medium text-gray-900 dark:text-white">{{ $interview['location'] }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Thư mời</dt><dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $interview['inviteStatus'] }}@if ($interview['inviteSentAt'] !== '-') · {{ $interview['inviteSentAt'] }}@endif</dd></div>
                    <div><dt class="text-xs text-gray-500">Mẫu đánh giá</dt><dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $interview['templateName'] }}</dd></div>
                </dl>
                <div class="grid gap-4 border-y border-gray-200 py-4 dark:border-white/10 sm:grid-cols-3">
                    <div><p class="text-xs text-gray-500">Tiến độ phiếu</p><p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ $interview['progress'] }}</p></div>
                    <div><p class="text-xs text-gray-500">Điểm hội đồng</p><p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ $interview['panelAverage'] }}</p></div>
                    <div><p class="text-xs text-gray-500">Kết quả vòng</p><p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ $interview['result'] }}</p></div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Hội đồng đánh giá</h4>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[42rem] text-left text-sm">
                            <thead class="border-b border-gray-200 text-xs text-gray-500 dark:border-white/10"><tr><th class="pb-2 font-medium">Thành viên</th><th class="pb-2 font-medium">Trạng thái</th><th class="pb-2 font-medium">Điểm</th><th class="pb-2 font-medium">Kết luận</th><th class="pb-2 font-medium">Gửi lúc</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @forelse ($interview['participants'] as $participant)
                                    <tr>
                                        <td class="py-3"><p class="font-medium text-gray-950 dark:text-white">{{ $participant['name'] }}</p><p class="text-xs text-gray-500">{{ $participant['role'] }}</p></td>
                                        <td class="py-3"><x-filament::badge :color="$participant['statusTone']">{{ $participant['status'] }}</x-filament::badge></td>
                                        <td class="py-3 font-medium text-gray-900 dark:text-white">{{ $participant['average'] ?: '-' }}</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300">{{ $participant['conclusion'] }}</td>
                                        <td class="py-3 tabular-nums text-gray-500">{{ $participant['submittedAt'] }}</td>
                                    </tr>
                                    @if ($participant['notes'])<tr><td colspan="5" class="pb-3 text-sm leading-6 text-gray-600 dark:text-gray-300"><span class="font-medium">Nhận xét:</span> {{ $participant['notes'] }}</td></tr>@endif
                                @empty
                                    <tr><td colspan="5" class="py-4 text-gray-500">Chưa phân công người đánh giá.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($interview['finalizedAt'] !== '-' || $interview['finalNotes'])
                    <div class="rounded-lg bg-success-50 px-4 py-3 text-sm dark:bg-success-500/10">
                        <p class="font-semibold text-success-800 dark:text-success-300">Kết quả đã được chốt</p>
                        <p class="mt-1 leading-6 text-success-700 dark:text-success-400">{{ $interview['finalizedBy'] }} · {{ $interview['finalizedAt'] }}@if ($interview['finalNotes']) · {{ $interview['finalNotes'] }}@endif</p>
                    </div>
                @endif
            </div>
        </details>
    @empty
        <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-4 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
            <x-filament::icon icon="heroicon-o-calendar" class="h-5 w-5 shrink-0 text-gray-400" />
            <span>Ứng tuyển chưa có vòng phỏng vấn nào.</span>
        </div>
    @endforelse
</div>
