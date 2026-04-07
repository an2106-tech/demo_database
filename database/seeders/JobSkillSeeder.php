<?php

namespace Database\Seeders;

use App\Models\RecruitmentJob;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobSkillSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('job_skills') || ! Schema::hasTable('skills')) {
            return;
        }

        $jobs = RecruitmentJob::query()->select(['id', 'slug', 'title'])->get();

        if ($jobs->isEmpty()) {
            return;
        }

        $skillIds = DB::table('skills')->pluck('id', 'name')->all();

        if (empty($skillIds)) {
            $this->call(SkillSeeder::class);
            $skillIds = DB::table('skills')->pluck('id', 'name')->all();
        }

        if (empty($skillIds)) {
            return;
        }

        $defaultSkills = ['Git', 'REST API'];

        $perJobSkillsBySlug = [
            'lap-trinh-vien-php-laravel' => ['PHP', 'Laravel', 'MySQL', 'REST API', 'Docker', 'Git'],
            'chuyen-vien-qa-tu-dong' => ['Testing', 'Selenium', 'CI/CD', 'Git'],
        ];

        foreach ($jobs as $job) {
            $skillNames = $perJobSkillsBySlug[$job->slug] ?? $defaultSkills;

            foreach ($skillNames as $skillName) {
                $skillId = $skillIds[$skillName] ?? null;
                if (! $skillId) {
                    continue;
                }

                DB::table('job_skills')->updateOrInsert(
                    ['job_id' => $job->id, 'skill_id' => $skillId],
                    ['level' => 'mid', 'is_required' => true],
                );
            }
        }
    }
}

