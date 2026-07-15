<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\AiChatContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatContextServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_context_is_limited_to_their_branch(): void
    {
        $branchA = $this->makeBranch('AI-A');
        $branchB = $this->makeBranch('AI-B');
        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branchA->id,
            'is_active' => true,
        ]);
        $otherHr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branchB->id,
            'is_active' => true,
        ]);

        $jobA = $this->makeJob($branchA, $hr, 'Backend nội bộ A');
        $jobB = $this->makeJob($branchB, $otherHr, 'Dữ liệu bí mật B');
        $candidateA = Candidate::query()->create(['name' => 'Ứng viên chi nhánh A', 'email' => 'a@example.com']);
        $candidateB = Candidate::query()->create(['name' => 'Ứng viên bí mật B', 'email' => 'b@example.com']);

        Application::query()->create([
            'job_id' => $jobA->id,
            'candidate_id' => $candidateA->id,
            'branch_id' => $branchA->id,
            'cv_path' => 'candidates/a/cv.pdf',
            'status' => 'cv_reviewing',
            'applied_at' => now(),
        ]);
        $mismatchedCandidate = Candidate::query()->create([
            'name' => 'Ứng viên lệch chi nhánh',
            'email' => 'mismatch@example.com',
        ]);
        Application::query()->create([
            'job_id' => $jobA->id,
            'candidate_id' => $mismatchedCandidate->id,
            'branch_id' => $branchB->id,
            'cv_path' => 'candidates/mismatch/cv.pdf',
            'status' => 'cv_reviewing',
            'applied_at' => now(),
        ]);
        Application::query()->create([
            'job_id' => $jobB->id,
            'candidate_id' => $candidateB->id,
            'branch_id' => $branchB->id,
            'cv_path' => 'candidates/b/cv.pdf',
            'status' => 'cv_reviewing',
            'applied_at' => now(),
        ]);

        $context = app(AiChatContextService::class)->build($hr, 'employer');
        $serialized = json_encode($context, JSON_UNESCAPED_UNICODE);

        $this->assertStringContainsString('Backend nội bộ A', $serialized);
        $this->assertStringContainsString('Ứng viên chi nhánh A', $serialized);
        $this->assertStringNotContainsString('Dữ liệu bí mật B', $serialized);
        $this->assertStringNotContainsString('Ứng viên bí mật B', $serialized);
        $this->assertStringNotContainsString('Ứng viên lệch chi nhánh', $serialized);
        $this->assertStringNotContainsString('a@example.com', $serialized);
    }

    public function test_candidate_job_context_prioritizes_profile_match_over_recency(): void
    {
        $branch = $this->makeBranch('MATCH');
        $employer = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $candidateUser = User::factory()->create([
            'role' => 'candidate',
            'is_active' => true,
        ]);
        $candidate = Candidate::query()->create([
            'user_id' => $candidateUser->id,
            'name' => 'Ứng viên Laravel',
            'email' => $candidateUser->email,
        ]);
        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Laravel Backend Developer',
            'desired_job' => ['position' => 'Laravel Developer'],
            'skills' => [['name' => 'Laravel'], ['name' => 'PHP']],
        ]);

        $matchingJob = $this->makeJob($branch, $employer, 'Laravel Backend Developer');
        foreach (range(1, 7) as $index) {
            $this->makeJob($branch, $employer, 'Nhân viên kinh doanh '.$index);
        }

        $context = app(AiChatContextService::class)->build($candidateUser, 'candidate');
        $jobSources = collect($context)
            ->filter(fn (array $source): bool => str_starts_with($source['key'], 'job-'))
            ->values();

        $this->assertCount(6, $jobSources);
        $this->assertSame('job-'.$matchingJob->id, $jobSources->first()['key']);
        $this->assertSame('Tin tuyển dụng: Laravel Backend Developer', $jobSources->first()['label']);
    }

    public function test_director_context_includes_operational_briefing_and_pending_offers(): void
    {
        $branch = $this->makeBranch('DIRECTOR');
        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'name' => 'HR Chi nhánh',
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $job = $this->makeJob($branch, $hr, 'Kỹ sư dữ liệu');
        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên chờ duyệt offer',
            'email' => 'offer@example.com',
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'assigned_hr_id' => $hr->id,
            'branch_id' => $branch->id,
            'cv_path' => 'candidates/offer/cv.pdf',
            'status' => 'offer',
            'applied_at' => now(),
        ]);
        Offer::query()->create([
            'application_id' => $application->id,
            'content' => 'Nội dung offer kiểm thử',
            'salary_offered' => 25000000,
            'status' => 'awaiting_approval',
            'approval_requested_at' => now(),
        ]);

        $context = app(AiChatContextService::class)->build($director, 'employer');
        $keys = collect($context)->pluck('key')->all();
        $serialized = json_encode($context, JSON_UNESCAPED_UNICODE);

        $this->assertContains('operational-workload', $keys);
        $this->assertContains('branch-performance', $keys);
        $this->assertContains('hr-workload', $keys);
        $this->assertContains('offers-awaiting-approval', $keys);
        $this->assertStringContainsString('đề nghị chờ giám đốc duyệt: 1', $serialized);
        $this->assertStringContainsString('Ứng viên chờ duyệt offer', $serialized);
        $this->assertStringContainsString('HR Chi nhánh: 1 hồ sơ', $serialized);
        $this->assertStringNotContainsString('offer@example.com', $serialized);
    }

    private function makeBranch(string $code): Branch
    {
        return Branch::query()->create([
            'name' => 'Branch '.$code,
            'code' => $code,
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
    }

    private function makeJob(Branch $branch, User $creator, string $title): RecruitmentJob
    {
        return RecruitmentJob::query()->create([
            'title' => $title,
            'slug' => str($title)->slug().'-'.uniqid(),
            'description' => 'Mô tả công việc kiểm thử.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $creator->id,
        ]);
    }
}
