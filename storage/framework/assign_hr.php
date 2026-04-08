<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'hr@demo.local')->firstOrFail();
$user->syncRoles(['hr']);
$user->refresh();
echo 'assigned=' . implode(',', $user->roles->pluck('name')->all()) . PHP_EOL;
