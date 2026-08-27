<?php

namespace Tests\Feature;

use App\Livewire\Client\Job\JobDetail;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\AiMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobDetailAiMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_run_ai_match_from_job_detail(): void
    {
        $user = User::factory()->create([
            'name' => 'AI Candidate',
            'email' => 'ai-candidate@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate'], 'account_type' => 'candidate'],
        ]);

        $candidate = Candidate::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Backend Developer',
            'career_objective' => 'Build useful recruitment products.',
            'desired_job' => ['position' => 'Laravel Developer'],
            'skills' => [['name' => 'Laravel']],
        ]);

        $branch = Branch::query()->create([
            'name' => 'AI Branch',
            'code' => 'AIBR',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer-ai-match',
            'description' => 'Build Laravel systems with REST APIs.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $user->id,
        ]);

        $this->mock(AiMatchingService::class, function ($mock) use ($job): void {
            $mock->shouldReceive('evaluateJobFitWithCv')
                ->once()
                ->andReturn([
                    'score' => 91,
                    'reason' => 'CV thể hiện kinh nghiệm phù hợp với công việc này.',
                    'matched_requirements' => ['Laravel', 'REST API'],
                    'missing_requirements' => ['Kinh nghiệm lead team'],
                ]);
        });

        $this->actingAs($user);

        Livewire::test(JobDetail::class, ['slug' => $job->slug])
            ->set('showApplyAction', true)
            ->call('checkJobFitWithAi')
            ->assertSee('Ứng tuyển vị trí này')
            ->assertDontSee('Xem giao diện ứng viên')
            ->assertSee('91% — Rất phù hợp')
            ->assertSee('CV thể hiện kinh nghiệm phù hợp với công việc này.')
            ->assertSee('Kinh nghiệm lead team');
    }
}
