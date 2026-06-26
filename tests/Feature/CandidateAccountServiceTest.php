<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Services\CandidateAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_readiness_is_separate_from_full_profile_completion(): void
    {
        $candidate = Candidate::query()->create([
            'name' => 'Nguyen Van A',
            'email' => 'candidate@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
        ]);

        $service = app(CandidateAccountService::class);

        $this->assertSame(100, $service->applicationProfileCompletion($candidate));
        $this->assertSame(40, $service->profileCompletion($candidate));
        $this->assertTrue($service->isProfileReadyForApplication($candidate));
    }

    public function test_profile_completion_counts_resume_detail_fields(): void
    {
        $candidate = Candidate::query()->create([
            'name' => 'Nguyen Van A',
            'email' => 'candidate@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/demo/cv.pdf',
            'experience_years' => 2,
        ]);

        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Backend Developer',
            'career_objective' => 'Build reliable hiring tools.',
            'desired_job' => ['position' => 'Laravel Developer'],
            'educations' => [
                ['school' => 'FPT Polytechnic'],
            ],
            'skills' => [
                ['name' => 'Laravel'],
            ],
        ]);

        $this->assertSame(100, app(CandidateAccountService::class)->profileCompletion($candidate));
    }
}
