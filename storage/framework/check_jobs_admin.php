<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@demo.local')->first();
if (! $user) {
    echo "missing-admin" . PHP_EOL;
    exit;
}

Illuminate\Support\Facades\Auth::login($user);

echo 'user=' . $user->email . PHP_EOL;
echo 'roles=' . implode(',', $user->roles->pluck('name')->all()) . PHP_EOL;
echo 'resource_count=' . App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource::getEloquentQuery()->count() . PHP_EOL;
foreach (App\Enums\StatusRecruitmentJobsEnum::cases() as $case) {
    echo $case->value . '=' . App\Filament\Resources\RecruitmentJobs\RecruitmentJobResource::getEloquentQuery()->where('status', $case->value)->count() . PHP_EOL;
}
