<?php
// Backfill public_url for all existing recruitment jobs based on their slug
// Run: php artisan tinker --execute="require base_path('scratch/backfill_public_url.php');"

$jobs = App\Models\RecruitmentJob::withTrashed()->whereNotNull('slug')->get();
$updated = 0;

foreach ($jobs as $job) {
    $job->public_url = route('jobs.public', ['slug' => $job->slug]);
    $job->saveQuietly();
    $updated++;
}

echo "Done: {$updated} job(s) updated with public_url.\n";
