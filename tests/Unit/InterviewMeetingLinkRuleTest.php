<?php

namespace Tests\Unit;

use App\Rules\InterviewMeetingLink;
use App\Services\InterviewMeetingLinkValidator;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class InterviewMeetingLinkRuleTest extends TestCase
{
    public function test_it_validates_meeting_links_without_a_filament_closure(): void
    {
        $rule = new InterviewMeetingLink(app(InterviewMeetingLinkValidator::class));

        $this->assertTrue(Validator::make([
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        ], [
            'meeting_link' => [$rule],
        ])->passes());

        $this->assertTrue(Validator::make([
            'meeting_link' => 'https://meet.google.com/home',
        ], [
            'meeting_link' => [$rule],
        ])->fails());
    }

    public function test_filament_can_resolve_the_rule_without_an_unresolvable_attribute_parameter(): void
    {
        $input = TextInput::make('meeting_link')
            ->rules([new InterviewMeetingLink(app(InterviewMeetingLinkValidator::class))]);

        $this->assertTrue(collect($input->getValidationRules())
            ->contains(fn (mixed $rule): bool => $rule instanceof InterviewMeetingLink));
    }
}
