<?php

namespace Tests\Unit;

use App\Services\InterviewMeetingLinkValidator;
use Tests\TestCase;

class InterviewMeetingLinkValidatorTest extends TestCase
{
    public function test_it_accepts_supported_meeting_links_and_secure_custom_links(): void
    {
        $validator = app(InterviewMeetingLinkValidator::class);

        $this->assertTrue($validator->isValid('https://meet.google.com/abc-defg-hij'));
        $this->assertTrue($validator->isValid('https://us02web.zoom.us/j/123456789'));
        $this->assertTrue($validator->isValid('https://teams.microsoft.com/l/meetup-join/19%3ameeting_demo'));
        $this->assertTrue($validator->isValid('https://interview.example.edu.vn/room/123'));
    }

    public function test_it_rejects_home_pages_insecure_and_malformed_meeting_links(): void
    {
        $validator = app(InterviewMeetingLinkValidator::class);

        $this->assertFalse($validator->isValid('https://meet.google.com/home'));
        $this->assertFalse($validator->isValid('http://meet.google.com/abc-defg-hij'));
        $this->assertFalse($validator->isValid('meet.google.com/abc-defg-hij'));
        $this->assertFalse($validator->isValid('https://zoom.us/home'));
    }
}
