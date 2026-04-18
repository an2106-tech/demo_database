<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Workplace;
use Illuminate\Database\Seeder;

class WorkplaceSeeder extends Seeder
{
    public function run(): void
    {
        Workplace::query()->delete();

        $branches = Branch::query()->get();
        if ($branches->isEmpty()) {
            $this->call(BranchSeeder::class);
            $branches = Branch::query()->get();
        }

        if ($branches->isEmpty()) {
            return;
        }

        foreach ($branches as $branch) {
            // 1. Main Office
            Workplace::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Văn phòng chính - ' . $branch->name],
                [
                    'type' => 'office',
                    'floor' => 'Tầng 1',
                    'capacity' => rand(50, 200),
                    'directions' => 'Khu vực làm việc chính tại ' . $branch->address,
                    'is_interview_room' => false,
                    'is_active' => true,
                ],
            );

            // 2. Interview Room
            $roomNumber = rand(101, 199);
            Workplace::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => "Phòng phỏng vấn P.$roomNumber"],
                [
                    'type' => 'interview_room',
                    'floor' => 'Tầng 1',
                    'room' => "P.$roomNumber",
                    'capacity' => rand(4, 10),
                    'directions' => 'Gần khu vực lễ tân của ' . $branch->name,
                    'is_interview_room' => true,
                    'is_active' => true,
                ],
            );

            // 3. Meeting Room
            Workplace::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Phòng họp Team - ' . $branch->code],
                [
                    'type' => 'meeting_room',
                    'floor' => 'Tầng 2',
                    'capacity' => rand(10, 30),
                    'directions' => 'Di chuyển lên tầng 2, dãy phòng họp phía bên trái.',
                    'is_interview_room' => false,
                    'is_active' => true,
                ],
            );

            // 4. Remote
            Workplace::updateOrCreate(
                ['branch_id' => $branch->id, 'name' => 'Làm việc từ xa (Remote)'],
                [
                    'type' => 'remote',
                    'is_interview_room' => false,
                    'is_active' => true,
                ],
            );
        }
    }
}


