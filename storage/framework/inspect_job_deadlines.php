<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\RecruitmentJob::select('id', 'title', 'status', 'deadline')->get() as $job) {
    echo $job->id . ' | ' . $job->title . ' | ' . ($job->status instanceof BackedEnum ? $job->status->value : $job->status) . ' | ' . ($job->deadline?->format('Y-m-d') ?? 'null') . PHP_EOL;
}
