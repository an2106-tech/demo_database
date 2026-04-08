<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'jobs=' . App\Models\RecruitmentJob::count() . PHP_EOL;
foreach (App\Models\RecruitmentJob::select('id','title','status','branch_id')->limit(10)->get() as $job) {
    $status = $job->status;
    if ($status instanceof BackedEnum) {
        $status = $status->value;
    }
    echo $job->id . '|' . $job->title . '|' . $status . '|branch=' . ($job->branch_id ?? 'null') . PHP_EOL;
}
