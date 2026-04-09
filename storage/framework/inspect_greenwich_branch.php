<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$branches = App\Models\Branch::where('name', 'like', '%Greenwich Việt Nam - Hồ Chí Minh%')->get(['id','name','image','city']);
echo json_encode($branches, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
