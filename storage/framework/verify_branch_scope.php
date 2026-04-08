<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'atus.pm@demo.local')->first();
if (! $user) {
    echo "missing-user" . PHP_EOL;
    exit;
}
Illuminate\Support\Facades\Auth::login($user);
echo 'branch_id=' . ($user->branch_id ?? 'null') . PHP_EOL;
echo 'jobs=' . App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource::getEloquentQuery()->count() . PHP_EOL;
echo 'applications=' . App\Filament\Resources\Applications\ApplicationResource::getEloquentQuery()->count() . PHP_EOL;
echo 'departments=' . App\Filament\Resources\Departments\DepartmentResource::getEloquentQuery()->count() . PHP_EOL;
echo 'workplaces=' . App\Filament\Resources\Workplaces\WorkplaceResource::getEloquentQuery()->count() . PHP_EOL;
echo 'branches=' . App\Filament\Resources\Branches\BranchResource::getEloquentQuery()->count() . PHP_EOL;
echo 'users=' . App\Filament\Resources\Users\UserResource::getEloquentQuery()->count() . PHP_EOL;
