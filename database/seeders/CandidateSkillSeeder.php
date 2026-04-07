<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CandidateSkillSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('candidate_skills') || ! Schema::hasTable('skills')) {
            return;
        }

        $candidates = Candidate::query()->select(['id', 'experience_years'])->get();

        if ($candidates->isEmpty()) {
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

        $candidateSkills = [
            'PHP',
            'Laravel',
            'MySQL',
            'Git',
            'REST API',
            'JavaScript',
            'HTML/CSS',
        ];

        foreach ($candidates as $candidate) {
            $years = (int) ($candidate->experience_years ?? 0);
            $proficiency = $years >= 5 ? 4 : ($years >= 2 ? 3 : 2);

            foreach ($candidateSkills as $skillName) {
                $skillId = $skillIds[$skillName] ?? null;
                if (! $skillId) {
                    continue;
                }

                DB::table('candidate_skills')->updateOrInsert(
                    ['candidate_id' => $candidate->id, 'skill_id' => $skillId],
                    [
                        'proficiency_level' => $proficiency,
                        'years_experience' => min($years, 10),
                    ],
                );
            }
        }
    }
}

