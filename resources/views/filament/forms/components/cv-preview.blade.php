@php
    use Illuminate\Support\Facades\Storage;

    $record = $getRecord();
    $cvState = $get('cv_path');

    if (is_array($cvState)) {
        $cvState = reset($cvState) ?: null;
    }

    $cvPath = is_string($cvState) && $cvState !== ''
        ? $cvState
        : ($record?->submittedCvPath() ?? $record?->cv_path ?? null);

    $cvUrl = null;

    $isTemporaryUpload = is_object($cvState) && method_exists($cvState, 'temporaryUrl');

    if ($isTemporaryUpload) {
        $cvPath = method_exists($cvState, 'getClientOriginalName')
            ? $cvState->getClientOriginalName()
            : $cvPath;
    } elseif ($cvPath && Storage::disk('public')->exists($cvPath)) {
        $cvUrl = route('public-file.preview', ['path' => $cvPath]);
    }

    $ext = $cvPath ? strtolower(pathinfo($cvPath, PATHINFO_EXTENSION)) : null;
    $isPdf = $ext === 'pdf';
    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
@endphp

<div class="w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">

    @if ($isTemporaryUpload)
        <div class="flex flex-col items-center justify-center gap-3 p-10 text-center" style="min-height: 24rem;">
            <x-heroicon-o-clock style="width:3.5rem;height:3.5rem;" class="text-gray-300 dark:text-gray-600" />
            <p class="text-sm text-gray-500 dark:text-gray-400">
                CV mới đã được chọn. Bấm Lưu để xem trước bản đã tải lên.
            </p>
        </div>

    @elseif ($cvUrl && $isPdf)
        <iframe
            src="{{ $cvUrl }}"
            title="Xem trước CV"
            class="w-full"
            style="height: calc(100vh - 12rem); min-height: 24rem; width: 100%; border: none;"
        ></iframe>

    @elseif ($cvUrl && $isImg)
        <div class="overflow-auto" style="max-height: calc(100vh - 12rem); min-height: 24rem;">
            <img src="{{ $cvUrl }}" alt="CV" class="w-full object-contain" />
        </div>

    @elseif ($cvUrl)
        <div class="flex flex-col items-center justify-center gap-4 p-10 text-center" style="min-height: 24rem;">
            <x-heroicon-o-document-text style="width:3.5rem;height:3.5rem;" class="text-gray-400" />
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Định dạng .{{ strtoupper($ext) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Không thể xem trực tiếp trong trình duyệt
                </p>
            </div>
        </div>

    @else
        <div class="flex flex-col items-center justify-center gap-3 p-10 text-center" style="min-height: 24rem;">
            <x-heroicon-o-document style="width:3.5rem;height:3.5rem;" class="text-gray-300 dark:text-gray-600" />
            <p class="text-sm text-gray-400 dark:text-gray-500">
                Không tìm thấy CV để xem trước
            </p>
        </div>
    @endif

</div>
