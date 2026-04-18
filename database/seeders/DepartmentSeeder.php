<?php

namespace Database\Seeders;

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

        foreach (Branch::query()->orderBy('id')->cursor() as $branch) {
            Department::updateOrCreate(
                ['code' => 'DEPT-' . $branch->code],
                [
                    'name' => 'Ban điều hành — ' . $branch->name,
                    'branch_id' => $branch->id,
                    'description' => 'Phòng ban mẫu gắn với chi nhánh.',
                ],
            );
        }
    }
}
