#!/bin/bash

# Debug white screen script
# Run as camptell user

cd ~/repositories/gamestival

echo "=== Checking vendor directory ==="
if [ -d "vendor" ]; then
    echo "✓ vendor directory exists"
else
    echo "✗ vendor directory missing - installing..."
    composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-exif
fi

echo ""
echo "=== Checking .env file ==="
if [ -f ".env" ]; then
    echo "✓ .env file exists"
    echo "APP_KEY status:"
    grep "APP_KEY" .env
else
    echo "✗ .env file missing - copying from .env.example..."
    cp .env.example .env
    php artisan key:generate
fi

echo ""
echo "=== Creating storage directories ==="
mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo ""
echo "=== Checking Laravel logs ==="
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 30 lines of Laravel log:"
    tail -30 storage/logs/laravel.log
else
    echo "No Laravel log file yet"
fi

echo ""
echo "=== Checking Apache error log ==="
tail -20 ~/access-logs/camptell.space 2>/dev/null || echo "No access log found"
tail -20 ~/access-logs/camptell.space-ssl_log 2>/dev/null || echo "No SSL log found"

echo ""
echo "=== Testing Laravel directly ==="
php artisan --version

echo ""
echo "=== Clearing all caches ==="
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "✅ Done! Check browser again."
