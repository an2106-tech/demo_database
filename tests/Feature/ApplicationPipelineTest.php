<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Livewire\Client\Employers\ApplicationPipeline;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_uses_application_snapshot_for_candidate_card(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Pipeline Branch',
            'code' => 'PB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Current Candidate Name',
            'email' => 'current-candidate@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/current/cv.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Pipeline Developer',
            'slug' => 'pipeline-developer',
            'description' => 'Build pipeline.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/snapshot/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::NEW,
            'branch_id' => $branch->id,
            'profile_snapshot' => [
                'candidate' => [
                    'name' => 'Snapshot Candidate Name',
                    'email' => 'snapshot@example.com',
                ],
                'resume' => [
                    'profile_title' => 'Snapshot Backend Developer',
                ],
                'cv' => [
                    'original_filename' => 'snapshot-cv.pdf',
                ],
            ],
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->assertSee('Snapshot Candidate Name')
            ->assertSee('snapshot@example.com')
            ->assertSee('Snapshot Backend Developer')
            ->assertSee('snapshot-cv.pdf')
            ->assertDontSee('updateStatus')
            ->assertDontSee('Current Candidate Name')
            ->assertDontSee('current-candidate@example.com');
    }

    public function test_hr_can_mark_application_as_viewed_from_pipeline(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'is_viewed' => false,
            'viewed_at' => null,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('markAsViewed', $application->id);

        $application->refresh();

        $this->assertTrue($application->is_viewed);
        $this->assertNotNull($application->viewed_at);
    }

    public function test_pipeline_shows_legacy_application_without_branch_id_when_job_is_in_hr_branch(): void
    {
        [$hr] = $this->createPipelineApplication([
            'branch_id' => null,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->assertSee('Pipeline Candidate Snapshot')
            ->assertSee('pipeline-cv.pdf');
    }

    public function test_hr_can_advance_application_to_next_pipeline_status(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::CV_REVIEWING,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('advanceApplication', $application->id);

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::SCREENING, $application->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::CV_REVIEWING->value,
            'to_status' => StatusApplicationEnum::SCREENING->value,
            'changed_by_id' => $hr->id,
            'comment' => 'HR chuyển nhanh từ Pipeline.',
        ]);
    }

    public function test_hr_can_advance_application_with_pipeline_post_fallback(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::CV_REVIEWING,
        ]);

        $this->actingAs($hr)
            ->post(route('employers.application_pipeline.advance', ['application' => $application->id]))
            ->assertRedirect(ApplicationResource::getUrl('kanban'));

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::CV_REVIEWING, $application->status);
    }

    public function test_hr_can_open_interview_scheduler_from_pipeline(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::SCREENING,
        ]);

        $this->actingAs($hr);

        app(\App\Services\ApplicationPreScreeningService::class)->record(
            $application,
            $hr,
            'phone',
            now(),
            'passed',
            note: 'Ứng viên xác nhận tiếp tục quy trình.',
        );

        Livewire::test(ApplicationPipeline::class)
            ->call('openInterviewScheduler', $application->id)
            ->assertSet('showInterviewModal', true)
            ->assertSet('interviewApplicationId', $application->id)
            ->assertSet('interviewForm.interviewer_id', (string) $hr->id);
    }

    public function test_hr_can_schedule_interview_with_pipeline_post_fallback(): void
    {
        Mail::fake();
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::SCREENING,
        ]);

        $scheduledAt = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->addDays(2)
            ->setTime(10, 30)
            ->format('Y-m-d\TH:i');

        $this->actingAs($hr)
            ->post(route('employers.application_pipeline.schedule_interview', ['application' => $application->id]), [
                'round_name' => 'Phong van chuyen mon',
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 60,
                'type' => 'online',
                'meeting_link' => 'https://meet.google.com/fpt-demo',
                'interviewer_id' => (string) $hr->id,
                'notes' => 'Chuan bi portfolio.',
            ])
            ->assertRedirect(ApplicationResource::getUrl('kanban'));

        $application->refresh();
        $interview = $application->interviews()->first();

        $this->assertNull($interview);
        $this->assertSame(StatusApplicationEnum::SCREENING, $application->status);
        Mail::assertNothingSent();
    }

    public function test_screening_application_requires_interview_schedule_instead_of_quick_advance(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::SCREENING,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('advanceApplication', $application->id);

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::SCREENING, $application->status);
        $this->assertFalse($application->interviews()->exists());
    }

    public function test_hr_can_evaluate_interview_and_move_application_to_offer(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::INTERVIEWING,
        ]);

        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phong van chuyen mon',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => now()->subDay(),
            'result' => 'pending',
        ]);

        $this->actingAs($hr)
            ->post(route('employers.application_pipeline.evaluate_interview', ['application' => $application->id]), [
                'technical_score' => 8.5,
                'problem_solving_score' => 8,
                'communication_score' => 7.5,
                'culture_score' => 8,
                'conclusion' => 'pass',
                'notes' => 'Dat yeu cau phong van.',
            ])
            ->assertRedirect(ApplicationResource::getUrl('kanban'));

        $application->refresh();
        $interview->refresh();

        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->status);
        $this->assertSame('pending', $interview->result);
        $this->assertDatabaseMissing('scorecards', ['interview_id' => $interview->id]);
    }

    public function test_hr_can_hold_interview_evaluation_without_offering(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::INTERVIEW_SCHEDULED,
        ]);

        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phong van vong 1',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => now()->subDay(),
            'result' => 'pending',
        ]);

        $this->actingAs($hr)
            ->post(route('employers.application_pipeline.evaluate_interview', ['application' => $application->id]), [
                'technical_score' => 6,
                'problem_solving_score' => 6,
                'communication_score' => 7,
                'culture_score' => 7,
                'conclusion' => 'hold',
                'notes' => 'Can phong van them voi truong nhom.',
            ])
            ->assertRedirect(ApplicationResource::getUrl('kanban'));

        $application->refresh();
        $interview->refresh();

        $this->assertSame(StatusApplicationEnum::INTERVIEW_SCHEDULED, $application->status);
        $this->assertSame('pending', $interview->result);
        $this->assertDatabaseMissing('scorecards', ['interview_id' => $interview->id]);
    }

    public function test_hr_can_reject_application_from_pipeline(): void
    {
        Mail::fake();

        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::CV_REVIEWING,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('openRejectionModal', $application->id)
            ->assertSet('showRejectModal', true)
            ->call('rejectApplication')
            ->assertHasErrors(['rejectionReason' => 'required'])
            ->set('rejectionReason', 'Chưa đáp ứng yêu cầu kinh nghiệm tối thiểu.')
            ->call('rejectApplication')
            ->assertSet('showRejectModal', false);

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::REJECTED, $application->status);
        $this->assertSame('Chưa đáp ứng yêu cầu kinh nghiệm tối thiểu.', $application->rejected_reason);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::CV_REVIEWING->value,
            'to_status' => StatusApplicationEnum::REJECTED->value,
            'changed_by_id' => $hr->id,
            'comment' => 'Từ chối ứng viên. Lý do: Chưa đáp ứng yêu cầu kinh nghiệm tối thiểu.',
        ]);
    }

    public function test_hr_cannot_quick_action_application_from_other_branch(): void
    {
        [$hr] = $this->createPipelineApplication();
        [, $otherApplication] = $this->createPipelineApplication(
            branchAttributes: ['name' => 'Other Branch', 'code' => 'OB'],
            userAttributes: ['email' => 'other-hr@example.com']
        );

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('advanceApplication', $otherApplication->id)
            ->assertForbidden();
    }

    public function test_pm_cannot_run_hr_pipeline_actions(): void
    {
        [$pm, $application] = $this->createPipelineApplication(
            userAttributes: ['role' => 'pm'],
        );

        $this->actingAs($pm);

        Livewire::test(ApplicationPipeline::class)
            ->call('advanceApplication', $application->id)
            ->assertForbidden();
    }

    public function test_assigned_pm_can_open_and_submit_interview_evaluation(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::INTERVIEWING,
        ]);
        $pm = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'branch_id' => $application->branch_id,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ]);

        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $pm->id,
            'round_number' => 1,
            'round_name' => 'Phong van voi quan ly',
            'scheduled_at' => now()->subHour(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-pm',
            'invite_sent_at' => now()->subHours(2),
            'result' => 'pending',
        ]);

        $this->actingAs($pm);

        Livewire::test(ApplicationPipeline::class)
            ->call('openInterviewEvaluation', $application->id)
            ->assertSet('showEvaluationModal', true);

        $this->post(route('employers.application_pipeline.evaluate_interview', ['application' => $application->id]), [
            'technical_score' => 7,
            'problem_solving_score' => 7,
            'communication_score' => 7,
            'culture_score' => 7,
            'conclusion' => 'hold',
            'notes' => 'Cần thêm một vòng đánh giá.',
        ])->assertRedirect(ApplicationResource::getUrl('kanban'));

        $this->assertDatabaseMissing('scorecards', ['application_id' => $application->id]);
    }

    public function test_interview_fail_requires_rejection_reason(): void
    {
        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::INTERVIEWING,
        ]);

        Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'round_number' => 1,
            'round_name' => 'Phong van chuyen mon',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-demo',
            'invite_sent_at' => now()->subHours(2),
            'result' => 'pending',
        ]);

        $this->actingAs($hr)
            ->from(route('employers.application_pipeline', ['evaluate_interview' => $application->id]))
            ->post(route('employers.application_pipeline.evaluate_interview', ['application' => $application->id]), [
                'technical_score' => 4,
                'problem_solving_score' => 4,
                'communication_score' => 5,
                'culture_score' => 5,
                'conclusion' => 'fail',
                'notes' => 'Chưa đạt yêu cầu.',
            ])
            ->assertRedirect(ApplicationResource::getUrl('kanban'));

        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->fresh()->status);
    }

    /**
     * @return array{0: User, 1: Application}
     */
    private function createPipelineApplication(
        array $applicationAttributes = [],
        array $branchAttributes = [],
        array $userAttributes = [],
    ): array {
        $branch = Branch::query()->create(array_merge([
            'name' => 'FPT Pipeline Branch',
            'code' => 'PB'.fake()->unique()->numberBetween(100, 999),
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ], $branchAttributes));

        $hr = User::factory()->create(array_merge([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
            'metadata' => ['account_types' => ['employer'], 'account_type' => 'employer'],
        ], $userAttributes));

        $candidate = Candidate::query()->create([
            'name' => 'Pipeline Candidate',
            'email' => 'pipeline-candidate-'.fake()->unique()->numberBetween(1000, 9999).'@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/current/cv.pdf',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Pipeline Developer '.fake()->unique()->numberBetween(100, 999),
            'slug' => 'pipeline-developer-'.fake()->unique()->numberBetween(100, 999),
            'description' => 'Build pipeline.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create(array_merge([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/snapshot/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::CV_REVIEWING,
            'branch_id' => $branch->id,
            'is_viewed' => false,
            'profile_snapshot' => [
                'candidate' => [
                    'name' => 'Pipeline Candidate Snapshot',
                    'email' => 'pipeline-snapshot@example.com',
                ],
                'resume' => [
                    'profile_title' => 'Pipeline Backend Developer',
                ],
                'cv' => [
                    'original_filename' => 'pipeline-cv.pdf',
                ],
            ],
        ], $applicationAttributes));

        return [$hr, $application];
    }
}
