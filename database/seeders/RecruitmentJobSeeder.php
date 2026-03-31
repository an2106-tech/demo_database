<?php

namespace Database\Seeders;

use App\Models\RecruitmentJob;
use App\Models\Department;
use App\Models\Workplace;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecruitmentJobSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::first();
        $workplace = Workplace::first();
        $creator = User::first();

        if (! $department || ! $workplace || ! $creator) {
            return;
        }

        $jobs = [
            [
                'title' => 'Lập trình viên PHP Laravel',
                'slug' => 'lap-trinh-vien-php-laravel',
                'description' => 'Phát triển hệ thống ứng dụng web với Laravel 10/11, REST API, tích hợp third-party. ',
                'status' => 'published',
                'salary_range' => ['min' => 1200, 'max' => 2200, 'currency' => 'USD'],
                'deadline' => now()->addDays(30)->toDateString(),
                'positions_count' => 3,
                'public_url' => '/jobs/lap-trinh-vien-php-laravel',
                'thumbnail' => 'assets/img/company-logo-4.png',
                'department_id' => $department->id,
                'branch_id' => $department->branch_id,
                'workplace_id' => $workplace->id,
                'created_by' => $creator->id,
            ],
            [
                'title' => 'Chuyên viên QA tự động',
                'slug' => 'chuyen-vien-qa-tu-dong',
                'description' => 'Viết test tự động, tham gia CI/CD, kiểm thử chất lượng phần mềm.',
                'status' => 'published',
                'salary_range' => ['min' => 1000, 'max' => 1800, 'currency' => 'USD'],
                'deadline' => now()->addDays(25)->toDateString(),
                'positions_count' => 2,
                'public_url' => '/jobs/chuyen-vien-qa-tu-dong',
                'thumbnail' => 'assets/img/company-logo-2.png',
                'department_id' => $department->id,
                'branch_id' => $department->branch_id,
                'workplace_id' => $workplace->id,
                'created_by' => $creator->id,
            ],
        ];

        foreach ($jobs as $job) {
            RecruitmentJob::updateOrCreate(
                ['slug' => $job['slug']],
                array_merge($job, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
