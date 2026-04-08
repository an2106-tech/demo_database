<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (Spatie\Permission\Models\Permission::where('name', 'like', '%RecruitmentJob%')->orWhere('name', 'like', '%recruitment%')->orderBy('name')->pluck('name') as $name) {
    echo $name . PHP_EOL;
}
