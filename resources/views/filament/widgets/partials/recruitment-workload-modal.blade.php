<div class="space-y-4">
    @if (! empty($previewRows))
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Hiển thị {{ count($previewRows) }} mục cần xem gần nhất.
            </div>

            @if (! empty($item['url']))
                <a
                    href="{{ $item['url'] }}"
                    class="inline-flex w-fit items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                >
                    Xem danh sách đầy đủ
                </a>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
            @foreach ($previewRows as $row)
                <a
                    href="{{ $row['url'] }}"
                    class="group grid gap-3 border-b border-gray-100 px-4 py-3 transition last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5 md:grid-cols-[minmax(0,1fr)_10rem_6rem]"
                >
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                {{ $row['status'] }}
                            </span>
                            <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $row['title'] }}
                            </div>
                        </div>

                        <div class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">
                            {{ $row['description'] }}
                        </div>
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400 md:text-right">
                        {{ $row['meta'] }}
                    </div>

                    <div class="flex items-center text-sm font-medium text-primary-600 opacity-100 dark:text-primary-400 md:justify-end md:opacity-0 md:transition md:group-hover:opacity-100">
                        Mở chi tiết
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="rounded-xl bg-gray-50 px-4 py-8 text-center dark:bg-white/5">
            <div class="text-sm font-medium text-gray-900 dark:text-white">
                Nhóm việc này đang trống
            </div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Không còn dữ liệu cần xử lý trong phạm vi hiện tại.
            </div>
        </div>
    @endif
</div>
