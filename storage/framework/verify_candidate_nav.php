<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'atus.pm@demo.local')->first();
Illuminate\Support\Facades\Auth::login($user);
echo 'can_candidate=' . (App\Filament\Resources\Candidates\CandidateResource::canViewAny() ? 'yes' : 'no') . PHP_EOL;
