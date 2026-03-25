<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Workplace;
use Illuminate\Database\Seeder;

class WorkplaceSeeder extends Seeder
{
    public function run(): void
    {
        $hcm = Branch::where('code', 'HCM-HQ')->first();
        if (! $hcm) {
            return;
        }

        Workplace::updateOrCreate(
            ['branch_id' => $hcm->id, 'name' => 'Office - Floor 10'],
            [
                'type' => 'office',
                'floor' => '10',
                'capacity' => 200,
                'directions' => 'Đi thang máy lên tầng 10, quẹo phải.',
                'is_interview_room' => false,
                'is_active' => true,
            ],
        );

        Workplace::updateOrCreate(
            ['branch_id' => $hcm->id, 'name' => 'Interview Room 1'],
            [
                'type' => 'interview_room',
                'floor' => '10',
                'room' => 'P.1001',
                'capacity' => 6,
                'directions' => 'Ngay cạnh khu pantry.',
                'is_interview_room' => true,
                'is_active' => true,
            ],
        );

        Workplace::updateOrCreate(
            ['branch_id' => $hcm->id, 'name' => 'Remote'],
            [
                'type' => 'remote',
                'is_interview_room' => false,
                'is_active' => true,
            ],
        );
    }
}
