<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->cleanupStaleViteHotFile();
    }

    private function cleanupStaleViteHotFile(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return;
        }

        $hotUrl = trim((string) @file_get_contents($hotFile));

        if ($hotUrl === '') {
            return;
        }

        $parts = parse_url($hotUrl);
        $host = $parts['host'] ?? null;
        $port = (int) ($parts['port'] ?? 5173);

        if (! $host) {
            return;
        }

        $appUrl = (string) config('app.url');
        $appParts = $appUrl !== '' ? parse_url($appUrl) : [];
        $appHost = $appParts['host'] ?? null;
        $appPort = (int) ($appParts['port'] ?? 80);

        // `public/hot` must point to the Vite dev server (typically 5173), not the Laravel app (typically 8000).
        // If it points back to APP_URL, treat it as invalid/stale.
        if ($appHost && $host === $appHost && $port === $appPort) {
            @unlink($hotFile);
            return;
        }

        $connection = @fsockopen($host, $port, $errorNumber, $errorString, 0.2);

        if (is_resource($connection)) {
            fclose($connection);
            return;
        }

        // Some Windows setups intermittently fail IPv6 loopback (`::1`) even though IPv4 works.
        // If `public/hot` uses IPv6, try IPv4 and rewrite the hot file when it is reachable.
        if ($host === '::1') {
            $ipv4Connection = @fsockopen('127.0.0.1', $port, $errorNumber, $errorString, 0.2);

            if (is_resource($ipv4Connection)) {
                fclose($ipv4Connection);
                @file_put_contents($hotFile, "http://127.0.0.1:{$port}");
                return;
            }
        }

        @unlink($hotFile);
    }
}
