<?php

namespace Tests\Feature;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Enums\VietnamProvince;
use App\Livewire\Client\BrowseJobs;
use App\Models\Branch;
use App\Models\Department;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrowseJobsLocationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_jobs_by_common_location_aliases(): void
    {
        $hcmJob = $this->createJob('Academic Advisor HCM', VietnamProvince::HO_CHI_MINH->value, 'Ho Chi Minh Campus');
        $haNoiJob = $this->createJob('Academic Advisor Ha Noi', VietnamProvince::HA_NOI->value, 'Ha Noi Campus');
        $canThoJob = $this->createJob('Academic Advisor Can Tho', VietnamProvince::CAN_THO->value, 'Can Tho Campus');

        Livewire::test(BrowseJobs::class)
            ->set('city', 'hcm')
            ->assertSee($hcmJob->title)
            ->assertDontSee($haNoiJob->title)
            ->assertDontSee($canThoJob->title);

        Livewire::test(BrowseJobs::class)
            ->set('city', 'hn')
            ->assertSee($haNoiJob->title)
            ->assertDontSee($hcmJob->title)
            ->assertDontSee($canThoJob->title);

        Livewire::test(BrowseJobs::class)
            ->set('city', 'ct')
            ->assertSee($canThoJob->title)
            ->assertDontSee($hcmJob->title)
            ->assertDontSee($haNoiJob->title);
    }

    public function test_it_filters_jobs_by_accented_and_unaccented_location_names(): void
    {
        $hcmJob = $this->createJob('Student Support HCM', VietnamProvince::HO_CHI_MINH->value, 'Ho Chi Minh Campus');
        $haNoiJob = $this->createJob('Student Support Ha Noi', VietnamProvince::HA_NOI->value, 'Ha Noi Campus');

        Livewire::test(BrowseJobs::class)
            ->set('city', 'Hồ Chí Minh')
            ->assertSee($hcmJob->title)
            ->assertDontSee($haNoiJob->title);

        Livewire::test(BrowseJobs::class)
            ->set('city', 'ha noi')
            ->assertSee($haNoiJob->title)
            ->assertDontSee($hcmJob->title);
    }

    public function test_department_options_are_limited_by_open_jobs_and_selected_location(): void
    {
        $hcmDepartment = Department::query()->create(['name' => 'Tuyển sinh HCM', 'code' => 'TS-HCM']);
        $haNoiDepartment = Department::query()->create(['name' => 'Đào tạo Hà Nội', 'code' => 'DT-HN']);
        $emptyDepartment = Department::query()->create(['name' => 'Phòng ban chưa tuyển', 'code' => 'EMPTY']);

        $this->createJob('Admissions HCM', VietnamProvince::HO_CHI_MINH->value, 'Ho Chi Minh Campus', $hcmDepartment);
        $this->createJob('Training Ha Noi', VietnamProvince::HA_NOI->value, 'Ha Noi Campus', $haNoiDepartment);

        Livewire::test(BrowseJobs::class)
            ->assertSee($hcmDepartment->name)
            ->assertSee($haNoiDepartment->name)
            ->assertDontSee($emptyDepartment->name)
            ->set('city', 'hcm')
            ->assertSee($hcmDepartment->name)
            ->assertDontSee($haNoiDepartment->name)
            ->assertDontSee($emptyDepartment->name)
            ->set('city', 'hn')
            ->assertSee($haNoiDepartment->name)
            ->assertDontSee($hcmDepartment->name)
            ->assertDontSee($emptyDepartment->name);
    }

    private function createJob(string $title, string $city, string $branchName, ?Department $department = null): RecruitmentJob
    {
        $branch = Branch::query()->create([
            'name' => $branchName,
            'code' => str($title)->slug()->upper()->limit(20, '')->toString(),
            'city' => $city,
            'address' => $branchName,
            'is_active' => true,
        ]);

        $creator = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        return RecruitmentJob::query()->create([
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'description' => 'Tuyen dung noi bo FPT Education.',
            'status' => StatusRecruitmentJobsEnum::PUBLISHED->value,
            'branch_id' => $branch->id,
            'department_id' => $department?->id,
            'positions_count' => 1,
            'created_by' => $creator->id,
        ]);
    }
}
