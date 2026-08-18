<?php

namespace App\Rules;

use App\Services\InterviewMeetingLinkValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InterviewMeetingLink implements ValidationRule
{
    public function __construct(
        private readonly InterviewMeetingLinkValidator $meetingLinkValidator,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value) && ! $this->meetingLinkValidator->isValid((string) $value)) {
            $fail('Dùng link họp https hợp lệ, ví dụ Google Meet/Zoom/Teams.');
        }
    }
}
