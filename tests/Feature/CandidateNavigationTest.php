<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_messages_sidebar_uses_current_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('candidates.messages'))
            ->assertOk()
            ->assertSee(route('candidates.candidate_dashboard'), false)
            ->assertSee(route('candidates.candidate_profile'), false)
            ->assertSee(route('candidates.manage_jobs'), false)
            ->assertSee(route('candidates.earnings'), false)
            ->assertDontSee('candidate-dashboard.html', false)
            ->assertDontSee('message.html', false)
            ->assertDontSee('manage-jobs.html', false);
    }

    public function test_legacy_candidate_html_links_redirect_to_current_routes(): void
    {
        $this->get('/candidates/message.html')->assertRedirect('/candidates/messages');
        $this->get('/candidates/manage-jobs.html')->assertRedirect('/candidates/manage-jobs');
        $this->get('/candidates/candidate-earnings.html')->assertRedirect('/candidates/earnings');
        $this->get('/candidates/change-password.html')->assertRedirect('/candidates/change-password');
    }

    public function test_candidate_change_password_uses_candidate_sidebar(): void
    {
        $user = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('candidates.change_password'))
            ->assertOk()
            ->assertSee(route('candidates.change_password'), false)
            ->assertSee(route('candidates.messages'), false)
            ->assertSee(route('candidates.manage_jobs'), false)
            ->assertDontSee(route('employers.company_profile'), false)
            ->assertDontSee(route('employers.post_job'), false)
            ->assertDontSee(route('employers.application_pipeline'), false);
    }
}
