@php
    // Read directly from the saved record to avoid conflicting with FileUpload's state pipeline.
    // cv_path_preview isn't a model attribute, so $getState() would return null anyway.
    $record  = $getRecord();
    $cvPath  = $record?->cv_path ?? null;
    $cvUrl   = $cvPath ? asset('storage/' . $cvPath) : null;
    $ext     = $cvPath ? strtolower(pathinfo($cvPath, PATHINFO_EXTENSION)) : null;
    $isPdf   = $ext === 'pdf';
    $isImg   = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
@endphp

<div class="w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">

    @if ($cvUrl && $isPdf)
        {{-- PDF – inline preview via <iframe> --}}
        <iframe
            src="{{ $cvUrl }}"
            title="Xem trước CV"
            class="w-full"
            style="height: calc(100vh - 12rem); min-height: 24rem; width: 100%; border: none;"
        ></iframe>

    @elseif ($cvUrl && $isImg)
        {{-- Image file --}}
        <div class="overflow-auto" style="max-height: calc(100vh - 12rem); min-height: 24rem;">
            <img src="{{ $cvUrl }}" alt="CV" class="w-full object-contain" />
        </div>

    @elseif ($cvUrl)
        {{-- Other file types (docx, xlsx, …) – browser can't embed them --}}
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
            <a
                href="{{ $cvUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                <x-heroicon-o-arrow-down-tray style="width:1rem;height:1rem;" />
                Tải xuống
            </a>
        </div>

    @else
        {{-- No CV --}}
        <div class="flex flex-col items-center justify-center gap-3 p-10 text-center" style="min-height: 24rem;">
            <x-heroicon-o-document style="width:3.5rem;height:3.5rem;" class="text-gray-300 dark:text-gray-600" />
            <p class="text-sm text-gray-400 dark:text-gray-500">
                Chưa có CV để xem trước
            </p>
        </div>
    @endif

</div>
