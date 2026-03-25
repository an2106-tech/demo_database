<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();

        User::updateOrCreate(
            ['email' => 'admin@demo.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'branch_id' => $branch?->id,
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'hr@demo.local'],
            [
                'name' => 'HR',
                'password' => Hash::make('password'),
                'role' => 'hr',
                'branch_id' => $branch?->id,
                'is_active' => true,
            ],
        );
    }
}
