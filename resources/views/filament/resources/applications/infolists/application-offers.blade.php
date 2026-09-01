<div class="space-y-4">
    @forelse ($offers as $offer)
        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-transparent">
            <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-white/10 dark:bg-white/5 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-primary-500 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                        <x-filament::icon icon="heroicon-o-document-check" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tabular-nums text-gray-500">{{ $offer['code'] }}</p>
                        <h3 class="mt-1 text-base font-semibold text-gray-950 dark:text-white">Đề nghị tuyển dụng</h3>
                    </div>
                </div>
                <x-filament::badge :color="$offer['statusTone']">{{ $offer['status'] }}</x-filament::badge>
            </div>
            <div class="grid gap-6 px-4 py-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)]">
                <div class="min-w-0">
                    <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Điều khoản đề nghị</h4>
                    <dl class="mt-3 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div><dt class="text-xs text-gray-500">Mức lương</dt><dd class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ $offer['salary'] }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Ngày nhận việc</dt><dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $offer['startDate'] }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Thời gian thử việc</dt><dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $offer['probation'] }}</dd></div>
                        <div><dt class="text-xs text-gray-500">Hạn phản hồi</dt><dd class="mt-1 text-sm font-medium tabular-nums text-gray-900 dark:text-gray-100">{{ $offer['expiresAt'] }}</dd></div>
                    </dl>
                    <dl class="mt-5 divide-y divide-gray-100 border-y border-gray-200 dark:divide-white/5 dark:border-white/10">
                        <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sm text-gray-500">Mẫu thư mời</dt><dd class="text-right text-sm font-medium text-gray-950 dark:text-white">{{ $offer['template'] }}</dd></div>
                        <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sm text-gray-500">PDF thư mời</dt><dd class="text-right text-sm font-medium text-gray-950 dark:text-white">{{ $offer['hasPdf'] ? 'Đã tạo' : 'Chưa tạo' }}</dd></div>
                    </dl>
                </div>

                <div class="border-t border-gray-200 pt-5 dark:border-white/10 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                    <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Tiến trình xử lý</h4>
                    <ol class="mt-4 space-y-4">
                        @foreach ([
                            ['label' => 'Gửi duyệt', 'time' => $offer['requestedAt'], 'meta' => null],
                            ['label' => 'Phê duyệt', 'time' => $offer['approvedAt'], 'meta' => $offer['approvedBy'] !== '-' ? $offer['approvedBy'] : null],
                            ['label' => 'Gửi ứng viên', 'time' => $offer['sentAt'], 'meta' => null],
                            ['label' => 'Ứng viên phản hồi', 'time' => $offer['responseAt'], 'meta' => null],
                        ] as $step)
                            <li class="flex items-start gap-3">
                                <span @class([
                                    'mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full',
                                    'bg-success-500' => $step['time'] !== '-',
                                    'bg-gray-200 dark:bg-white/15' => $step['time'] === '-',
                                ])></span>
                                <div class="min-w-0">
                                    <p @class([
                                        'text-sm font-medium',
                                        'text-gray-950 dark:text-white' => $step['time'] !== '-',
                                        'text-gray-400' => $step['time'] === '-',
                                    ])>{{ $step['label'] }}</p>
                                    <p class="mt-0.5 text-xs tabular-nums text-gray-500">{{ $step['time'] !== '-' ? $step['time'] : 'Chưa thực hiện' }}@if ($step['meta']) · {{ $step['meta'] }}@endif</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
            @if ($offer['approvalNotes'] || $offer['declinedReason'])
                <div class="border-t border-gray-200 bg-warning-50 px-4 py-3 text-sm leading-6 text-gray-700 dark:border-white/10 dark:bg-warning-500/10 dark:text-gray-300">
                    @if ($offer['approvalNotes'])<p><span class="font-semibold text-gray-950 dark:text-white">Yêu cầu chỉnh sửa:</span> {{ $offer['approvalNotes'] }}</p>@endif
                    @if ($offer['declinedReason'])<p @class(['mt-2' => $offer['approvalNotes']])><span class="font-semibold text-gray-950 dark:text-white">Lý do ứng viên từ chối:</span> {{ $offer['declinedReason'] }}</p>@endif
                </div>
            @endif
        </section>
    @empty
        <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-4 text-sm text-gray-600 dark:bg-white/5 dark:text-gray-300">
            <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5 shrink-0 text-gray-400" />
            <span>Ứng tuyển chưa có đề nghị tuyển dụng.</span>
        </div>
    @endforelse
</div>
