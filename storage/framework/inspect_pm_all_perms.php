<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$role = Spatie\Permission\Models\Role::where('name', 'pm')->first();
echo $role->permissions->sortBy('name')->pluck('name')->implode(PHP_EOL) . PHP_EOL;
