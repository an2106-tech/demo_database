<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'pm@demo.local')->first();
if (! $user) {
    echo "missing-user" . PHP_EOL;
    exit;
}
Illuminate\Support\Facades\Auth::login($user);
echo 'perm_app=' . ($user->can('ViewAny:Application') ? 'yes' : 'no') . PHP_EOL;
echo 'perm_job=' . ($user->can('ViewAny:RecruitmentJob') ? 'yes' : 'no') . PHP_EOL;
echo 'user_res=' . (App\Filament\Resources\Users\UserResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'app_res=' . (App\Filament\Resources\Applications\ApplicationResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
echo 'job_res=' . (App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
