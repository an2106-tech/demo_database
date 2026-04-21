<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('applications')->select('status', Illuminate\Support\Facades\DB::raw('COUNT(*) as c'))->groupBy('status')->get();
foreach ($rows as $row) {
    echo $row->status.' => '.$row->c.PHP_EOL;
}
