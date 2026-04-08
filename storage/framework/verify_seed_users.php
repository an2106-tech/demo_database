<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (['admin@demo.local', 'hr@demo.local', 'director@demo.local', 'pm@demo.local'] as $email) {
    $user = App\Models\User::where('email', $email)->first();
    echo $email . ':' . ($user ? ($user->name . '|' . $user->role . '|' . implode(',', $user->roles->pluck('name')->all())) : 'missing') . PHP_EOL;
}
