<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
Illuminate\Support\Facades\Auth::login($user);

try {
    $result = Livewire\Livewire::test(App\Livewire\Client\Employers\PostJob::class)
        ->set('ai_brief', 'test brief')
        ->set('title', 'Dev')
        ->call('reviewAiDraft');
    echo 'Score: ' . json_encode($result->get('ai_quality_score')) . PHP_EOL;
    echo 'Errors: ' . json_encode($result->errors()) . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
