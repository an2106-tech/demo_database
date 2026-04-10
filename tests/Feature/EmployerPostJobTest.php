<?php

namespace Tests\Feature;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Livewire\Client\Employers\ManageJobs;
use App\Livewire\Client\Employers\PostJob;
use App\Models\Branch;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployerPostJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_employer_login_from_post_jobs_entry(): void
    {
        $this->get(route('auth.post_jobs'))
            ->assertRedirect(route('auth.login', ['role' => 'employer']));
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

        $this->actingAs($user);

        Livewire::test(PostJob::class)
            ->set('title', 'Laravel Developer')
            ->set('description', 'Xay dung va van hanh he thong tuyen dung.')
            ->set('branch_id', $branch->id)
            ->set('status', StatusRecruitmentJobsEnum::PUBLISHED->value)
            ->set('positions_count', 2)
            ->set('salary_min', '15000000')
            ->set('salary_max', '25000000')
            ->call('save')
            ->assertRedirect(route('employers.manage_jobs'));

        $job = RecruitmentJob::query()->first();

        $this->assertNotNull($job);
        $this->assertSame('Laravel Developer', $job->title);
        $this->assertSame($user->id, $job->created_by);
        $this->assertSame($branch->id, $job->branch_id);
        $this->assertSame(StatusRecruitmentJobsEnum::PUBLISHED, $job->status);
        $this->assertEquals(['min' => 15000000.0, 'max' => 25000000.0], $job->salary_range);
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
