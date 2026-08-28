<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class OutboundMailQueue
{
    public function queue(string|array $recipients, Mailable $mailable): void
    {
        $delay = $this->reserveDelaySeconds();

        Mail::to($recipients)->queue(
            $mailable
                ->afterCommit()
                ->delay($delay),
        );
    }

    private function reserveDelaySeconds(): int
    {
        $spacing = max(1, (int) config('mail.queue_spacing_seconds', 2));

        return Cache::lock('outbound-mail-schedule-lock', 10)->block(5, function () use ($spacing): int {
            $now = now()->getTimestamp();
            $nextAt = max($now, (int) Cache::get('outbound-mail-next-at', $now));

            Cache::put('outbound-mail-next-at', $nextAt + $spacing, now()->addHour());

            return max(0, $nextAt - $now);
        });
    }
}
