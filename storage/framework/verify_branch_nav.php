<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'atus.pm@demo.local')->first();
Illuminate\Support\Facades\Auth::login($user);
echo 'can_jobs=' . (App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'can_apps=' . (App\Filament\Resources\Applications\ApplicationResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'can_departments=' . (App\Filament\Resources\Departments\DepartmentResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'can_workplaces=' . (App\Filament\Resources\Workplaces\WorkplaceResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'can_branches=' . (App\Filament\Resources\Branches\BranchResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'can_users=' . (App\Filament\Resources\Users\UserResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
