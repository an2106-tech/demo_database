<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();

$response = $this ?? null;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/employers/post-job', 'GET');
$request->setLaravelSession($app->make('session')->driver());
Illuminate\Support\Facades\Auth::login($user);

$response = $kernel->handle($request);
$html = $response->getContent();

echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
echo 'livewire.js references: ' . substr_count(strtolower($html), 'livewire') . PHP_EOL;
echo 'script src livewire count: ' . preg_match_all('/src="[^"]*livewire[^"]*"/i', $html, $m) . PHP_EOL;
if (!empty($m[0])) {
    foreach ($m[0] as $src) echo $src . PHP_EOL;
}
echo 'wire:click generateAiDraft present: ' . (str_contains($html, 'wire:click="generateAiDraft"') ? 'yes' : 'no') . PHP_EOL;
echo 'wire:snapshot present: ' . (str_contains($html, 'wire:snapshot') ? 'yes' : 'no') . PHP_EOL;

$kernel->terminate($request, $response);
