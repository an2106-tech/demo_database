<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = Candidate::take(3)->pluck('id');
        $jobs = RecruitmentJob::take(3)->pluck('id');

        if ($candidates->isEmpty() || $jobs->isEmpty()) {
            return;
        }

        foreach ($jobs as $jobId) {
            foreach ($candidates as $candidateId) {
                Application::updateOrCreate(
                    ['job_id' => $jobId, 'candidate_id' => $candidateId],
                    [
                        'cv_path' => 'cv/' . $candidateId . '-' . $jobId . '.pdf',
                        'source' => 'website',
                        'status' => 'new',
                        'salary_expected' => ['min' => 1000, 'max' => 1500],
                        'applied_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
