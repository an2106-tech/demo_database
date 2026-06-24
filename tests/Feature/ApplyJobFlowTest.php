<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\ApplyJob;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ApplyJobFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_apply_to_job_with_cv(): void
    {
        Mail::fake();
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $candidate = User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => 'candidate-apply@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);
        Candidate::query()->create([
            'user_id' => $candidate->id,
            'name' => 'Nguyen Van A',
            'email' => 'candidate-apply@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/existing/cv.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer',
            'description' => 'Build and maintain Laravel applications.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        $this->actingAs($candidate);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'Nguyen Van A')
            ->set('email', 'candidate-apply@example.com')
            ->set('phone', '0901234567')
            ->set('experience_years', 2)
            ->set('profile_title', 'PHP Developer')
            ->set('career_objective', 'Muon phat trien san pham tuyen dung on dinh.')
            ->set('cv', UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertSet('showSuccessModal', true);

        $this->assertDatabaseHas('applications', [
            'job_id' => $job->id,
            'branch_id' => $branch->id,
            'status' => StatusApplicationEnum::NEW->value,
            'apply_method' => 'cv',
        ]);
        $this->assertDatabaseHas('candidate_job_submissions', [
            'job_id' => $job->id,
            'apply_method' => 'cv',
        ]);

        $application = Application::query()->first();
        $submission = CandidateJobSubmission::query()->first();

        $this->assertNotNull($application?->candidate_id);
        $this->assertSame($application->candidate_id, $submission?->candidate_id);
        $this->assertNotEmpty($submission?->cv_path);
        $this->assertSame('Nguyen Van A', data_get($application->profile_snapshot, 'candidate.name'));
        $this->assertSame('candidate-apply@example.com', data_get($application->profile_snapshot, 'candidate.email'));
        $this->assertSame('PHP Developer', data_get($application->profile_snapshot, 'resume.profile_title'));
        $this->assertSame('Muon phat trien san pham tuyen dung on dinh.', data_get($application->profile_snapshot, 'resume.career_objective'));
        $this->assertNotEmpty(data_get($application->profile_snapshot, 'cv.path'));
        $this->assertSame($application->profile_snapshot, $submission?->profile_snapshot);
    }

    public function test_hr_without_candidate_account_cannot_apply_as_candidate(): void
    {
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
            'metadata' => ['account_types' => ['employer']],
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer-hr-guard',
            'description' => 'Build and maintain Laravel applications.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'HR User')
            ->set('email', $hr->email)
            ->set('cv', UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasErrors('account');

        $this->assertDatabaseMissing('applications', [
            'job_id' => $job->id,
        ]);
        $this->assertDatabaseMissing('candidate_job_submissions', [
            'job_id' => $job->id,
        ]);
        $this->assertFalse(Candidate::query()->where('user_id', $hr->id)->exists());
    }
}
