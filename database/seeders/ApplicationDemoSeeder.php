<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use Illuminate\Database\Seeder;

class ApplicationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $candidate = Candidate::query()->firstOrCreate(
            ['email' => 'an.nguyentrong@demo.local'],
            [
                'name' => 'Nguyễn Trọng An',
                'phone' => '0900000099',
                'cv_file' => 'candidates/cv/nguyen-trong-an-demo.pdf',
                'experience_years' => 2,
                'match_score' => null,
                'match_reasons' => null,
                'blacklist' => false,
                'blacklist_reason' => null,
                'blacklisted_at' => null,
                'blacklisted_by' => null,
                'metadata' => [
                    'linkedin' => 'https://linkedin.com/in/nguyen-trong-an-demo',
                    'note' => 'Dữ liệu mẫu để test luồng ứng tuyển.',
                ],
            ],
        );

        $job = RecruitmentJob::query()
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        if (! $job) {
            // JobSkillSeeder will normally create jobs; fallback just in case.
            $this->call(JobSkillSeeder::class);
            $job = RecruitmentJob::query()->where('status', 'published')->orderByDesc('id')->first();
        }

        if (! $job) {
            return;
        }

        Application::query()->updateOrCreate(
            [
                'job_id' => $job->id,
                'candidate_id' => $candidate->id,
            ],
            [
                'cv_path' => $candidate->cv_file,
                'source' => 'website',
                'referral_user_id' => null,
                'utm_source' => 'demo',
                'utm_medium' => 'seed',
                'utm_campaign' => 'application-demo',
                'status' => 'new',
                'salary_expected' => ['min' => 15000000, 'max' => 25000000],
                'applied_at' => now(),
                'rejected_reason' => null,
            ],
        );
    }
}

