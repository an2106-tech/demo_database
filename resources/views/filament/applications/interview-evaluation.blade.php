@php
    /** @var \App\Models\Application $record */
    $interview = $record->interviews()->latest('id')->first();
    $scorecards = $interview ? $interview->scorecards()->with('evaluator')->get() : collect();
    
    $currentUserScorecard = $scorecards->firstWhere('evaluator_id', auth()->id());
    
    // Tính điểm trung bình từ tất cả các evaluator (trừ người hiện tại nếu lần đầu)
    $otherScorecards = $scorecards->where('evaluator_id', '!=', auth()->id());
    $averageScore = $otherScorecards->count() > 0 
        ? round($otherScorecards->avg('average_score'), 2)
        : null;
@endphp

<div class="space-y-6">
    <!-- Phần thông tin phỏng vấn -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Thông tin phỏng vấn</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Ứng viên</div>
                <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->snapshotCandidateName() }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Vị trí</div>
                <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $record->job?->title ?? '-' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Thời gian phỏng vấn</div>
                <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">
                    {{ $interview?->scheduled_at?->format('H:i, d/m/Y') ?? '-' }}
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Người phỏng vấn</div>
                <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ $interview?->interviewer?->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Phần Điểm trung bình từ các evaluator khác -->
    @if ($otherScorecards->count() > 0)
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Điểm từ các người đánh giá khác</h3>
        
        <div class="mb-4 p-4 bg-white dark:bg-gray-800 rounded border">
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Điểm trung bình</div>
                <div class="text-4xl font-bold text-primary-600">{{ $averageScore ?? '-' }}/10</div>
            </div>
        </div>

        <div class="space-y-4">
            @foreach ($otherScorecards as $scorecard)
            <div class="p-4 bg-white dark:bg-gray-800 rounded border">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $scorecard->evaluator?->name ?? 'Người đánh giá' }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $scorecard->created_at?->format('H:i, d/m/Y') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-primary-600">{{ $scorecard->average_score ?? '-' }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">/10</div>
                    </div>
                </div>

                <!-- Chi tiết tiêu chí -->
                @if ($scorecard->criteria && is_array($scorecard->criteria))
                <div class="mb-3">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tiêu chí chấm</div>
                    <div class="space-y-1">
                        @foreach ($scorecard->criteria as $criterion)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $criterion['name'] ?? '-' }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $criterion['score'] ?? '-' }}/10</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Nhận xét -->
                @if ($scorecard->notes)
                <div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nhận xét</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 italic">{{ $scorecard->notes }}</div>
                </div>
                @endif

                <!-- Kết luận của người khác -->
                @if ($scorecard->conclusion)
                <div class="mt-2 pt-2 border-t">
                    <span class="text-xs font-medium px-2 py-1 rounded-full
                        {{ $scorecard->conclusion === 'pass' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                        {{ $scorecard->conclusion === 'hold' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                        {{ $scorecard->conclusion === 'fail' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                    ">
                        @switch($scorecard->conclusion)
                            @case('pass')
                                ✓ Đề xuất: Đạt
                                @break
                            @case('hold')
                                ⊙ Đề xuất: Cân nhắc
                                @break
                            @case('fail')
                                ✗ Đề xuất: Từ chối
                                @break
                        @endswitch
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Phần Đánh giá của bạn -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Đánh giá của {{ auth()->user()?->name ?? 'bạn' }}
        </h3>
        
        @if ($currentUserScorecard)
        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800 mb-4">
            <div class="text-sm font-medium text-green-800 dark:text-green-400">✓ Bạn đã đánh giá hồ sơ này</div>
        </div>
        @endif
    </div>
</div>
