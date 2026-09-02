<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_candidate_templates_use_the_new_email_structure(): void
    {
        User::factory()->create();

        $this->seed(EmailTemplateSeeder::class);

        $templates = EmailTemplate::query()
            ->whereIn('type', ['auto_reply', 'interview_invite', 'offer'])
            ->where('is_active', true)
            ->get()
            ->keyBy('type');

        $this->assertCount(3, $templates);
        $this->assertStringContainsString('info-card', $templates['auto_reply']->body);
        $this->assertStringContainsString('{{round_name}}', $templates['interview_invite']->subject);
        $this->assertStringContainsString('{{round_name}}', $templates['interview_invite']->body);
        $this->assertStringContainsString('{{duration_minutes}}', $templates['interview_invite']->body);
        $this->assertStringContainsString('info-card', $templates['offer']->body);
        $this->assertStringContainsString('{{offer_response_actions}}', $templates['offer']->body);
    }
}
