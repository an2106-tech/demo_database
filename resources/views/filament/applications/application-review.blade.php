@php
    /** @var \App\Models\Application $record */
    $applyMethod = $record->apply_method ?? null;
    $snapshot = is_array($record->profile_snapshot) ? $record->profile_snapshot : null;
    $candidateSnap = is_array($snapshot['candidate'] ?? null) ? $snapshot['candidate'] : [];
    $resumeSnap = is_array($snapshot['resume'] ?? null) ? $snapshot['resume'] : [];

    $cvUrl = $record->cv_path ? asset('storage/' . ltrim($record->cv_path, '/')) : null;

    $experiences = is_array($resumeSnap['experiences'] ?? null) ? $resumeSnap['experiences'] : [];
    $educations = is_array($resumeSnap['educations'] ?? null) ? $resumeSnap['educations'] : [];
    $certifications = is_array($resumeSnap['certifications'] ?? null) ? $resumeSnap['certifications'] : [];
    $languages = is_array($resumeSnap['languages'] ?? null) ? $resumeSnap['languages'] : [];
    $skills = is_array($resumeSnap['skills'] ?? null) ? $resumeSnap['skills'] : [];
    $achievements = is_array($resumeSnap['achievements'] ?? null) ? $resumeSnap['achievements'] : [];
    $activities = is_array($resumeSnap['activities'] ?? null) ? $resumeSnap['activities'] : [];
    $references = is_array($resumeSnap['references'] ?? null) ? $resumeSnap['references'] : [];

    $status = $record->status instanceof \App\Enums\StatusApplicationEnum ? $record->status : \App\Enums\StatusApplicationEnum::tryFrom((string) $record->status);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Hồ sơ bên trái -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Thông tin cơ bản -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Thông tin ứng viên</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Họ tên</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->candidate?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->candidate?->email ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Số điện thoại</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->candidate?->phone ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Kinh nghiệm</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->candidate?->experience_years ? $record->candidate->experience_years.' năm' : '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Thông tin công việc -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Vị trí ứng tuyển</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Công việc</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->job?->title ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Chi nhánh</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->job?->branch?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Ngày ứng tuyển</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->applied_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">CV</div>
                    <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">
                        @if ($cvUrl)
                            <a class="text-primary-600 hover:underline" href="{{ $cvUrl }}" target="_blank" rel="noopener">Mở CV</a>
                        @else
                            Không có
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Nội dung CV -->
        @if ($record->cv_text_snapshot)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Nội dung CV</h3>
            <div class="max-h-96 overflow-y-auto bg-gray-50 dark:bg-gray-700 p-4 rounded border">
                <pre class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-200">{{ $record->cv_text_snapshot }}</pre>
            </div>
        </div>
        @elseif ($cvUrl)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">CV</h3>
            <div class="mt-1">
                <a class="text-primary-600 hover:underline" href="{{ $cvUrl }}" target="_blank" rel="noopener">Mở CV để xem chi tiết</a>
            </div>
        </div>
        @endif

        <!-- Kinh nghiệm -->
        @if (!empty($experiences))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Kinh nghiệm làm việc</h3>
            <div class="space-y-4">
                @foreach($experiences as $exp)
                <div class="border-l-4 border-primary-500 pl-4">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $exp['position'] ?? '' }} tại {{ $exp['company'] ?? '' }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $exp['start_date'] ?? '' }} - {{ $exp['end_date'] ?? 'Hiện tại' }}</div>
                    <div class="mt-2 text-gray-700 dark:text-gray-300">{{ $exp['description'] ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Học vấn -->
        @if (!empty($educations))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Học vấn</h3>
            <div class="space-y-4">
                @foreach($educations as $edu)
                <div class="border-l-4 border-green-500 pl-4">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $edu['degree'] ?? '' }} - {{ $edu['field'] ?? '' }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $edu['school'] ?? '' }} ({{ $edu['start_year'] ?? '' }} - {{ $edu['end_year'] ?? '' }})</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Kỹ năng -->
        @if (!empty($skills))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Kỹ năng</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($skills as $skill)
                <span class="px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full text-sm">{{ $skill['name'] ?? '' }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Nút hành động bên phải -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quyết định</h3>
            <div class="space-y-3">
                @if ($status === \App\Enums\StatusApplicationEnum::NEW)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Xem xét hồ sơ và quyết định có chuyển sang sàng lọc hay không.</p>
                @elseif ($status === \App\Enums\StatusApplicationEnum::SCREENING)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Sau khi sàng lọc, mời phỏng vấn hoặc từ chối ứng viên.</p>
                @endif
            </div>
        </div>
    </div>
</div>