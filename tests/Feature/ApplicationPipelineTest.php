<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\Employers\ApplicationPipeline;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
            ->assertRedirect();

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::SCREENING, $application->status);
    }

    public function test_hr_can_schedule_interview_from_pipeline(): void
    {
        Mail::fake();
        Storage::fake('local');

        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::SCREENING,
        ]);

        $scheduledAt = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->addDays(2)
            ->setTime(10, 30)
            ->format('Y-m-d\TH:i');

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('openInterviewScheduler', $application->id)
            ->assertSet('showInterviewModal', true)
            ->set('interviewForm.round_name', 'Phỏng vấn chuyên môn')
            ->set('interviewForm.scheduled_at', $scheduledAt)
            ->set('interviewForm.duration_minutes', 60)
            ->set('interviewForm.type', 'online')
            ->set('interviewForm.meeting_link', 'https://meet.google.com/fpt-demo')
            ->set('interviewForm.interviewer_id', (string) $hr->id)
            ->set('interviewForm.notes', 'Chuẩn bị portfolio.')
            ->call('saveInterviewSchedule')
            ->assertSet('showInterviewModal', false);

        $application->refresh();
        $interview = $application->interviews()->first();

        $this->assertNotNull($interview);
        $this->assertSame(StatusApplicationEnum::INTERVIEW_SCHEDULED, $application->status);
        $this->assertSame($hr->id, $interview->interviewer_id);
        $this->assertSame('online', $interview->type);
        $this->assertSame('https://meet.google.com/fpt-demo', $interview->meeting_link);

        Storage::disk('local')->assertExists("interviews/interview-{$interview->id}.ics");
        Mail::assertSent(InterviewScheduledMail::class);
    }

    public function test_hr_can_schedule_interview_with_pipeline_post_fallback(): void
    {
        Mail::fake();
        Storage::fake('local');

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
            ->assertRedirect(route('employers.application_pipeline'));

        $application->refresh();
        $interview = $application->interviews()->first();

        $this->assertNotNull($interview);
        $this->assertSame(StatusApplicationEnum::INTERVIEW_SCHEDULED, $application->status);
        $this->assertSame($hr->id, $interview->interviewer_id);
        $this->assertSame('Phong van chuyen mon', $interview->round_name);
        $this->assertSame('online', $interview->type);
        $this->assertSame('https://meet.google.com/fpt-demo', $interview->meeting_link);

        Storage::disk('local')->assertExists("interviews/interview-{$interview->id}.ics");
        Mail::assertSent(InterviewScheduledMail::class);
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
            ->assertRedirect(route('employers.application_pipeline'));

        $application->refresh();
        $interview->refresh();

        $this->assertSame(StatusApplicationEnum::OFFERED, $application->status);
        $this->assertSame('pass', $interview->result);
        $this->assertDatabaseHas('scorecards', [
            'application_id' => $application->id,
            'interview_id' => $interview->id,
            'evaluator_id' => $hr->id,
            'conclusion' => 'pass',
        ]);
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
            ->assertRedirect(route('employers.application_pipeline'));

        $application->refresh();
        $interview->refresh();

        $this->assertSame(StatusApplicationEnum::INTERVIEWING, $application->status);
        $this->assertSame('pending', $interview->result);
        $this->assertDatabaseHas('scorecards', [
            'application_id' => $application->id,
            'interview_id' => $interview->id,
            'evaluator_id' => $hr->id,
            'conclusion' => 'hold',
        ]);
    }

    public function test_hr_can_reject_application_from_pipeline(): void
    {
        Mail::fake();

        [$hr, $application] = $this->createPipelineApplication([
            'status' => StatusApplicationEnum::CV_REVIEWING,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplicationPipeline::class)
            ->call('rejectApplication', $application->id);

        $application->refresh();

        $this->assertSame(StatusApplicationEnum::REJECTED, $application->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status' => StatusApplicationEnum::CV_REVIEWING->value,
            'to_status' => StatusApplicationEnum::REJECTED->value,
            'changed_by_id' => $hr->id,
            'comment' => 'HR từ chối nhanh từ Pipeline.',
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
            'code' => 'PB' . fake()->unique()->numberBetween(100, 999),
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
            'email' => 'pipeline-candidate-' . fake()->unique()->numberBetween(1000, 9999) . '@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/current/cv.pdf',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Pipeline Developer ' . fake()->unique()->numberBetween(100, 999),
            'slug' => 'pipeline-developer-' . fake()->unique()->numberBetween(100, 999),
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
