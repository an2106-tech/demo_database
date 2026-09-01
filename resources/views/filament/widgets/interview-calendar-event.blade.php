<div
    class="interview-calendar-event-content min-w-0 overflow-hidden px-1.5"
    x-bind:title="event.extendedProps.tooltip"
>
    <div class="interview-calendar-event-compact flex min-w-0 items-center gap-1 text-[11px] font-semibold leading-4">
        <span class="shrink-0" x-text="event.extendedProps.compactRound"></span>
        <span class="opacity-70">·</span>
        <span class="min-w-0 truncate" x-text="event.extendedProps.candidate"></span>
        <span class="opacity-70">·</span>
        <span class="shrink-0" x-text="event.extendedProps.compactStatus"></span>
    </div>

    <div class="interview-calendar-event-list min-w-0 py-1">
        <div class="flex min-w-0 items-center gap-1">
            <span
                class="shrink-0 text-xs font-semibold"
                x-text="timeText"
            ></span>
            <span class="truncate text-xs font-semibold" x-text="event.extendedProps.candidate"></span>
        </div>
        <p class="truncate text-xs opacity-80">
            <span x-text="event.extendedProps.round"></span>
            <span> · </span>
            <span x-text="event.extendedProps.job"></span>
        </p>
        <p class="truncate text-xs font-semibold">
            <span x-text="event.extendedProps.status"></span>
            <span> · </span>
            <span x-text="event.extendedProps.action"></span>
        </p>
    </div>
</div>
