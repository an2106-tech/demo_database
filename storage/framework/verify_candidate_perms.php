<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (['director', 'pm', 'hr'] as $roleName) {
    $role = Spatie\Permission\Models\Role::where('name', $roleName)->first();
    echo $roleName . ':' . $role->permissions->filter(fn ($permission) => str_contains($permission->name, 'Candidate'))->pluck('name')->implode(',') . PHP_EOL;
}
