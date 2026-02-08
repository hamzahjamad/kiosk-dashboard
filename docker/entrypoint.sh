#!/bin/sh
set -e

echo "Starting Kiosk Dashboard..."

# Wait for database to be ready
echo "Waiting for database connection..."
while ! php artisan db:monitor --databases=mysql > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready!"

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
