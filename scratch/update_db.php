<?php
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement("ALTER TABLE recruitment_jobs MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'closed', 'archived', 'expired') DEFAULT 'draft'");
echo "Database updated successfully.\n";
