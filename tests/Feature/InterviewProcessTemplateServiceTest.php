<?php

namespace Tests\Feature;

use App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource;
use App\Livewire\Client\Job\JobDetail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\InterviewProcessTemplate;
use App\Models\RecruitmentJob;
use App\Models\ScorecardTemplate;
use App\Models\User;
use App\Services\InterviewProcessTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InterviewProcessTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_keeps_process_snapshot_when_shared_template_changes(): void
    {
        [$job, $template] = $this->createJobWithProcess();
        $originalSnapshot = $job->interview_process_snapshot;

        $template->update(['name' => 'Tên quy trình đã thay đổi']);
        $template->rounds()->where('round_number', 1)->update([
            'name' => 'Tên vòng đã thay đổi',
        ]);

        $job->refresh();

        $this->assertSame($originalSnapshot, $job->interview_process_snapshot);
        $this->assertSame('Quy trình kiểm thử', $job->interview_process_snapshot['name']);
        $this->assertSame('Phỏng vấn chuyên môn', $job->interview_process_snapshot['rounds'][0]['name']);
    }

    public function test_process_can_be_changed_before_job_receives_applications(): void
    {
        [$job] = $this->createJobWithProcess();
        $replacement = $this->createProcessTemplate('replacement-process', 'Quy trình thay thế');

        $job->update(['interview_process_template_id' => $replacement->id]);
        $job->refresh();

        $this->assertSame($replacement->id, $job->interview_process_template_id);
        $this->assertSame('Quy trình thay thế', $job->interview_process_snapshot['name']);
    }

    public function test_process_cannot_be_changed_after_job_receives_an_application(): void
    {
        [$job] = $this->createJobWithProcess();
        $replacement = $this->createProcessTemplate('locked-replacement', 'Quy trình không được áp dụng');
        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên kiểm thử',
            'email' => 'candidate@example.test',
        ]);

        Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'cvs/test.pdf',
            'status' => 'screening',
        ]);

        try {
            $job->update(['interview_process_template_id' => $replacement->id]);
            $this->fail('Quy trình vẫn có thể bị thay đổi sau khi đã có ứng viên.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Không thể đổi quy trình khi tin tuyển dụng đã có ứng viên.',
                $exception->errors()['interview_process_template_id'][0]
            );
        }

        $job->refresh();
        $this->assertNotSame($replacement->id, $job->interview_process_template_id);
    }

    public function test_legacy_job_without_snapshot_resolves_to_one_round(): void
    {
        [$branch, $creator] = $this->createBranchAndCreator();
        $job = RecruitmentJob::query()->create([
            'title' => 'Tin tuyển dụng cũ',
            'slug' => 'tin-tuyen-dung-cu',
            'description' => 'Mô tả công việc.',
            'status' => 'draft',
            'branch_id' => $branch->id,
            'created_by' => $creator->id,
        ]);

        $process = app(InterviewProcessTemplateService::class)->resolveForJob($job);

        $this->assertSame(1, $process['round_count']);
        $this->assertSame('legacy-single-round', $process['code']);
        $this->assertCount(1, $process['rounds']);
    }

    public function test_admin_job_form_renders_process_selection_and_preview(): void
    {
        [, $creator] = $this->createBranchAndCreator();
        $permissions = collect(['ViewAny:RecruitmentJob', 'Create:RecruitmentJob'])
            ->map(fn (string $name): Permission => Permission::query()->create([
                'name' => $name,
                'guard_name' => 'web',
            ]));
        $role = Role::query()->create([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($permissions);
        $creator->update(['role' => 'admin']);
        $template = $this->createProcessTemplate(
            'filament-process',
            'Quy trình hiển thị trên Filament',
            $creator
        );

        $this->actingAs($creator)
            ->get(RecruitmentJobResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Quy trình phỏng vấn')
            ->assertSee($template->name);
    }

    public function test_public_job_process_only_exposes_candidate_facing_round_labels(): void
    {
        [$job] = $this->createJobWithProcess();

        $summary = app(InterviewProcessTemplateService::class)->publicSummaryForJob($job);

        $this->assertSame(2, $summary['round_count']);
        $this->assertSame('Phỏng vấn dành cho ứng viên', $summary['rounds'][0]['label']);
        $this->assertSame('Trao đổi với đơn vị tuyển dụng', $summary['rounds'][1]['label']);
        $this->assertArrayNotHasKey('objective', $summary['rounds'][0]);
        $this->assertArrayNotHasKey('scorecard_template', $summary['rounds'][0]);

        Livewire::test(JobDetail::class, ['slug' => $job->slug])
            ->assertSee('Quy trình tuyển dụng dự kiến')
            ->assertDontSee('2 vòng phỏng vấn')
            ->assertSee('Phỏng vấn dành cho ứng viên')
            ->assertSee('Trao đổi với đơn vị tuyển dụng')
            ->assertDontSee('Thống nhất nội bộ');
    }

    /**
     * @return array{RecruitmentJob, InterviewProcessTemplate}
     */
    private function createJobWithProcess(): array
    {
        [$branch, $creator] = $this->createBranchAndCreator();
        $template = $this->createProcessTemplate('test-process', 'Quy trình kiểm thử', $creator);
        $firstRound = $template->rounds()->firstOrFail();
        $template->rounds()->create([
            'round_number' => 2,
            'name' => 'Thống nhất nội bộ',
            'candidate_label' => 'Trao đổi với đơn vị tuyển dụng',
            'objective' => 'Xác nhận kết quả và điều kiện tiếp nhận.',
            'scorecard_template_id' => $firstRound->scorecard_template_id,
            'evaluator_roles' => ['director', 'hr'],
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Công nghệ thông tin',
            'slug' => 'giang-vien-cong-nghe-thong-tin',
            'description' => 'Mô tả công việc.',
            'status' => 'draft',
            'branch_id' => $branch->id,
            'created_by' => $creator->id,
            'interview_process_template_id' => $template->id,
        ]);

        return [$job->refresh(), $template];
    }

    private function createProcessTemplate(
        string $code,
        string $name,
        ?User $creator = null
    ): InterviewProcessTemplate {
        $creator ??= User::query()->firstOrFail();
        $scorecard = ScorecardTemplate::query()->create([
            'name' => 'Mẫu đánh giá '.$code,
            'criteria' => [
                ['name' => 'Năng lực chuyên môn', 'score' => null, 'note' => null],
            ],
            'is_default' => true,
            'created_by' => $creator->id,
        ]);
        $template = InterviewProcessTemplate::query()->create([
            'code' => $code,
            'name' => $name,
            'description' => 'Quy trình phục vụ kiểm thử.',
            'is_default' => false,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);
        $template->rounds()->create([
            'round_number' => 1,
            'name' => 'Phỏng vấn chuyên môn',
            'candidate_label' => 'Phỏng vấn dành cho ứng viên',
            'objective' => 'Đánh giá năng lực theo vị trí.',
            'scorecard_template_id' => $scorecard->id,
            'evaluator_roles' => ['hr', 'pm'],
        ]);

        return $template;
    }

    /**
     * @return array{Branch, User}
     */
    private function createBranchAndCreator(): array
    {
        $branch = Branch::query()->firstOrCreate(
            ['code' => 'CT'],
            [
                'name' => 'FPT Polytechnic Cần Thơ',
                'city' => 'Cần Thơ',
            ]
        );
        $creator = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        return [$branch, $creator];
    }
}
