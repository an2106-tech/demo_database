<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'hr@demo.local')->first();
if (! $user) {
    echo "missing-user" . PHP_EOL;
    exit;
}
echo 'db-role=' . $user->role . PHP_EOL;
echo 'guard-roles=' . implode(',', Spatie\Permission\Models\Role::pluck('name')->all()) . PHP_EOL;
echo 'assigned=' . implode(',', $user->roles->pluck('name')->all()) . PHP_EOL;
