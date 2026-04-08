<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'roles=' . implode(',', Spatie\Permission\Models\Role::orderBy('name')->pluck('name')->all()) . PHP_EOL;
foreach (App\Models\User::whereIn('email', ['admin@demo.local', 'hr@demo.local'])->get() as $user) {
    echo $user->email . ':' . implode(',', $user->roles->pluck('name')->all()) . PHP_EOL;
}
