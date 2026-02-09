#!/bin/sh
set -e

echo "Starting Kiosk Dashboard..."

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Run seeders only when RUN_SEED is set (e.g. first deploy or local)
if [ "$RUN_SEED" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force
else
    echo "Skipping seeders (set RUN_SEED=true to run on startup, or run: docker exec <container> php artisan db:seed --force)"
fi

# Clear and cache config for production
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if not exists
php artisan storage:link 2>/dev/null || true

echo "Application ready!"

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
