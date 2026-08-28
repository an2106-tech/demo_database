<?php

namespace Tests\Unit;

use App\Services\OutboundMailQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OutboundMailQueueTest extends TestCase
{
    public function test_queued_mail_is_spaced_to_respect_smtp_rate_limit(): void
    {
        Mail::fake();
        Cache::forget('outbound-mail-next-at');
        Carbon::setTestNow('2026-08-28 10:00:00');
        config()->set('mail.queue_spacing_seconds', 2);

        $queue = app(OutboundMailQueue::class);
        $queue->queue('first@example.com', new OutboundMailQueueTestMail);
        $queue->queue('second@example.com', new OutboundMailQueueTestMail);

        $mailables = Mail::queued(OutboundMailQueueTestMail::class)->values();

        $this->assertCount(2, $mailables);
        $this->assertSame(0, $mailables[0]->delay);
        $this->assertSame(2, $mailables[1]->delay);

        Carbon::setTestNow();
    }
}

class OutboundMailQueueTestMail extends Mailable
{
    use Queueable, SerializesModels;
}
