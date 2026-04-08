<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$role = Spatie\Permission\Models\Role::where('name', 'pm')->first();
if (! $role) {
    echo "missing-role" . PHP_EOL;
    exit;
}
echo 'pm_permissions=' . $role->permissions->pluck('name')->sort()->implode(',') . PHP_EOL;
