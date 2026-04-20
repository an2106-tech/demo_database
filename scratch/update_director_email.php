<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('role', 'director')->first();
if ($user) {
    $oldEmail = $user->email;
    $user->email = 'trongan456nc@gmail.com';
    $user->save();
    echo "Updated Director: {$user->name} ({$oldEmail} -> {$user->email})\n";
} else {
    echo "No director found.\n";
}
