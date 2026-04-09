<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$job = App\Models\RecruitmentJob::with('branch')->where('title', 'Lập trình viên PHP Laravel')->first();
if (! $job) {
    echo json_encode(['job' => null], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
echo json_encode([
    'job_id' => $job->id,
    'job_title' => $job->title,
    'branch_id' => $job->branch_id,
    'branch_name' => $job->branch?->name,
    'branch_image' => $job->branch?->image,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
