<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['super_admin', 'director', 'pm', 'hr'] as $roleName) {
    $role = Spatie\Permission\Models\Role::where('name', $roleName)->first();
    echo $roleName . ':' . ($role ? $role->permissions->count() : 0) . PHP_EOL;
}

echo 'has_policy=' . (class_exists(App\Policies\RecruitmentJobPolicy::class) ? 'yes' : 'no') . PHP_EOL;
echo 'jobs=' . App\Models\RecruitmentJob::count() . PHP_EOL;
