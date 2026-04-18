<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BranchPersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->orderBy('id')->get();

        if ($branches->isEmpty()) {
            $this->call(BranchSeeder::class);
            $branches = Branch::query()->orderBy('id')->get();
        }

        foreach ($branches as $branch) {
            $key = strtolower(str_replace([' ', '.'], '-', (string) $branch->code));

            $hr = User::updateOrCreate(
                ['email' => "hr-{$key}@demo.local"],
                [
                    'name' => 'HR — ' . $branch->name,
                    'password' => Hash::make('password'),
                    'role' => 'hr',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            $director = User::updateOrCreate(
                ['email' => "director-{$key}@demo.local"],
                [
                    'name' => 'Giám đốc chi nhánh — ' . $branch->name,
                    'password' => Hash::make('password'),
                    'role' => 'director',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            $pm = User::updateOrCreate(
                ['email' => "pm-{$key}@demo.local"],
                [
                    'name' => 'PM / Trưởng nhóm — ' . $branch->name,
                    'password' => Hash::make('password'),
                    'role' => 'pm',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            $candidateUser = User::updateOrCreate(
                ['email' => "candidate-{$key}@demo.local"],
                [
                    'name' => 'Ứng viên mẫu — ' . $branch->name,
                    'password' => Hash::make('password'),
                    'role' => 'candidate',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            Candidate::updateOrCreate(
                ['email' => "candidate-{$key}@demo.local"],
                [
                    'user_id' => $candidateUser->id,
                    'name' => $candidateUser->name,
                    'phone' => sprintf('090%07d', ($branch->id * 137) % 10000000),
                    'cv_file' => 'candidates/cv/demo-cv-placeholder.pdf',
                    'experience_years' => min(3 + ($branch->id % 5), 12),
                    'match_score' => 70 + ($branch->id % 25),
                    'blacklist' => false,
                    'metadata' => [
                        'seed_branch_code' => $branch->code,
                        'note' => 'Ứng viên mẫu theo chi nhánh (db:seed).',
                    ],
                ],
            );

            $this->syncSeededUserRoles($hr);
            $this->syncSeededUserRoles($director);
            $this->syncSeededUserRoles($pm);
            $this->syncSeededUserRoles($candidateUser);
        }
    }

    protected function syncSeededUserRoles(User $user): void
    {
        $user = User::query()->find($user->getKey()) ?? $user;
        $availableRoles = Role::query()->pluck('name')->all();

        $roles = match ($user->role) {
            'admin' => array_values(array_intersect(['super_admin'], $availableRoles)),
            default => in_array($user->role, $availableRoles, true) ? [$user->role] : [],
        };

        $user->syncRoles($roles);
    }
}
