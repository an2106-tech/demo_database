<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (['director@demo.local', 'pm@demo.local'] as $email) {
    $active = App\Models\User::where('email', $email)->first();
    $trashed = App\Models\User::withTrashed()->where('email', $email)->first();
    echo $email . '|active=' . ($active ? 'yes' : 'no') . '|trashed=' . ($trashed ? 'yes' : 'no') . PHP_EOL;
    if ($trashed) {
        echo 'id=' . $trashed->id . '|role=' . $trashed->role . '|deleted_at=' . ($trashed->deleted_at ?? 'null') . PHP_EOL;
    }
}
