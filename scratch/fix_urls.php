<?php
$files = [
    'resources/views/livewire/client/job/job-detail.blade.php',
    'resources/views/livewire/client/job-list-sidebar.blade.php',
    'resources/views/livewire/client/home.blade.php',
    'resources/views/livewire/client/browse-jobs.blade.php',
    'resources/views/livewire/client/browse-companies.blade.php'
];

foreach ($files as $file) {
    if (file_exists(base_path($file))) {
        $path = base_path($file);
        $content = file_get_contents($path);
        
        // Find: route('candidates.job_detail', ['id' => $job->id])
        // Replace: route('jobs.public', ['slug' => $job->slug])
        $content = preg_replace(
            '/route\(\'candidates\.job_detail\',\s*\[\'id\'\s*=>\s*\$([a-zA-Z0-9_]+)->id\]\)/',
            "route('jobs.public', ['slug' => $\\1->slug])",
            $content
        );

        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
