<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

try {
    echo "Attempting to send test email to trongan456nc@gmail.com...\n";
    Mail::raw('Test email from FPT Careers - Debug Script', function ($message) {
        $message->to('trongan456nc@gmail.com')->subject('Test SMTP Debug');
    });
    echo "Success! Email sent (conceptually). Check your inbox.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    Log::error("Mail Debug Error: " . $e->getMessage());
}
