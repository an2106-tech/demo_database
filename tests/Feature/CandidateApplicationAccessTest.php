<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\ApplicationDetail;
use App\Models\Application;
use App\Models\CandidateJobSubmission;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateApplicationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_manage_jobs_links_and_shows_guest_application_by_email(): void
    {
        [$job, $candidate, $application] = $this->guestApplication('linked-guest@example.com');
        $user = User::factory()->create([
            'name' => 'Linked Guest',
            'email' => 'linked-guest@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($user)
            ->get(route('candidates.manage_jobs'))
            ->assertOk()
            ->assertSee($job->title);

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'candidate_id' => $candidate->id,
        ]);
    }

    public function test_candidate_can_view_linked_guest_application_detail(): void
    {
        [$job, $candidate, $application] = $this->guestApplication('linked-detail@example.com');
        $candidate->update([
            'name' => 'Changed Current Candidate',
            'email' => 'linked-detail@example.com',
        ]);
        $user = User::factory()->create([
            'name' => 'Linked Detail',
            'email' => 'linked-detail@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($user)
            ->get(route('candidates.application_detail', ['application' => $application]))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee('Guest Candidate')
            ->assertSee('guest-snapshot-cv.pdf')
            ->assertDontSee('Changed Current Candidate');

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_candidate_cannot_view_another_candidates_application_detail(): void
    {
        [, , $application] = $this->guestApplication('other-owner@example.com');
        $user = User::factory()->create([
            'name' => 'Different Candidate',
            'email' => 'different-candidate@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);
        Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/different/cv.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('candidates.application_detail', ['application' => $application]))
            ->assertForbidden();
    }

    public function test_candidate_can_withdraw_own_application(): void
    {
        [$job, $candidate, $application] = $this->guestApplication('withdraw@example.com');
        $user = User::factory()->create([
            'name' => 'Withdraw Candidate',
            'email' => 'withdraw@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $this->actingAs($user);

        CandidateJobSubmission::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'apply_method' => 'cv',
            'profile_snapshot' => $application->profile_snapshot,
            'cv_path' => $application->cv_path,
        ]);

        Livewire::test(ApplicationDetail::class, ['application' => $application])
            ->call('withdraw')
            ->assertRedirect(route('candidates.manage_jobs'));

        $this->assertSoftDeleted('applications', [
            'id' => $application->id,
        ]);
        $this->assertSoftDeleted('candidate_job_submissions', [
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
        ]);
    }

    /**
     * @return array{0: RecruitmentJob, 1: Candidate, 2: Application}
     */
    private function guestApplication(string $email): array
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Application Access',
            'code' => 'AA',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Application Access Developer',
            'slug' => 'application-access-developer-' . uniqid(),
            'description' => 'Build application access flow.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Guest Candidate',
            'email' => $email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/guest/cv.pdf',
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/guest/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'branch_id' => $branch->id,
            'profile_snapshot' => [
                'candidate' => [
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                    'phone' => $candidate->phone,
                    'experience_years' => null,
                ],
                'resume' => [
                    'profile_title' => 'Snapshot Developer',
                ],
                'cv' => [
                    'path' => 'candidates/guest/cv.pdf',
                    'original_filename' => 'guest-snapshot-cv.pdf',
                ],
            ],
        ]);

        return [$job, $candidate, $application];
    }
}
