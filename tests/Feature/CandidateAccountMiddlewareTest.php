<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class CandidateAccountMiddlewareTest extends TestCase
{
    public function test_hr_without_candidate_account_is_redirected_to_activation(): void
    {
        $user = new User([
            'name' => 'HR',
            'email' => 'hr-mw@example.com',
            'role' => 'hr',
            'metadata' => ['account_types' => ['employer']],
        ]);
        $user->id = 10;

        $this->actingAs($user);

        $response = $this->get(route('candidates.submit_resume'));

        $response->assertRedirect();
        $response->assertRedirectToRoute('auth.sign_up', [
            'role' => 'candidate',
            'next_route' => 'candidates.submit_resume',
        ]);
    }

    public function test_candidate_can_access_candidate_pages(): void
    {
        $user = new User([
            'name' => 'Candidate',
            'email' => 'cand-mw@example.com',
            'role' => 'candidate',
            'metadata' => ['account_types' => ['candidate']],
        ]);
        $user->id = 11;

        $this->actingAs($user);

        $this->get(route('candidates.submit_resume'))->assertStatus(200);
    }
}

