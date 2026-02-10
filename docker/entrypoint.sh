#!/bin/sh
set -e

echo "Starting Kiosk Dashboard..."

# Ensure storage/app exists and is writable by www-data (volume may be root-owned)
mkdir -p /var/www/html/storage/app
if [ -n "$DB_DATABASE" ] && [ "$DB_CONNECTION" = "sqlite" ]; then
  touch "$DB_DATABASE" 2>/dev/null || true
fi
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

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

# Ensure www-data can write to storage and bootstrap/cache (migrate/cache run as root)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "Application ready!"

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
