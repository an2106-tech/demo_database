<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
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

    public function test_status_change_sends_candidate_and_hr_notifications(): void
    {
        Mail::fake();

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

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/current-status/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
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

        $application->forceFill([
            'status' => StatusApplicationEnum::SCREENING,
        ])->save();

        Mail::assertSent(CandidateApplicationStatusChangeMail::class, function (CandidateApplicationStatusChangeMail $mail) use ($application, $job): bool {
            return $mail->application->is($application) && $mail->job->is($job);
        });

        Mail::assertSent(HrApplicationStatusChangeMail::class, function (HrApplicationStatusChangeMail $mail) use ($application, $job): bool {
            return $mail->application->is($application) && $mail->job->is($job);
        });
    }
}
