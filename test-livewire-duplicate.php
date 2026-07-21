<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
Illuminate\Support\Facades\Auth::login($user);

$html = Livewire\Livewire::test(App\Livewire\Client\Employers\PostJob::class)->html();

preg_match_all('/livewire(\.min)?\.js/', $html, $matches);
preg_match_all('/@livewireScripts|wire:snapshot|data-livewire/', $html, $snapshots);

echo 'livewire.js count: ' . count($matches[0]) . PHP_EOL;
echo 'wire:snapshot count: ' . substr_count($html, 'wire:snapshot') . PHP_EOL;
echo 'Alpine count: ' . substr_count($html, 'alpine') . PHP_EOL;

// Full page render through layout
$response = Livewire\Livewire::actingAs($user)
    ->test(App\Livewire\Client\Employers\PostJob::class);

$pageHtml = $response->html();
// The test might not include full layout - let's use HTTP
$httpResponse = Illuminate\Support\Facades\Route::dispatch(
    Illuminate\Http\Request::create('/employers/post-job', 'GET')
);
// That won't work with auth easily

// Count in component html only
echo 'Component html length: ' . strlen($html) . PHP_EOL;
