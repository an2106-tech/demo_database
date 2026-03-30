<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchIds = Branch::query()->pluck('id');

        if ($branchIds->isEmpty()) {
            $this->call(BranchSeeder::class);
            $branchIds = Branch::query()->pluck('id');
        }

        Department::query()
            ->whereNull('branch_id')
            ->get()
            ->each(function (Department $department) use ($branchIds): void {
                $department->update([
                    'branch_id' => $branchIds->random(),
                ]);
            });

        Department::factory()
            ->count(10)
            ->state(fn () => [
                'branch_id' => $branchIds->random(),
            ])
            ->create();
    }
}
