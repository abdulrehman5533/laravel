#!/usr/bin/env bash
set -e

composer install --no-dev --optimize-autoloader --ignore-platform-reqs

npm ci
npm run build

mkdir -p storage/framework/{sessions,views,cache,testing}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
