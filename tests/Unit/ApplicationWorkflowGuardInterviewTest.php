<?php

namespace Tests\Unit;

use App\Enums\StatusApplicationEnum;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\Scorecard;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\ApplicationWorkflowGuard;
use App\Services\ApplicationWorkflowSummaryService;
use App\Services\InterviewEvaluatorService;
use App\Services\InterviewScheduleDeliveryService;
use App\Services\RecruitmentInternalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationWorkflowGuardInterviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_must_complete_pre_screening_before_creating_interview_schedule(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);

        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertFalse($guard->canManageInterview($hr, $application));
        $this->assertTrue($guard->canRecordPreScreening($hr, $application));

        $application->preScreenings()->create([
            'handled_by_user_id' => $hr->id,
            'contact_channel' => 'phone',
            'contacted_at' => now(),
            'outcome' => 'passed',
            'note' => 'Ứng viên xác nhận tham gia phỏng vấn.',
        ]);

        $application->unsetRelation('latestPreScreening');

        $this->assertTrue($guard->canManageInterview($hr, $application));
        $this->assertFalse($guard->canRecordPreScreening($hr, $application));
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

    public function test_hr_cannot_evaluate_interview_before_schedule_is_sent(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subMinutes(10),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'result' => 'pending',
        ]);

        $this->assertFalse(app(ApplicationWorkflowGuard::class)->canEvaluateInterview($hr, $application));
    }

    public function test_hr_can_reschedule_overdue_draft_interview_before_sending(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'result' => 'pending',
        ]);

        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertTrue($guard->canManageInterview($hr, $application));
        $this->assertFalse($guard->canSendInterviewSchedule($hr, $application));
        $this->assertFalse($guard->canEvaluateInterview($hr, $application));
    }

    public function test_interview_delivery_recipients_include_candidate_and_all_assigned_evaluators(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);
        $interviewer = User::factory()->create([
            'email' => 'interviewer-workflow@example.com',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);

        $panelist = User::factory()->create([
            'email' => 'panelist-workflow@example.com',
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        User::factory()->create([
            'email' => 'unassigned-director@example.com',
            'role' => 'director',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);

        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $interviewer->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn vòng 1',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'result' => 'pending',
        ]);
        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$panelist->id],
        );

        $recipients = app(InterviewScheduleDeliveryService::class)->recipients($application);

        $this->assertSame('candidate', $recipients['candidate-workflow@example.com']);
        $this->assertSame('lead', $recipients['interviewer-workflow@example.com']);
        $this->assertSame('evaluator', $recipients['panelist-workflow@example.com']);
        $this->assertArrayNotHasKey('unassigned-director@example.com', $recipients);

        app(RecruitmentInternalNotificationService::class)->notifyInterviewPanelAssigned($interview);
        app(RecruitmentInternalNotificationService::class)->notifyInterviewPanelAssigned($interview, true);

        $this->assertSame(1, UserNotification::query()->where('user_id', $interviewer->id)->count());
        $this->assertSame(1, UserNotification::query()->where('user_id', $panelist->id)->count());
        $this->assertSame(
            'Lịch phỏng vấn đã cập nhật',
            UserNotification::query()->where('user_id', $interviewer->id)->firstOrFail()->title,
        );
        $this->assertDatabaseMissing('notifications', [
            'user_id' => User::query()->where('email', 'unassigned-director@example.com')->value('id'),
            'type' => 'interview_panel_assigned',
        ]);
    }

    public function test_only_interview_lead_can_finalize_multi_evaluator_panel_or_confirm_an_early_end(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);
        $lead = User::factory()->create([
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $member = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $unassignedHr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $lead->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn chuyên môn',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subMinutes(10),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-panel',
            'invite_sent_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subHour(),
            'result' => 'pending',
        ]);
        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$member->id],
        );
        $interview->evaluators()->update(['submitted_at' => now()]);

        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertFalse($guard->canFinalizeInterviewPanel($lead, $application));
        $this->assertTrue($guard->canFinalizeInterviewPanel($lead, $application, true));
        $this->assertFalse($guard->canFinalizeInterviewPanel($member, $application, true));
        $this->assertFalse($guard->canFinalizeInterviewPanel($unassignedHr, $application, true));

        $summary = app(ApplicationWorkflowSummaryService::class)->summarize($application);
        $this->assertSame('Chờ chốt kết quả', $summary['status_label']);
        $this->assertStringContainsString('2/2 phiếu', $summary['description']);
    }

    public function test_internal_interview_mail_uses_assignment_wording(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn chuyên môn',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-panel',
            'result' => 'pending',
        ]);

        $internalMail = new InterviewScheduledMail($interview, 'evaluator');
        $legacyLeadMail = new InterviewScheduledMail($interview, 'interviewer');
        $candidateMail = new InterviewScheduledMail($interview, 'candidate');

        $this->assertStringContainsString('Phân công phỏng vấn', $internalMail->envelope()->subject);
        $this->assertStringContainsString('Thành viên đánh giá', $internalMail->render());
        $this->assertStringNotContainsString('Chúc mừng bạn đã vượt qua', $internalMail->render());
        $this->assertStringContainsString('Người phụ trách vòng phỏng vấn', $legacyLeadMail->render());
        $this->assertStringContainsString('Thư mời phỏng vấn', $candidateMail->envelope()->subject);
    }

    public function test_only_assigned_hr_can_record_draft_and_can_only_finalize_after_interview_ends(): void
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
            'invite_sent_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subHour(),
            'result' => 'pending',
        ]);

        $guard = app(ApplicationWorkflowGuard::class);

        $this->assertFalse($guard->canEvaluateInterview($hr, $application));

        app(InterviewEvaluatorService::class)->sync(
            $interview->loadMissing('application.job'),
            [$hr->id],
        );

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
