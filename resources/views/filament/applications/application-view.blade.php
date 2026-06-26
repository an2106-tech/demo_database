@php
    /** @var \App\Models\Application $record */
    $applyMethod = $record->apply_method ?? null;
    $snapshot = is_array($record->profile_snapshot) ? $record->profile_snapshot : null;
    $candidateSnap = is_array($snapshot['candidate'] ?? null) ? $snapshot['candidate'] : [];
    $resumeSnap = is_array($snapshot['resume'] ?? null) ? $snapshot['resume'] : [];

    $cvUrl = $record->submittedCvUrl();
    $cvName = $record->submittedCvName();

    $experiences = is_array($resumeSnap['experiences'] ?? null) ? $resumeSnap['experiences'] : [];
    $educations = is_array($resumeSnap['educations'] ?? null) ? $resumeSnap['educations'] : [];
    $certifications = is_array($resumeSnap['certifications'] ?? null) ? $resumeSnap['certifications'] : [];
    $languages = is_array($resumeSnap['languages'] ?? null) ? $resumeSnap['languages'] : [];
    $skills = is_array($resumeSnap['skills'] ?? null) ? $resumeSnap['skills'] : [];
    $achievements = is_array($resumeSnap['achievements'] ?? null) ? $resumeSnap['achievements'] : [];
    $activities = is_array($resumeSnap['activities'] ?? null) ? $resumeSnap['activities'] : [];
    $references = is_array($resumeSnap['references'] ?? null) ? $resumeSnap['references'] : [];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Cách nộp</div>
            <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                @if ($applyMethod === 'profile')
                    Hồ sơ
                @elseif ($applyMethod === 'cv')
                    CV
                @else
                    -
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">CV trong đơn</div>
            <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                @if ($cvUrl)
                    <a class="text-primary-600 hover:underline" href="{{ $cvUrl }}" target="_blank" rel="noopener">{{ $cvName ?: 'Mở CV' }}</a>
                @else
                    Không có
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Ngày ứng tuyển</div>
            <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">
                {{ $record->applied_at?->format('d/m/Y H:i') ?? '-' }}
            </div>
        </div>
    </div>

    @if ($applyMethod === 'profile')
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Thông tin hồ sơ (snapshot)</h3>
                <div class="text-xs text-gray-500 dark:text-gray-400">Lưu tại thời điểm ứng viên nộp</div>
            </div>

            @if (! $snapshot)
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">Không có dữ liệu snapshot.</div>
            @else
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Thông tin cơ bản</div>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Họ tên</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $candidateSnap['name'] ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $candidateSnap['email'] ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">SĐT</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $candidateSnap['phone'] ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-gray-500 dark:text-gray-400">Kinh nghiệm</dt>
                                <dd class="text-gray-900 dark:text-gray-100">
                                    @php $expYears = $candidateSnap['experience_years'] ?? null; @endphp
                                    {{ is_numeric($expYears) ? ($expYears . ' năm') : '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tiêu đề / Mục tiêu</div>
                        <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Tiêu đề hồ sơ</div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $resumeSnap['profile_title'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Mục tiêu nghề nghiệp</div>
                                <div class="whitespace-pre-wrap">{{ $resumeSnap['career_objective'] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Kinh nghiệm làm việc</div>
                        <div class="mt-3 space-y-3 text-sm">
                            @forelse ($experiences as $item)
                                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['position'] ?? '-' }} — {{ $item['company'] ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['from'] ?? '' }} {{ ($item['from'] ?? null) || ($item['to'] ?? null) ? '→' : '' }} {{ $item['to'] ?? '' }}</div>
                                    @if (! empty($item['description']))
                                        <div class="mt-2 whitespace-pre-wrap text-gray-700 dark:text-gray-200">{{ $item['description'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-gray-500 dark:text-gray-400">-</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Học vấn</div>
                        <div class="mt-3 space-y-3 text-sm">
                            @forelse ($educations as $item)
                                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['degree'] ?? '-' }}</div>
                                    <div class="text-gray-700 dark:text-gray-200">{{ $item['school'] ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['from'] ?? '' }} {{ ($item['from'] ?? null) || ($item['to'] ?? null) ? '→' : '' }} {{ $item['to'] ?? '' }}</div>
                                    @if (! empty($item['description']))
                                        <div class="mt-2 whitespace-pre-wrap text-gray-700 dark:text-gray-200">{{ $item['description'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-gray-500 dark:text-gray-400">-</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Kỹ năng / Ngôn ngữ</div>

                        <div class="mt-3">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Kỹ năng</div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($skills as $item)
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                        {{ $item['name'] ?? '-' }}@if (! empty($item['level'])) ({{ $item['level'] }}) @endif
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Ngôn ngữ</div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($languages as $item)
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                        {{ $item['name'] ?? '-' }}@if (! empty($item['level'])) ({{ $item['level'] }}) @endif
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Chứng chỉ / Thành tích</div>

                        <div class="mt-3 space-y-3 text-sm">
                            <div>
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Chứng chỉ</div>
                                <div class="mt-2 space-y-2">
                                    @forelse ($certifications as $item)
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['name'] ?? '-' }}</div>
                                            <div class="text-gray-700 dark:text-gray-200">{{ $item['issuer'] ?? '-' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['date'] ?? '-' }}</div>
                                        </div>
                                    @empty
                                        <div class="text-gray-500 dark:text-gray-400">-</div>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Thành tích</div>
                                <div class="mt-2 space-y-2">
                                    @forelse ($achievements as $item)
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['title'] ?? '-' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['date'] ?? '-' }}</div>
                                            @if (! empty($item['description']))
                                                <div class="mt-2 whitespace-pre-wrap text-gray-700 dark:text-gray-200">{{ $item['description'] }}</div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-gray-500 dark:text-gray-400">-</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Hoạt động khác</div>
                        <div class="mt-3 space-y-3 text-sm">
                            @forelse ($activities as $item)
                                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['title'] ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['from'] ?? '' }} {{ ($item['from'] ?? null) || ($item['to'] ?? null) ? '→' : '' }} {{ $item['to'] ?? '' }}</div>
                                    @if (! empty($item['description']))
                                        <div class="mt-2 whitespace-pre-wrap text-gray-700 dark:text-gray-200">{{ $item['description'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-gray-500 dark:text-gray-400">-</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Người tham khảo</div>
                        <div class="mt-3 space-y-3 text-sm">
                            @forelse ($references as $item)
                                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item['name'] ?? '-' }}</div>
                                    <div class="text-gray-700 dark:text-gray-200">
                                        {{ $item['position'] ?? '-' }} — {{ $item['company'] ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $item['phone'] ?? '' }}{{ (! empty($item['phone']) && ! empty($item['email'])) ? ' • ' : '' }}{{ $item['email'] ?? '' }}
                                    </div>
                                    @if (! empty($item['note']))
                                        <div class="mt-2 whitespace-pre-wrap text-gray-700 dark:text-gray-200">{{ $item['note'] }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-gray-500 dark:text-gray-400">-</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($applyMethod === 'cv' && is_string($record->cv_text_snapshot) && trim($record->cv_text_snapshot) !== '')
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <details>
                <summary class="cursor-pointer text-sm font-semibold text-gray-900 dark:text-gray-100">Xem text trích xuất từ CV</summary>
                <pre class="mt-3 max-h-96 overflow-auto whitespace-pre-wrap rounded-lg bg-gray-50 p-3 text-xs text-gray-800 dark:bg-gray-950 dark:text-gray-200">{{ $record->cv_text_snapshot }}</pre>
            </details>
        </div>
    @endif
</div>
