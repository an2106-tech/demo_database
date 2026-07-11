<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

        $this->ensureDemoCvPlaceholder();

        foreach ($branches as $branch) {
            $key = strtolower(str_replace([' ', '.'], '-', (string) $branch->code));

            $hr = User::updateOrCreate(
                ['email' => "hr-{$key}@demo.local"],
                [
                    'name' => 'HR — ' . $branch->name,
                    'password' => Hash::make('123456'),
                    'role' => 'hr',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            $director = User::updateOrCreate(
                ['email' => "director-{$key}@demo.local"],
                [
                    'name' => 'Giám đốc chi nhánh — ' . $branch->name,
                    'password' => Hash::make('123456'),
                    'role' => 'director',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            $pm = User::updateOrCreate(
                ['email' => "pm-{$key}@demo.local"],
                [
                    'name' => 'PM / Trưởng nhóm — ' . $branch->name,
                    'password' => Hash::make('123456'),
                    'role' => 'pm',
                    'branch_id' => $branch->id,
                    'is_active' => true,
                ],
            );

            $candidateUser = User::updateOrCreate(
                ['email' => "candidate-{$key}@demo.local"],
                [
                    'name' => 'Ứng viên mẫu — ' . $branch->name,
                    'password' => Hash::make('123456'),
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

    private function ensureDemoCvPlaceholder(): void
    {
        $path = 'candidates/cv/demo-cv-placeholder.pdf';

        if (Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->put($path, <<<'PDF'
%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>
endobj
4 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
5 0 obj
<< /Length 144 >>
stream
BT
/F1 22 Tf
72 720 Td
(Demo CV Placeholder) Tj
/F1 12 Tf
0 -32 Td
(This file is generated for seeded candidate demo data.) Tj
ET
endstream
endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000241 00000 n
0000000311 00000 n
trailer
<< /Size 6 /Root 1 0 R >>
startxref
505
%%EOF
PDF);
    }
}
