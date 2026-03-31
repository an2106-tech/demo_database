<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(2)->get();

        
        $candidateData = [
            ['name' => 'Nguyen Van A', 'email' => 'ava@gmail.com', 'phone' => '0909123456', 'experience_years' => 4, 'match_score' => 85, 'metadata' => ['level'=>'senior']],
            ['name' => 'Le Thi B', 'email' => 'bth@gmail.com', 'phone' => '0909876543', 'experience_years' => 2, 'match_score' => 70, 'metadata' => ['level'=>'junior']],
            ['name' => 'Tran Van C', 'email' => 'ctran@gmail.com', 'phone' => '0912345678', 'experience_years' => 6, 'match_score' => 92, 'metadata' => ['level'=>'lead']],
        ];

        foreach ($candidateData as $data) {
            Candidate::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
