<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\Application::query()->select('id','status','cv_path','candidate_id')->limit(10)->get() as $row) {
    echo json_encode($row->toArray(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
