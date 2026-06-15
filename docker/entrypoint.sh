#!/usr/bin/env sh
set -e
cd /var/www/html

if [ ! -f .env ] && [ -f .env.production.example ]; then
  cp .env.production.example .env
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/database bootstrap/cache public/media/news/images public/media/ads
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "${DB_DATABASE:-}" ]; then
  mkdir -p "$(dirname "$DB_DATABASE")"
  touch "$DB_DATABASE"
fi
chown -R www-data:www-data storage bootstrap/cache public/media || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force
fi

php artisan storage:link || true
php artisan optimize:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"
