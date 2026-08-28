<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\Pages\KanbanApplications;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\Scorecard;
use App\Models\ScorecardTemplate;
use App\Models\User;
use App\Services\ApplicationAiAnalysisService;
use App\Services\ApplicationKanbanTransitionService;
use App\Services\InterviewEvaluatorService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApplicationKanbanTransitionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_blocks_invalid_stage_jump(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::NEW);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'hired', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertNull($result['target_status']);
        $this->assertNull($result['requires']);
        $this->assertStringContainsString('Không thể chuyển', $result['message']);
    }

    public function test_withdrawn_application_is_terminal_on_kanban(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::WITHDRAWN);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'screening', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertNull($result['target_status']);
        $this->assertStringContainsString('đã kết thúc', $result['message']);
    }

    public function test_it_requires_screening_data_before_moving_to_screening_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::NEW);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'screening', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::SCREENING->value, $result['target_status']);
        $this->assertSame('cv_screening', $result['requires']);
    }

    public function test_it_requires_rejection_reason_before_moving_to_rejected_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'rejected', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::REJECTED->value, $result['target_status']);
        $this->assertSame('rejection_reason', $result['requires']);
    }

    public function test_it_requires_interview_evaluation_before_moving_interview_stage_to_offer_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'offer', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::OFFERED->value, $result['target_status']);
        $this->assertSame('interview_evaluation', $result['requires']);
        $this->assertStringContainsString('đánh giá phỏng vấn', $result['message']);
    }

    public function test_it_requires_pre_screening_before_moving_to_interview_stage(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application, 'interview', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame(StatusApplicationEnum::INTERVIEW_SCHEDULED->value, $result['target_status']);
        $this->assertSame('pre_screening', $result['requires']);
    }

    public function test_it_allows_interview_schedule_requirement_after_passing_pre_screening(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);
        $application->preScreenings()->create([
            'handled_by_user_id' => $hr->id,
            'contact_channel' => 'phone',
            'contacted_at' => now(),
            'outcome' => 'passed',
            'note' => 'Ứng viên xác nhận sẵn sàng tham gia phỏng vấn.',
        ]);

        $result = app(ApplicationKanbanTransitionService::class)
            ->evaluateStageMove($application->fresh(), 'interview', $hr);

        $this->assertFalse($result['allowed']);
        $this->assertSame('interview_schedule', $result['requires']);
    }

    public function test_kanban_renders_schedule_form_after_passing_pre_screening(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::SCREENING);
        $member = User::factory()->create([
            'name' => 'Trưởng bộ môn CNTT',
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $application->preScreenings()->create([
            'handled_by_user_id' => $hr->id,
            'contact_channel' => 'phone',
            'contacted_at' => now(),
            'outcome' => 'passed',
            'note' => 'Ứng viên xác nhận sẵn sàng tham gia phỏng vấn.',
        ]);
        $hr->givePermissionTo([
            Permission::findOrCreate('ViewAny:Application'),
            Permission::findOrCreate('View:Application'),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Storage::fake('local');

        $component = Livewire::actingAs($hr)
            ->test(KanbanApplications::class)
            ->set('search', 'HS'.$application->id)
            ->assertSee($application->snapshotCandidateName())
            ->call('moveApplicationToStage', $application->id, 'interview')
            ->assertSet('kanbanDropAction.type', 'interview_schedule')
            ->assertSet('kanbanDropAction.application_id', $application->id)
            ->assertSee('scheduleInterviewFromKanban', escape: false)
            ->assertSee('Mẫu đánh giá mặc định')
            ->assertSee('Xem tiêu chí đánh giá')
            ->assertDontSee('Chọn mẫu đánh giá')
            ->assertSee('Người phụ trách vòng phỏng vấn')
            ->assertSee('Thành viên đánh giá')
            ->set('kanbanInterviewForm.evaluator_ids', [(string) $hr->id, (string) $member->id])
            ->set('kanbanInterviewForm.interviewer_id', (string) $hr->id)
            ->assertSet('kanbanInterviewForm.evaluator_ids', [(string) $member->id])
            ->set('kanbanInterviewForm.interviewer_id', '')
            ->assertSet('kanbanInterviewForm.evaluator_ids', [])
            ->assertSee('Lưu lịch phỏng vấn');

        $component
            ->set('kanbanInterviewForm.interviewer_id', (string) $hr->id)
            ->set('kanbanInterviewForm.scheduled_at', now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->addDay()->setMinute(0)->format('Y-m-d\TH:i'))
            ->set('kanbanInterviewForm.duration_minutes', '60')
            ->set('kanbanInterviewForm.type', 'online')
            ->set('kanbanInterviewForm.meeting_link', 'https://meet.google.com/abc-defg-hij')
            ->call('scheduleInterviewFromKanban')
            ->assertHasNoErrors();

        $scorecard = ScorecardTemplate::query()->where('name', 'Mẫu đánh giá mặc định')->firstOrFail();
        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn và đánh giá',
            'scorecard_template_id' => $scorecard->id,
        ]);
    }

    public function test_kanban_can_open_and_reschedule_an_overdue_unsent_interview(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEW_SCHEDULED);
        $scorecard = ScorecardTemplate::query()->where('name', 'Mẫu đánh giá mặc định')->firstOrFail();
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'scorecard_template_id' => $scorecard->id,
            'scorecard_template_snapshot' => [
                'id' => $scorecard->id,
                'name' => $scorecard->name,
                'criteria' => $scorecard->criteria,
            ],
            'round_number' => 1,
            'round_name' => 'Phỏng vấn và đánh giá',
            'scheduled_at' => now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->subDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'result' => 'pending',
        ]);
        $hr->givePermissionTo([
            Permission::findOrCreate('ViewAny:Application'),
            Permission::findOrCreate('View:Application'),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Storage::fake('local');
        $newSchedule = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))
            ->addDay()
            ->setMinute(0)
            ->format('Y-m-d\TH:i');

        Livewire::actingAs($hr)
            ->test(KanbanApplications::class)
            ->call('openInterviewScheduleFromKanban', $application->id)
            ->assertSet('kanbanDropAction.type', 'interview_schedule')
            ->assertSet('kanbanDropAction.application_id', $application->id)
            ->assertSet('kanbanInterviewForm.interviewer_id', (string) $hr->id)
            ->set('kanbanInterviewForm.scheduled_at', $newSchedule)
            ->call('scheduleInterviewFromKanban')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('interviews', 1);
        $this->assertTrue($interview->fresh()->scheduled_at->isFuture());
        $this->assertNull($interview->fresh()->invite_sent_at);
    }

    public function test_it_only_allows_hired_stage_after_candidate_accepts_offer(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::OFFER);

        Offer::query()->create([
            'application_id' => $application->id,
            'status' => 'pending',
            'salary_offered' => 15000000,
            'start_date' => now()->addMonth(),
            'probation_months' => 2,
            'expires_at' => now()->addDays(3),
            'content' => 'Offer content',
        ]);

        $service = app(ApplicationKanbanTransitionService::class);
        $pendingResult = $service->evaluateStageMove($application->fresh('latestOffer'), 'hired', $hr);

        $this->assertFalse($pendingResult['allowed']);
        $this->assertStringContainsString('đã đồng ý', $pendingResult['message']);

        $application->latestOffer()->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $acceptedResult = $service->evaluateStageMove($application->fresh('latestOffer'), 'hired', $hr);

        $this->assertTrue($acceptedResult['allowed']);
        $this->assertSame(StatusApplicationEnum::HIRED->value, $acceptedResult['target_status']);
        $this->assertNull($acceptedResult['requires']);
    }

    public function test_offer_context_uses_final_panel_result_instead_of_latest_individual_scorecard(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::OFFERED);
        $member = User::factory()->create([
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $template = ScorecardTemplate::query()->create([
            'name' => 'Mẫu đánh giá hội đồng',
            'criteria' => [['name' => 'Chuyên môn']],
            'is_default' => false,
            'created_by' => $hr->id,
        ]);
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'scorecard_template_id' => $template->id,
            'round_number' => 1,
            'round_name' => 'Phỏng vấn chuyên môn',
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-panel',
            'result' => 'pass',
            'actual_ended_at' => now()->subHour(),
            'finalized_at' => now(),
            'finalized_by_user_id' => $hr->id,
            'final_notes' => 'Hội đồng thống nhất ứng viên đáp ứng yêu cầu.',
        ]);
        foreach ([[$hr, 8, 'pass'], [$member, 6, 'hold']] as [$evaluator, $average, $conclusion]) {
            Scorecard::query()->create([
                'application_id' => $application->id,
                'interview_id' => $interview->id,
                'template_id' => $template->id,
                'evaluator_id' => $evaluator->id,
                'criteria' => [['name' => 'Chuyên môn', 'score' => $average]],
                'average_score' => $average,
                'recommended_conclusion' => $conclusion,
                'conclusion' => $conclusion,
                'submitted_at' => now(),
            ]);
        }

        $page = app(KanbanApplications::class);
        $method = new \ReflectionMethod($page, 'offerDraftContext');
        $context = $method->invoke($page, $application->fresh());

        $this->assertSame('Đạt phỏng vấn', $context['interview_result']);
        $this->assertSame('7,00/10', $context['average_score']);
        $this->assertSame('Đạt phỏng vấn', $context['recommendation']);
        $this->assertSame('Hội đồng thống nhất ứng viên đáp ứng yêu cầu.', $context['interview_note']);
    }

    public function test_kanban_panel_finalization_payload_includes_each_submitted_scorecard(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEWING);
        $member = User::factory()->create([
            'role' => 'pm',
            'branch_id' => $hr->branch_id,
            'is_active' => true,
        ]);
        $template = ScorecardTemplate::query()->create([
            'name' => 'Mẫu đánh giá hội đồng',
            'criteria' => [['name' => 'Chuyên môn']],
            'is_default' => false,
            'created_by' => $hr->id,
        ]);
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $hr->id,
            'scorecard_template_id' => $template->id,
            'scorecard_template_snapshot' => [
                'name' => $template->name,
                'criteria' => $template->criteria,
            ],
            'round_number' => 1,
            'round_name' => 'Phỏng vấn chuyên môn',
            'scheduled_at' => now()->subHours(2),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/fpt-panel',
            'invite_sent_at' => now()->subHours(3),
            'result' => 'pending',
        ]);
        $evaluatorService = app(InterviewEvaluatorService::class);
        $evaluatorService->sync($interview->loadMissing('application.job'), [$member->id]);

        foreach ([[$hr, 8, 'pass', 'Phù hợp chuyên môn.'], [$member, 6, 'hold', 'Cần làm rõ kinh nghiệm.']] as [$evaluator, $average, $conclusion, $notes]) {
            Scorecard::query()->create([
                'application_id' => $application->id,
                'interview_id' => $interview->id,
                'template_id' => $template->id,
                'evaluator_id' => $evaluator->id,
                'criteria' => [['name' => 'Chuyên môn', 'score' => $average, 'note' => $notes]],
                'average_score' => $average,
                'recommended_conclusion' => $conclusion,
                'conclusion' => $conclusion,
                'notes' => $notes,
                'submitted_at' => now(),
            ]);
            $evaluatorService->markSubmitted($interview, $evaluator);
        }

        $this->actingAs($hr);
        $page = app(KanbanApplications::class);
        $method = new \ReflectionMethod($page, 'interviewEvaluationPayload');
        $payload = $method->invoke($page, $application->fresh());

        $this->assertTrue($payload['finalization_mode']);
        $this->assertFalse($payload['single_evaluator']);
        $this->assertCount(2, $payload['panel_submissions']);
        $this->assertSame('Phù hợp chuyên môn.', $payload['panel_submissions'][0]['notes']);
        $this->assertSame('Cần làm rõ kinh nghiệm.', $payload['panel_submissions'][1]['notes']);
    }

    public function test_evaluation_confirmation_is_only_reset_when_the_result_changes(): void
    {
        $page = app(KanbanApplications::class);
        $page->kanbanEvaluationForm['confirm_completion'] = true;

        $page->updatedKanbanEvaluationForm('Nhận xét bổ sung.', 'notes');
        $this->assertTrue($page->kanbanEvaluationForm['confirm_completion']);

        $page->updatedKanbanEvaluationForm('Làm rõ thêm.', 'criteria.0.note');
        $this->assertTrue($page->kanbanEvaluationForm['confirm_completion']);

        $page->updatedKanbanEvaluationForm('', 'notes');
        $this->assertFalse($page->kanbanEvaluationForm['confirm_completion']);

        $page->kanbanEvaluationForm['confirm_completion'] = true;
        $page->updatedKanbanEvaluationForm(9, 'criteria.0.score');
        $this->assertFalse($page->kanbanEvaluationForm['confirm_completion']);
    }

    public function test_interview_questions_can_use_job_and_scorecard_without_screening_analysis(): void
    {
        [$hr, $application] = $this->makeApplication(StatusApplicationEnum::INTERVIEWING);
        config()->set('services.gemini.key', 'test-key');
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'questions' => [[
                                    'criterion' => 'Năng lực chuyên môn',
                                    'type' => 'Tình huống',
                                    'question' => 'Bạn sẽ xử lý một buổi học có mức độ tiếp thu không đồng đều như thế nào?',
                                    'purpose' => 'Đánh giá khả năng xử lý tình huống',
                                    'expected_signal' => 'Có phương án phân nhóm và theo dõi tiến độ',
                                ]],
                            ], JSON_UNESCAPED_UNICODE),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $analysis = app(ApplicationAiAnalysisService::class)->generateInterviewQuestions(
            $application,
            [['name' => 'Năng lực chuyên môn']],
            $hr,
            'test',
        );

        $this->assertSame('completed', $analysis->status);
        $this->assertSame('interview_questions', $analysis->analysis_type);
        $this->assertSame('job_scorecard', data_get($analysis->result_json, 'basis'));
        $this->assertSame(
            'Bạn sẽ xử lý một buổi học có mức độ tiếp thu không đồng đều như thế nào?',
            data_get($analysis->result_json, 'questions.0.question'),
        );
        $this->assertDatabaseMissing('application_ai_analyses', [
            'application_id' => $application->id,
            'analysis_type' => 'screening',
        ]);
    }

    /**
     * @return array{0: User, 1: Application}
     */
    private function makeApplication(StatusApplicationEnum $status): array
    {
        $branch = Branch::query()->create([
            'name' => 'Kanban Transition Branch',
            'code' => 'KTB',
            'city' => 'can_tho',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Kanban Candidate',
            'email' => 'kanban-candidate@example.com',
            'phone' => '0901234567',
        ]);

        ScorecardTemplate::query()->firstOrCreate(
            ['name' => 'Mẫu đánh giá mặc định'],
            [
                'criteria' => [
                    ['name' => 'Năng lực chuyên môn', 'score' => null, 'note' => null],
                    ['name' => 'Phù hợp môi trường giáo dục', 'score' => null, 'note' => null],
                ],
                'is_default' => true,
                'created_by' => $hr->id,
            ],
        );

        $job = RecruitmentJob::query()->create([
            'title' => 'Kanban Job',
            'slug' => 'kanban-job-'.strtolower($status->value),
            'description' => 'Kanban transition test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/kanban/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => $status,
            'branch_id' => $branch->id,
            'applied_at' => now(),
        ]);

        return [$hr, $application];
    }
}
