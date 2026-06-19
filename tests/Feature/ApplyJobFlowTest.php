<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\ApplyJob;
use App\Models\Application;
use App\Models\Branch;
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
    }
}
