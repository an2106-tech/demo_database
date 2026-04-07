<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('skills')) {
            return;
        }

        $now = now();

        $skills = [
            ['name' => 'PHP', 'category' => 'Backend'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'REST API', 'category' => 'Backend'],
            ['name' => 'Git', 'category' => 'Tools'],
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'JavaScript', 'category' => 'Frontend'],
            ['name' => 'HTML/CSS', 'category' => 'Frontend'],
            ['name' => 'Testing', 'category' => 'QA'],
            ['name' => 'Selenium', 'category' => 'QA'],
            ['name' => 'CI/CD', 'category' => 'DevOps'],
        ];

        foreach ($skills as $skill) {
            DB::table('skills')->updateOrInsert(
                ['name' => $skill['name']],
                [
                    'category' => $skill['category'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}

