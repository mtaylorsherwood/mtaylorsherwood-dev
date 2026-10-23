#!/bin/sh
set -e

cd /var/www/mtsdev

if [ ! -f vendor/autoload.php ]; then
    echo "==> Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -d node_modules/.bin ]; then
    echo "==> Installing npm dependencies..."
    npm install
fi

if [ ! -f .env ]; then
    echo "==> Creating .env from .env.docker.example..."
    cp .env.docker.example .env
fi

if ! grep -q '^APP_KEY=base64' .env; then
    echo "==> Generating application key..."
    php artisan key:generate --ansi
fi

echo "==> Running migrations..."
php artisan migrate --ansi

# Seed once, based on the database's own state rather than a marker file
# — a marker on the bind-mounted project dir would survive a
# `docker compose down -v`, leaving a freshly-migrated-but-unseeded
# database on the next boot.
USER_COUNT=$(php artisan tinker --execute='echo \App\Models\User::query()->count();' 2>/dev/null)
if [ "${USER_COUNT:-0}" = "0" ]; then
    echo "==> Seeding database..."
    php artisan db:seed --ansi
fi

echo "==> Starting server, queue listener and Vite..."
exec npx concurrently -c "#93c5fd,#c4b5fd,#fdba74" \
    "php artisan serve --host=0.0.0.0 --port=8000" \
    "php artisan queue:listen --tries=1 --timeout=0" \
    "npx vite --host 0.0.0.0 --port ${VITE_PORT:-5176}" \
    --names="server,queue,vite"
