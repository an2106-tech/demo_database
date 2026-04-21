<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$apps = App\Models\Application::query()->with(['candidate', 'latestOffer'])->latest('id')->take(5)->get();
foreach ($apps as $a) {
    $status = $a->status instanceof BackedEnum ? $a->status->value : (string) $a->status;
    echo 'APP#'.$a->id.' app_status='.$status.PHP_EOL;
}
