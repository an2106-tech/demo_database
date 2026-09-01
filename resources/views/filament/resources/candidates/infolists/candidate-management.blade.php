<dl class="space-y-4">
    <div>
        <dt class="text-sm text-gray-500 dark:text-gray-400">Tài khoản ứng viên</dt>
        <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $accountStatus }}</dd>
        @if ($accountEmail)
            <dd class="mt-1 break-words text-sm text-gray-500 dark:text-gray-400">{{ $accountEmail }}</dd>
        @endif
    </div>

    <div class="border-t border-gray-200 pt-4 dark:border-white/10">
        <dt class="text-sm text-gray-500 dark:text-gray-400">Tình trạng tuyển dụng</dt>
        <dd class="mt-2">
            <x-filament::badge :color="$isRestricted ? 'danger' : 'success'" :icon="$isRestricted ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle'">
                {{ $isRestricted ? 'Đang hạn chế' : 'Không hạn chế' }}
            </x-filament::badge>
        </dd>
        @if ($isRestricted)
            <dd class="mt-3 text-sm leading-6 text-gray-800 dark:text-gray-200">{{ $restrictionReason ?: 'Chưa ghi nhận lý do.' }}</dd>
            <dd class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $restrictedBy ?: 'Quản trị hệ thống' }} · {{ $restrictedAt }}
            </dd>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 border-t border-gray-200 pt-4 sm:grid-cols-2 dark:border-white/10">
        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Tạo hồ sơ</dt>
            <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $createdAt }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500 dark:text-gray-400">Cập nhật gần nhất</dt>
            <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $updatedAt }}</dd>
        </div>
    </div>
</dl>
