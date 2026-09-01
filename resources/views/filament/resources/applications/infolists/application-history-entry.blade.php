<div class="relative pb-5 last:pb-0">
    <span class="absolute -left-[1.57rem] top-1.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-gray-400 dark:border-gray-900"></span>
    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $history['from'] }} → {{ $history['to'] }}</p>
        <span class="shrink-0 text-xs tabular-nums text-gray-500">{{ $history['time'] }}</span>
    </div>
    <p class="mt-1 text-xs text-gray-500">{{ $history['actor'] }}@if ($history['actorMeta']) · {{ $history['actorMeta'] }}@endif</p>
    @if ($history['comment'])<p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $history['comment'] }}</p>@endif
</div>
