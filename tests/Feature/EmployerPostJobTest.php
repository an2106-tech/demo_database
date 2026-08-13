<?php

namespace Tests\Feature;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Livewire\Client\Employers\ManageJobs;
use App\Livewire\Client\Employers\PostJob;
use App\Models\Branch;
use App\Models\Category;
use App\Models\RecruitmentJob;
use App\Models\Skill;
use App\Models\User;
use App\Models\Workplace;
use App\Services\AiMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployerPostJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_employer_login_from_post_jobs_entry(): void
    {
        $this->get(route('auth.post_jobs'))
            ->assertRedirect(route('employers.login'));
    }

    public function test_employer_can_create_recruitment_job_from_post_job_form(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Laravel']);
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Laravel Developer')
            ->set('description', 'Xay dung va van hanh he thong tuyen dung.')
            ->set('branch_id', $branch->id)
            ->set('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->set('positions_count', 2)
            ->set('salary_min', '15000000')
            ->set('salary_max', '25000000')
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('save')
            ->assertRedirect(route('employers.manage_jobs'));

        $job = RecruitmentJob::query()->first();

        $this->assertNotNull($job);
        $this->assertSame('Laravel Developer', $job->title);
        $this->assertSame($user->id, $job->created_by);
        $this->assertSame($branch->id, $job->branch_id);
        $this->assertSame(StatusRecruitmentJobsEnum::PENDING, $job->status);
        $this->assertEquals(['min' => 15000000.0, 'max' => 25000000.0], $job->salary_range);
    }

    public function test_branch_scoped_employer_cannot_post_job_for_another_branch(): void
    {
        $ownBranch = Branch::query()->create([
            'name' => 'FPT Polytechnic Da Nang',
            'code' => 'DN',
            'city' => 'Da Nang',
        ]);

        $otherBranch = Branch::query()->create([
            'name' => 'FPT Polytechnic Can Tho',
            'code' => 'CT',
            'city' => 'Can Tho',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $ownBranch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'PHP']);
        $category = Category::query()->create(['name' => 'Technology', 'slug' => 'technology']);

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Cross Branch Developer')
            ->set('description', 'Xay dung ung dung tuyen dung noi bo.')
            ->set('branch_id', $otherBranch->id)
            ->set('positions_count', 1)
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('save')
            ->assertHasErrors(['branch_id']);

        $this->assertDatabaseMissing('recruitment_jobs', [
            'title' => 'Cross Branch Developer',
        ]);
    }

    public function test_employer_can_create_job_with_workplace_without_department(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Quy Nhon',
            'code' => 'QN',
            'city' => 'Quy Nhon',
        ]);

        $workplace = Workplace::query()->create([
            'name' => 'Quy Nhon Office',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Livewire']);
        $category = Category::query()->create(['name' => 'Software', 'slug' => 'software']);

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Livewire Developer')
            ->set('description', 'Phat trien tinh nang cho cong thong tin viec lam.')
            ->set('workplace_id', $workplace->id)
            ->set('positions_count', 1)
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('save')
            ->assertRedirect(route('employers.manage_jobs'));

        $this->assertDatabaseHas('recruitment_jobs', [
            'title' => 'Livewire Developer',
            'branch_id' => $branch->id,
            'department_id' => null,
            'workplace_id' => $workplace->id,
        ]);
    }

    public function test_employer_can_generate_ai_job_draft_from_brief(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Da Nang',
            'code' => 'DN',
            'city' => 'Da Nang',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Laravel']);
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);

        $this->mock(AiMatchingService::class, function ($mock) {
            $mock->shouldReceive('cleanJobBrief')->andReturnUsing(fn($brief) => $brief);
            $mock->shouldReceive('draftRecruitmentJob')
                ->once()
                ->andReturn([
                    'title' => 'Senior Laravel Developer',
                    'description' => '<h3>Mô tả công việc</h3><ul><li>Xây dựng hệ thống nội bộ</li></ul>',
                    'highlights' => ['Làm việc với Laravel', 'Tối ưu hệ thống'],
                    'missing_information' => ['Chưa có mức lương'],
                ]);
        });

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Laravel Developer')
            ->set('ai_brief', 'Cần tuyển lập trình viên Laravel cho hệ thống nội bộ, ưu tiên kinh nghiệm REST API.')
            ->set('branch_id', $branch->id)
            ->set('positions_count', 2)
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('generateAiDraft')
            ->assertSet('title', 'Senior Laravel Developer')
            ->assertSet('description', '<h3>Mô tả công việc</h3><ul><li>Xây dựng hệ thống nội bộ</li></ul>')
            ->assertSet('ai_draft_highlights', ['Làm việc với Laravel', 'Tối ưu hệ thống'])
            ->assertSet('ai_draft_missing_information', ['Chưa có mức lương']);
    }

    public function test_employer_can_review_job_quality_with_ai(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Da Nang',
            'code' => 'DN',
            'city' => 'Da Nang',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Laravel']);
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);

        $this->mock(AiMatchingService::class, function ($mock) {
            $mock->shouldReceive('reviewRecruitmentJobDraft')
                ->once()
                ->andReturn([
                    'score' => 76,
                    'title_suggestion' => 'Senior Laravel Developer',
                    'issues' => ['Mô tả còn chung chung'],
                    'missing_information' => ['Lương', 'Hạn nộp'],
                    'suggestion_note' => 'JD đã có khung chính nhưng cần rõ hơn về quyền lợi và tiêu chí đầu vào.',
                ]);
        });

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Laravel Developer')
            ->set('overview', 'Xây dựng và phát triển hệ thống nội bộ.')
            ->set('responsibilities', "- Xây dựng tính năng mới\n- Tối ưu hiệu năng")
            ->set('requirements', "- 2 năm kinh nghiệm Laravel\n- Hiểu REST API")
            ->set('benefits', "- Môi trường ổn định\n- Có lộ trình phát triển")
            ->set('branch_id', $branch->id)
            ->set('positions_count', 2)
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('reviewAiDraft')
            ->assertSet('ai_quality_score', 76)
            ->assertSet('ai_quality_title_suggestion', 'Senior Laravel Developer')
            ->assertSet('ai_quality_issues', ['Mô tả còn chung chung'])
            ->assertSet('ai_quality_missing_information', ['Lương', 'Hạn nộp'])
            ->assertSet('ai_quality_note', 'JD đã có khung chính nhưng cần rõ hơn về quyền lợi và tiêu chí đầu vào.');
    }

    public function test_employer_can_improve_job_draft_with_ai(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Da Nang',
            'code' => 'DN',
            'city' => 'Da Nang',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Laravel']);
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);

        $this->mock(AiMatchingService::class, function ($mock) {
            $mock->shouldReceive('improveRecruitmentJobDraft')
                ->once()
                ->andReturn([
                    'title' => 'Senior Laravel Developer',
                    'description' => '<h3>Tổng quan</h3><p>Phát triển hệ thống nội bộ.</p><h3>Trách nhiệm chính</h3><ul><li>Xây dựng tính năng mới</li></ul>',
                    'changes' => ['Làm gọn mô tả', 'Rút tiêu đề'],
                    'note' => 'JD đã được làm rõ hơn và phù hợp để đăng.',
                ]);
        });

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Laravel Developer')
            ->set('description', '<p>Mô tả dài dòng cần tối ưu.</p>')
            ->set('branch_id', $branch->id)
            ->set('positions_count', 2)
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('improveAiDraft')
            ->assertSet('title', 'Senior Laravel Developer')
            ->assertSet('description', '<h3>Tổng quan</h3><p>Phát triển hệ thống nội bộ.</p><h3>Trách nhiệm chính</h3><ul><li>Xây dựng tính năng mới</li></ul>')
            ->assertSet('ai_improve_changes', ['Làm gọn mô tả', 'Rút tiêu đề'])
            ->assertSet('ai_improve_note', 'JD đã được làm rõ hơn và phù hợp để đăng.');
    }

    public function test_employer_can_save_job_from_structured_jd_sections(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Da Nang',
            'code' => 'DN',
            'city' => 'Da Nang',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Laravel']);
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Laravel Developer')
            ->set('overview', 'Xây dựng và phát triển hệ thống nội bộ.')
            ->set('responsibilities', "- Xây dựng tính năng mới\n- Tối ưu hiệu năng")
            ->set('requirements', "- 2 năm kinh nghiệm Laravel\n- Hiểu REST API")
            ->set('benefits', "- Môi trường ổn định\n- Có lộ trình phát triển")
            ->set('branch_id', $branch->id)
            ->set('positions_count', 2)
            ->set('skills', [$skill->id])
            ->set('selected_categories', [$category->id])
            ->call('save')
            ->assertRedirect(route('employers.manage_jobs'));

        $job = RecruitmentJob::query()->firstOrFail();

        $this->assertStringContainsString('<h3>Tổng quan</h3>', $job->description);
        $this->assertStringContainsString('<h3>Trách nhiệm chính</h3>', $job->description);
        $this->assertStringContainsString('<h3>Yêu cầu</h3>', $job->description);
        $this->assertStringContainsString('<h3>Quyền lợi</h3>', $job->description);
        $this->assertStringContainsString('Xây dựng và phát triển hệ thống nội bộ.', $job->description);
    }

    public function test_editing_title_that_keeps_same_slug_does_not_add_suffix(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Hue',
            'code' => 'HUE',
            'city' => 'Hue',
        ]);

        $user = User::factory()->create([
            'role' => 'director',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $skill = Skill::query()->create(['name' => 'Laravel']);
        $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);

        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer!',
            'slug' => 'laravel-developer',
            'description' => 'Mo ta cong viec hien tai.',
            'status' => StatusRecruitmentJobsEnum::PUBLISHED->value,
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $user->id,
        ]);
        $job->skills()->attach($skill->id, ['level' => 'mid', 'is_required' => true]);
        $job->categories()->attach($category->id);

        $this->actingAs($user);

        Livewire::test(PostJob::class, ['id' => $job->id])
            ->set('title', 'Laravel Developer')
            ->call('save')
            ->assertRedirect(route('employers.manage_jobs'));

        $this->assertDatabaseHas('recruitment_jobs', [
            'id' => $job->id,
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer',
        ]);
    }

    public function test_manage_jobs_only_shows_jobs_created_by_current_employer(): void
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ho Chi Minh',
            'code' => 'HCM',
            'city' => 'Ho Chi Minh',
        ]);

        $user = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
        ]);

        $otherUser = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
        ]);

        RecruitmentJob::query()->create([
            'title' => 'Backend PHP Developer',
            'slug' => 'backend-php-developer',
            'description' => 'Mo ta',
            'status' => StatusRecruitmentJobsEnum::PUBLISHED->value,
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $user->id,
        ]);

        RecruitmentJob::query()->create([
            'title' => 'Khong hien thi',
            'slug' => 'khong-hien-thi',
            'description' => 'Mo ta',
            'status' => StatusRecruitmentJobsEnum::PUBLISHED->value,
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $otherUser->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ManageJobs::class)
            ->assertSee('Backend PHP Developer')
            ->assertDontSee('Khong hien thi');
    }
}
