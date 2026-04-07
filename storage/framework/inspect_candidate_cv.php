<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$application = App\Models\Application::query()->find(1);
$candidate = App\Models\Candidate::query()->find($application?->candidate_id);
var_export([
    'application_id' => $application?->id,
    'application_cv_path' => $application?->cv_path,
    'candidate_id' => $candidate?->id,
    'candidate_cv_file' => $candidate?->cv_file,
]);
