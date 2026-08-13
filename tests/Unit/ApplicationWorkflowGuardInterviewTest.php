<?php

namespace Tests\Unit;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\Scorecard;
use App\Models\User;
use App\Services\ApplicationWorkflowGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationWorkflowGuardInterviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_create_interview_schedule_after_screening(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);

        $this->assertTrue(app(ApplicationWorkflowGuard::class)->canManageInterview($hr, $application));
    }

    public function test_hr_can_update_sent_interview_schedule_before_interview_time(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => now(),
            'result' => 'pending',
        ]);

        $application->load('latestInterview');

        $this->assertTrue(app(ApplicationWorkflowGuard::class)->canManageInterview($hr, $application));
    }

    public function test_hr_can_send_schedule_update_after_sent_interview_is_changed(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        $sentAt = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'));
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => $sentAt,
            'result' => 'pending',
        ]);

        $interview->updated_at = $sentAt->copy()->subMinutes(2);
        $interview->saveQuietly();

        $application->load('latestInterview');
        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertFalse($guard->canSendInterviewSchedule($hr, $application));

        $interview->forceFill([
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDays(2),
            'updated_at' => $sentAt->copy()->addMinutes(5), // explicitly in the future relative to sentAt
        ])->saveQuietly();

        $application->unsetRelation('latestInterview');
        $application->load('latestInterview');

        $this->assertTrue($guard->canSendInterviewSchedule($hr, $application));
    }

    public function test_hr_cannot_update_schedule_when_interview_is_due_for_evaluation(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subMinute(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => now()->subDay(),
            'result' => 'pending',
        ]);

        $application->load('latestInterview');
        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertFalse($guard->canManageInterview($hr, $application));
        $this->assertTrue($guard->canEvaluateInterview($hr, $application));
    }

    public function test_hr_cannot_update_schedule_after_scorecard_is_recorded(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW);

        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => now(),
            'result' => 'pending',
        ]);

        Scorecard::query()->create([
            'application_id' => $application->id,
            'interview_id' => $interview->id,
            'evaluator_id' => $hr->id,
            'criteria' => [],
            'average_score' => 7.5,
            'recommended_conclusion' => 'pass',
            'conclusion' => 'hold',
        ]);

        $application->load('latestInterview');

        $this->assertFalse(app(ApplicationWorkflowGuard::class)->canManageInterview($hr, $application));
    }

    public function test_branch_hr_can_record_draft_after_start_but_can_only_finalize_after_interview_ends(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);
        $interviewer = User::factory()->create([
            'role' => 'director',
            'is_active' => true,
            'branch_id' => $hr->branch_id,
        ]);

        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $interviewer->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subMinutes(10),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'result' => 'pending',
        ]);

        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertTrue($guard->canEvaluateInterview($hr, $application));
        $this->assertFalse($guard->canFinalizeInterviewEvaluation($hr, $application));

        $interview->update([
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subHours(2),
        ]);
        $application->unsetRelation('latestInterview');

        $this->assertTrue($guard->canFinalizeInterviewEvaluation($hr, $application));
    }

    /**
     * @return array{0: User, 1: Application}
     */
    private function makeApplication(StatusApplicationEnum $status): array
    {
        $branch = Branch::query()->create([
            'name' => 'Greenwich Việt Nam - Cần Thơ',
            'code' => 'GWCT',
            'city' => 'can_tho',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên kiểm thử',
            'email' => 'candidate-workflow@example.com',
            'phone' => '0901234567',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Công nghệ thông tin',
            'slug' => 'giang-vien-cong-nghe-thong-tin-test',
            'description' => 'Tuyển giảng viên ngành công nghệ thông tin.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'status' => $status,
            'branch_id' => $branch->id,
            'cv_path' => 'applications/cv/test.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'profile_snapshot' => [
                'candidate' => [
                    'name' => 'Ứng viên kiểm thử',
                    'email' => 'candidate-workflow@example.com',
                ],
            ],
        ]);

        return [$hr, $application];
    }
}
