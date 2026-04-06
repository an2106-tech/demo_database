<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Workplace;
use Illuminate\Database\Seeder;

class WorkplaceSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get();
        if ($branches->isEmpty()) {
            $this->call(BranchSeeder::class);
            $branches = Branch::query()->get();
        }

        if ($branches->isEmpty()) {
            return;
        }

        $primaryBranch = Branch::query()->where('code', 'POLY-HCM')->first() ?? $branches->first();

        Workplace::updateOrCreate(
            ['branch_id' => $primaryBranch->id, 'name' => 'Office - Floor 10'],
            [
                'type' => 'office',
                'floor' => '10',
                'capacity' => 200,
                'directions' => 'Take the elevator to floor 10 and turn right.',
                'is_interview_room' => false,
                'is_active' => true,
            ],
        );

        Workplace::updateOrCreate(
            ['branch_id' => $primaryBranch->id, 'name' => 'Interview Room 1'],
            [
                'type' => 'interview_room',
                'floor' => '10',
                'room' => 'R.1001',
                'capacity' => 6,
                'directions' => 'Next to the pantry area.',
                'is_interview_room' => true,
                'is_active' => true,
            ],
        );

        foreach ($branches as $branch) {
            Workplace::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Remote'],
                [
                    'type' => 'remote',
                    'is_interview_room' => false,
                    'is_active' => true,
                ],
            );
        }
    }
}

