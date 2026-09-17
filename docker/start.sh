#!/bin/sh
set -e

: "${PORT:=8080}"

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    php artisan db:seed --class='Database\Seeders\ProductionAdminSeeder' --force
fi

if [ -n "${PRODUCTION_USERS_JSON:-}" ] && [ -n "${PRODUCTION_USERS_PASSWORD:-}" ]; then
    php artisan db:seed --class='Database\Seeders\ProductionUsersSeeder' --force
fi

php artisan optimize:clear
php artisan storage:link || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
