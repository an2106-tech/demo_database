<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();

        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.local'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'branch_id' => $branch?->id,
                'is_active' => true,
            ],
        );

        $hr = User::updateOrCreate(
            ['email' => 'hr@demo.local'],
            [
                'name'      => 'HR',
                'password'  => Hash::make('password'),
                'role'      => 'hr',
                'branch_id' => $branch?->id,
                'is_active' => true,
            ],
        );

        $director = User::updateOrCreate(
            ['email' => 'director@demo.local'],
            [
                'name'      => 'Giám đốc',
                'password'  => Hash::make('password'),
                'role'      => 'director',
                'branch_id' => $branch?->id,
                'is_active' => true,
            ],
        );

        $pm = User::updateOrCreate(
            ['email' => 'pm@demo.local'],
            [
                'name'      => 'PM',
                'password'  => Hash::make('password'),
                'role'      => 'pm',
                'branch_id' => $branch?->id,
                'is_active' => true,
            ],
        );

        $this->syncSeededUserRoles($admin);
        $this->syncSeededUserRoles($hr);
        $this->syncSeededUserRoles($director);
        $this->syncSeededUserRoles($pm);

        User::query()
            ->whereNotNull('role')
            ->get()
            ->each(fn (User $user) => $this->syncSeededUserRoles($user));
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
