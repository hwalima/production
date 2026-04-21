#!/bin/bash
# ============================================================
#  MyMine — Cron Deploy Runner
#  Runs every minute via cron. Checks for a deploy-pending
#  flag written by deploy.php (web PHP can't run shell cmds).
#
#  Cron entry (set in DirectAdmin → Cron Jobs):
#  * * * * * bash /home/hwalimad/domains/mymine.hwalima.digital/laravel/deploy-cron.sh >> /home/hwalimad/domains/mymine.hwalima.digital/laravel/storage/logs/deploy.log 2>&1
# ============================================================

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
FLAG="$APP_DIR/storage/deploy-pending"
LOG="$APP_DIR/storage/logs/deploy.log"

# Nothing to do
[ -f "$FLAG" ] || exit 0

echo ""
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy triggered by webhook"
echo "Meta: $(cat "$FLAG")"

# Remove flag immediately so concurrent runs don't double-deploy
rm -f "$FLAG"

# Detect PHP binary
for PHP_BIN in \
    /opt/alt/php82/root/usr/bin/php \
    /usr/local/bin/php82 \
    /usr/local/bin/php8.2 \
    /usr/local/bin/php \
    /usr/bin/php; do
    [ -x "$PHP_BIN" ] && break
done

# Detect Composer
HOME_DIR="$(eval echo ~)"
for COMPOSER_BIN in \
    /usr/local/bin/composer \
    /usr/bin/composer \
    "$HOME_DIR/bin/composer"; do
    [ -x "$COMPOSER_BIN" ] && break
done

echo "==> PHP:      $PHP_BIN"
echo "==> Composer: $COMPOSER_BIN"
echo "==> App dir:  $APP_DIR"

cd "$APP_DIR" || exit 1

echo "==> git pull"
git fetch origin main
git reset --hard origin/main

echo "==> composer install"
HOME=/tmp COMPOSER_HOME=/tmp/composer-home \
    "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --working-dir="$APP_DIR"

echo "==> artisan"
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan view:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan db:seed --class=KnowledgeBaseSeeder --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "==> permissions"
chmod -R 755 storage bootstrap/cache

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy complete."
