#!/bin/bash
# ============================================================
#  My Mine — Production Deploy Script
#  Run this on the server after cloning / to update
# ============================================================
set -e

APP_DIR="${APP_DIR:-/home/trukumb2/public_html/mymine}"
cd "$APP_DIR"

echo "==> Pulling latest code..."
git pull origin main

echo "==> Installing/updating Composer dependencies (no dev)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Linking storage..."
php artisan storage:link || true

echo "==> Setting directory permissions..."
chmod -R 755 storage bootstrap/cache
chown -R "$(whoami)":nobody storage bootstrap/cache 2>/dev/null || true

echo "==> Clearing old caches..."
php artisan cache:clear
php artisan view:clear

echo ""
echo "Deploy complete!"
