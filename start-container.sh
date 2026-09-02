#!/usr/bin/env bash
set -Eeuo pipefail

cd /app

: "${RAILWAY_PROJECT_ID:?Only run this script on Railway}"
: "${APP_KEY:?APP_KEY is required}"

if [ "${DB_CONNECTION:-}" != "mysql" ]; then
    echo "DB_CONNECTION must be mysql"
    exit 1
fi

mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R ug+rwX storage bootstrap/cache

php artisan config:clear
php artisan route:clear
php artisan migrate --force

if [ ! -L public/storage ]; then
    php artisan storage:link
fi

php artisan config:cache
php artisan view:cache

pids=()

cleanup() {
    trap - EXIT
    for pid in "${pids[@]}"; do
        kill -TERM "$pid" 2>/dev/null || true
    done
    wait || true
}

trap cleanup EXIT
trap 'exit 0' TERM INT

php artisan queue:work database \
    --sleep=3 --tries=3 --timeout=120 --memory=128 &
pids+=("$!")

php artisan schedule:work &
pids+=("$!")

docker-php-entrypoint --config /Caddyfile --adapter caddyfile &
pids+=("$!")

# Restart the service if a required process stops.
wait -n "${pids[@]}" || true
exit 1