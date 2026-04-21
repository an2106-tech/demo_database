<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$apps = App\Models\Application::query()->with(['candidate', 'latestOffer'])->latest('id')->take(10)->get();
foreach ($apps as $a) {
    $status = $a->status instanceof BackedEnum ? $a->status->value : (string) $a->status;
    echo 'APP#'.$a->id.' app_status='.$status.' candidate_email='.($a->candidate?->email ?? 'NULL').' offer_status='.($a->latestOffer?->status ?? 'NULL').' sent_at='.($a->latestOffer?->sent_at ?? 'NULL').PHP_EOL;
}
