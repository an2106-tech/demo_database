<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
Illuminate\Support\Facades\Auth::login($user);

$html = Livewire\Livewire::test(App\Livewire\Client\Employers\PostJob::class)->html();

preg_match('/wire:id="([^"]+)"/', $html, $wireId);
preg_match_all('/wire:click="([^"]+)"/', $html, $clicks);

echo 'wire:id=' . ($wireId[1] ?? 'NONE') . PHP_EOL;
echo 'wire:clicks=' . implode(', ', $clicks[1] ?? []) . PHP_EOL;
echo 'has livewire.js reference: ' . (str_contains($html, 'livewire') ? 'yes' : 'no') . PHP_EOL;
echo 'script tags in component: ' . substr_count($html, '<script') . PHP_EOL;
echo 'has alpine x-data: ' . (str_contains($html, 'x-data') ? 'yes' : 'no') . PHP_EOL;

// Check if AI buttons are inside a form
if (preg_match('/AI hỗ trợ viết tin.*?(<form|<\/form)/s', $html, $m)) {
    echo 'AI section before form tag: ' . (str_contains(substr($html, 0, strpos($html, 'AI hỗ trợ viết tin')), '<form') ? 'inside form' : 'check manually') . PHP_EOL;
}

$aiPos = strpos($html, 'generateAiDraft');
$formPos = strpos($html, 'wire:submit="save"');
echo 'generateAiDraft pos: ' . $aiPos . ', save form pos: ' . $formPos . PHP_EOL;
echo ($aiPos < $formPos ? 'AI buttons BEFORE save form (good)' : 'AI buttons AFTER or IN form (bad)') . PHP_EOL;
