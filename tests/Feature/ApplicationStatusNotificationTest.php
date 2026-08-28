<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Mail\CandidateApplicationRejectedMail;
use App\Mail\CandidateApplicationStatusChangeMail;
use App\Mail\HrApplicationStatusChangeMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_status_change_records_history_without_status_change_emails(): void
    {
        Mail::fake();

        $application = $this->makeApplication(StatusApplicationEnum::NEW);

        $application->forceFill([
            'status' => StatusApplicationEnum::SCREENING,
        ])->save();

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::NEW->value,
            'to_status' => StatusApplicationEnum::SCREENING->value,
        ]);

        Mail::assertNotSent(CandidateApplicationStatusChangeMail::class);
        Mail::assertNotSent(HrApplicationStatusChangeMail::class);
        Mail::assertNotSent(CandidateApplicationRejectedMail::class);
    }

    public function test_rejected_status_change_sends_only_rejection_email(): void
    {
        Mail::fake();

        $application = $this->makeApplication(StatusApplicationEnum::SCREENING);

        $application->forceFill([
            'status' => StatusApplicationEnum::REJECTED,
            'rejected_reason' => 'Không phù hợp với yêu cầu vị trí.',
        ])->save();

        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::SCREENING->value,
            'to_status' => StatusApplicationEnum::REJECTED->value,
        ]);

        Mail::assertQueued(CandidateApplicationRejectedMail::class, function (CandidateApplicationRejectedMail $mail) use ($application): bool {
            return $mail->application->is($application)
                && ! str_contains($mail->render(), 'Không phù hợp với yêu cầu vị trí.');
        });
        Mail::assertNotSent(CandidateApplicationStatusChangeMail::class);
        Mail::assertNotSent(HrApplicationStatusChangeMail::class);
    }

    private function makeApplication(StatusApplicationEnum $status): Application
    {
        $branch = Branch::query()->create([
            'name' => 'Status Branch',
            'code' => 'ST',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Current Candidate',
            'email' => 'current-status@example.com',
            'phone' => '0901111222',
            'cv_file' => 'candidates/current-status/cv.pdf',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Status Notification Developer',
            'slug' => 'status-notification-developer',
            'description' => 'Status notifications test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        return Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/current-status/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => $status,
            'branch_id' => $branch->id,
            'profile_snapshot' => [
                'name' => 'Snapshot Candidate',
                'email' => 'snapshot-status@example.com',
                'candidate' => [
                    'name' => 'Snapshot Candidate',
                    'email' => 'snapshot-status@example.com',
                ],
                'resume' => [
                    'profile_title' => 'Snapshot Role',
                ],
            ],
            'applied_at' => now(),
        ]);
    }
}
