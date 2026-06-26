<?php

namespace Tests\Feature;

use App\Livewire\Client\CandidateProfile;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_profile_can_save_personal_section_without_cv(): void
    {
        $user = $this->candidateUser();
        $candidate = $this->candidateFor($user, [
            'phone' => null,
            'cv_file' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->set('name', 'Nguyen Van A')
            ->set('email', 'candidate-profile@example.com')
            ->set('phone', '0912345678')
            ->call('saveSection', 'career-objective')
            ->assertHasNoErrors()
            ->assertSet('activeSection', 'career-objective');

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'name' => 'Nguyen Van A',
            'email' => 'candidate-profile@example.com',
            'phone' => '0912345678',
            'cv_file' => null,
        ]);
    }

    public function test_candidate_profile_rejects_invalid_phone_number(): void
    {
        $user = $this->candidateUser();
        $candidate = $this->candidateFor($user, ['phone' => '12345']);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->set('phone', '12345')
            ->call('save')
            ->assertHasErrors(['phone']);

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'phone' => '12345',
        ]);
    }

    public function test_candidate_profile_requires_existing_or_uploaded_cv(): void
    {
        $user = $this->candidateUser();
        $candidate = $this->candidateFor($user, [
            'phone' => '0901234567',
            'cv_file' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->set('phone', '0901234567')
            ->call('save')
            ->assertHasErrors(['cv']);

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'cv_file' => null,
        ]);
    }

    public function test_candidate_profile_saves_when_required_contact_and_cv_are_present(): void
    {
        $user = $this->candidateUser();
        $candidate = $this->candidateFor($user, [
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->set('name', 'Nguyen Van A')
            ->set('email', 'candidate-profile@example.com')
            ->set('phone', '0912345678')
            ->set('profile_title', 'Backend Developer')
            ->set('career_objective', 'Build reliable hiring systems.')
            ->set('desired_job.position', 'Laravel Developer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'name' => 'Nguyen Van A',
            'email' => 'candidate-profile@example.com',
            'phone' => '0912345678',
        ]);

        $this->assertDatabaseHas('candidate_resumes', [
            'candidate_id' => $candidate->id,
            'profile_title' => 'Backend Developer',
            'career_objective' => 'Build reliable hiring systems.',
        ]);
    }

    public function test_candidate_profile_contact_email_does_not_change_login_email(): void
    {
        $user = $this->candidateUser([
            'email' => 'login-email@example.com',
        ]);
        $candidate = $this->candidateFor($user, [
            'email' => 'old-contact@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->set('name', 'Nguyen Van A')
            ->set('email', 'new-contact@example.com')
            ->set('phone', '0912345678')
            ->call('saveSection', 'career-objective')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'login-email@example.com',
            'name' => 'Nguyen Van A',
        ]);

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'email' => 'new-contact@example.com',
            'phone' => '0912345678',
        ]);

        app(\App\Services\CandidateAccountService::class)->resolveFor($user);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'login-email@example.com',
        ]);

        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'email' => 'new-contact@example.com',
        ]);
    }

    public function test_candidate_profile_save_section_reports_saved_section(): void
    {
        $user = $this->candidateUser();
        $this->candidateFor($user, [
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->set('activeSection', 'career-objective')
            ->set('career_objective', 'Build useful recruitment products.')
            ->call('saveSection', 'desired-job')
            ->assertHasNoErrors()
            ->assertSet('activeSection', 'desired-job')
            ->assertSet('lastSavedSectionLabel', 'Mục tiêu nghề nghiệp');
    }

    public function test_candidate_profile_switch_section_only_accepts_known_sections(): void
    {
        $user = $this->candidateUser();
        $this->candidateFor($user);

        $this->actingAs($user);

        Livewire::test(CandidateProfile::class)
            ->call('switchSection', 'skills')
            ->assertSet('activeSection', 'skills')
            ->call('switchSection', 'unknown-section')
            ->assertSet('activeSection', 'skills');
    }

    private function candidateUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Candidate Profile',
            'email' => 'candidate-profile@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ], $attributes));
    }

    private function candidateFor(User $user, array $attributes = []): Candidate
    {
        $candidate = Candidate::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ], $attributes));

        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
        ]);

        return $candidate;
    }
}
