<?php

namespace Tests\Unit;

use App\Models\Interview;
use App\Services\InterviewCalendarService;
use Carbon\Carbon;
use Tests\TestCase;

class InterviewScheduleTimezoneTest extends TestCase
{
    public function test_interview_scheduled_at_keeps_vietnam_clock_time(): void
    {
        config([
            'app.timezone' => 'UTC',
            'app.interview_timezone' => 'Asia/Ho_Chi_Minh',
        ]);

        $interview = new Interview;
        $interview->scheduled_at = '2026-07-26 13:10';

        $this->assertSame('2026-07-26 13:10:00', $interview->getAttributes()['scheduled_at']);
        $this->assertSame('Asia/Ho_Chi_Minh', $interview->scheduled_at->timezoneName);
        $this->assertSame('2026-07-26 13:10', $interview->scheduled_at->format('Y-m-d H:i'));
    }

    public function test_interview_calendar_exports_vietnam_time_as_utc(): void
    {
        config([
            'app.timezone' => 'UTC',
            'app.interview_timezone' => 'Asia/Ho_Chi_Minh',
        ]);

        $interview = new Interview([
            'id' => 99,
            'scheduled_at' => Carbon::parse('2026-07-26 13:10', 'Asia/Ho_Chi_Minh'),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/demo',
        ]);

        $content = app(InterviewCalendarService::class)->buildContent($interview);

        $this->assertStringContainsString('DTSTART:20260726T061000Z', $content);
        $this->assertStringContainsString('DTEND:20260726T071000Z', $content);
    }
}
