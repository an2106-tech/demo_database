<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\User::withTrashed()->get() as $user) {
    $user->syncAssignedRole();
    echo $user->email . ':' . implode(',', $user->roles->pluck('name')->all()) . PHP_EOL;
}
