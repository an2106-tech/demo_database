<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo Illuminate\Support\Facades\Storage::disk('public')->url('cvs/01KNJVS0HQR70PD6H3YVXWAY8M.pdf') . PHP_EOL;
echo public_path('storage/cvs/01KNJVS0HQR70PD6H3YVXWAY8M.pdf') . PHP_EOL;
