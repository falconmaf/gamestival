#!/bin/bash

# Simple deployment script for production server
# Run this on the production server: ~/gamestival/deploy-production.sh

echo "🚀 Starting deployment..."

cd ~/gamestival

# Pull latest code
echo "📥 Pulling latest code from GitHub..."
git fetch origin
git reset --hard origin/main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Clear all caches
echo "🧹 Clearing caches..."
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild optimized files
echo "⚡ Building optimized files..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if any)
echo "🗄️  Running migrations..."
php artisan migrate --force

# Fix permissions
echo "🔐 Fixing permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment complete!"
echo "🌐 Check your site at: https://camptell.space"
