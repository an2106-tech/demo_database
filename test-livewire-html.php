<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
Illuminate\Support\Facades\Auth::login($user);

$html = Livewire\Livewire::test(App\Livewire\Client\Employers\PostJob::class)->html();

echo str_contains($html, 'wire:click="generateAiDraft"') ? "HAS wire:click\n" : "NO wire:click\n";
echo preg_match('/wire:id="([^"]+)"/', $html, $m) ? "wire:id={$m[1]}\n" : "NO wire:id\n";

$result = Livewire\Livewire::test(App\Livewire\Client\Employers\PostJob::class)
    ->set('ai_brief', 'Can tuyen Laravel dev')
    ->call('generateAiDraft');

echo 'After generateAiDraft title: ' . $result->get('title') . "\n";
echo 'Errors: ' . json_encode($result->errors()) . "\n";
