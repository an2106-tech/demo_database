<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Jobs\ProcessApplicationCvText;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\CvTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ProcessApplicationCvTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_updates_application_and_submission_snapshots(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Can Tho',
            'code' => 'CT-CV-TEXT',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create(['role' => 'hr', 'branch_id' => $branch->id, 'is_active' => true]);
        $candidate = Candidate::query()->create([
            'name' => 'CV Queue Candidate',
            'email' => 'cv-queue@example.com',
            'phone' => '0901234567',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giang vien Cong nghe thong tin',
            'slug' => 'cv-queue-job',
            'description' => 'CV queue test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'applications/cv-queue.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'branch_id' => $branch->id,
        ]);
        CandidateJobSubmission::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => $application->cv_path,
            'apply_method' => 'cv',
        ]);

        $extractor = $this->mock(CvTextExtractor::class, function (MockInterface $mock): void {
            $mock->shouldReceive('extractFromPublicPath')
                ->once()
                ->with('applications/cv-queue.pdf')
                ->andReturn('Noi dung CV da duoc trich xuat.');
        });

        (new ProcessApplicationCvText($application->id))->handle($extractor);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'cv_text_snapshot' => 'Noi dung CV da duoc trich xuat.',
        ]);
        $this->assertDatabaseHas('candidate_job_submissions', [
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_text_snapshot' => 'Noi dung CV da duoc trich xuat.',
        ]);
    }
}
