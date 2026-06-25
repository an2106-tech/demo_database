<?php

namespace Tests\Feature;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Enums\VietnamProvince;
use App\Livewire\Client\Home;
use App\Models\Branch;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_search_redirects_to_browse_jobs_with_filters(): void
    {
        Livewire::test(Home::class)
            ->set('searchKeyword', 'Giang vien')
            ->set('searchCity', 'Ha Noi')
            ->set('searchDepartmentId', '5')
            ->call('searchJobs')
            ->assertRedirect(route('candidates.browse_job', [
                'q' => 'Giang vien',
                'city' => 'Ha Noi',
                'department_id' => 5,
            ]));
    }

    public function test_home_department_dropdown_only_shows_departments_with_open_jobs(): void
    {
        $openDepartment = Department::query()->create(['name' => 'Tuyển sinh đang tuyển', 'code' => 'OPEN']);
        $emptyDepartment = Department::query()->create(['name' => 'Phòng ban chưa có tin', 'code' => 'EMPTY']);
        $closedDepartment = Department::query()->create(['name' => 'Phòng ban đã đóng', 'code' => 'CLOSED']);

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic HCM',
            'code' => 'POLY-HCM',
            'city' => VietnamProvince::HO_CHI_MINH->value,
            'address' => 'Ho Chi Minh',
            'is_active' => true,
        ]);

        $creator = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        RecruitmentJob::query()->create([
            'title' => 'Chuyên viên tuyển sinh',
            'slug' => 'chuyen-vien-tuyen-sinh',
            'description' => 'Tuyen dung noi bo FPT Education.',
            'status' => StatusRecruitmentJobsEnum::PUBLISHED->value,
            'branch_id' => $branch->id,
            'department_id' => $openDepartment->id,
            'positions_count' => 1,
            'created_by' => $creator->id,
        ]);

        RecruitmentJob::query()->create([
            'title' => 'Tin đã đóng',
            'slug' => 'tin-da-dong',
            'description' => 'Tuyen dung noi bo FPT Education.',
            'status' => StatusRecruitmentJobsEnum::PENDING->value,
            'branch_id' => $branch->id,
            'department_id' => $closedDepartment->id,
            'positions_count' => 1,
            'created_by' => $creator->id,
        ]);

        Livewire::test(Home::class)
            ->assertSee($openDepartment->name)
            ->assertDontSee($emptyDepartment->name)
            ->assertDontSee($closedDepartment->name);
    }
}
