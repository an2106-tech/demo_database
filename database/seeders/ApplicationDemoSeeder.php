<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use Illuminate\Database\Seeder;

class ApplicationDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Branch::query()->orderBy('id')->cursor() as $branch) {
            $candidate = Candidate::query()
                ->where('email', 'like', 'candidate-%')
                ->whereHas('user', fn ($q) => $q->where('branch_id', $branch->id))
                ->first();

            if (! $candidate) {
                continue;
            }

            $job = RecruitmentJob::query()
                ->where('branch_id', $branch->id)
                ->where('status', 'published')
                ->orderByDesc('id')
                ->first();

            if (! $job) {
                continue;
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
                    'utm_campaign' => 'application-demo-' . $branch->code,
                    'status' => 'cv_reviewing',
                    'salary_expected' => ['min' => 15000000, 'max' => 25000000],
                    'applied_at' => now(),
                    'rejected_reason' => null,
                ],
            );
        }
    }
}

