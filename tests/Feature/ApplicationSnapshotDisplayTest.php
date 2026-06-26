<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSnapshotDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_uses_snapshot_candidate_data_instead_of_current_profile(): void
    {
        $application = $this->makeApplicationWithSnapshot();

        $this->assertSame('Snapshot Nguyen', $application->snapshotCandidateName());
        $this->assertSame('snapshot@example.com', $application->snapshotCandidateEmail());
        $this->assertSame('0909888777', $application->snapshotCandidatePhone());
        $this->assertSame(5, $application->snapshotCandidateExperienceYears());
        $this->assertSame('Snapshot Backend Developer', $application->snapshotProfileTitle());
    }

    public function test_submitted_cv_prefers_application_snapshot_and_attachment(): void
    {
        $application = $this->makeApplicationWithSnapshot([
            'cv' => [
                'path' => 'applications/old-submitted.pdf',
                'original_filename' => 'old-submitted.pdf',
            ],
        ]);

        $attachment = $application->attachments()->create([
            'path' => 'applications/attachment-submitted.pdf',
            'type' => 'cv',
            'original_filename' => 'attachment-submitted.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
        ]);
        $application->forceFill([
            'cv_attachment_id' => $attachment->id,
            'cv_path' => 'candidates/current-profile.pdf',
        ])->save();
        $application->refresh();

        $this->assertSame('applications/old-submitted.pdf', $application->submittedCvPath());
        $this->assertSame('old-submitted.pdf', $application->submittedCvName());
    }

    public function test_filament_application_review_renders_snapshot_candidate_data(): void
    {
        $application = $this->makeApplicationWithSnapshot();

        $html = view('filament.applications.application-review', ['record' => $application])->render();

        $this->assertStringContainsString('Snapshot Nguyen', $html);
        $this->assertStringContainsString('snapshot@example.com', $html);
        $this->assertStringContainsString('0909888777', $html);
        $this->assertStringContainsString('Snapshot CV.pdf', $html);
        $this->assertStringNotContainsString('Current Nguyen', $html);
        $this->assertStringNotContainsString('current@example.com', $html);
    }

    private function makeApplicationWithSnapshot(array $snapshotOverrides = []): Application
    {
        $branch = Branch::query()->create([
            'name' => 'Snapshot Branch',
            'code' => 'SB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Current Nguyen',
            'email' => 'current@example.com',
            'phone' => '0901111222',
            'experience_years' => 1,
            'cv_file' => 'candidates/current-profile.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Snapshot Job',
            'slug' => 'snapshot-job',
            'description' => 'Snapshot job description.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $snapshot = array_replace_recursive([
            'candidate' => [
                'name' => 'Snapshot Nguyen',
                'email' => 'snapshot@example.com',
                'phone' => '0909888777',
                'experience_years' => 5,
            ],
            'resume' => [
                'profile_title' => 'Snapshot Backend Developer',
                'career_objective' => 'Build reliable products.',
                'experiences' => [],
                'educations' => [],
                'skills' => [],
            ],
            'cv' => [
                'path' => 'applications/snapshot-cv.pdf',
                'original_filename' => 'Snapshot CV.pdf',
            ],
        ], $snapshotOverrides);

        return Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/current-profile.pdf',
            'apply_method' => 'profile',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'branch_id' => $branch->id,
            'profile_snapshot' => $snapshot,
            'applied_at' => now(),
        ]);
    }
}
