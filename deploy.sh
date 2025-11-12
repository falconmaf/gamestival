#!/bin/bash

##############################################
# Laravel Deployment Script for cPanel VPS
# Author: Generated for Gamestival
# Usage: ./deploy.sh
##############################################

set -e  # Exit on error

echo "🚀 Starting deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR=$(cd "$(dirname "$0")" && pwd)
PUBLIC_DIR="$HOME/public_html"
STORAGE_DIR="$APP_DIR/storage"
BACKUP_DIR="$HOME/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo "📁 Application Directory: $APP_DIR"
echo "📁 Public Directory: $PUBLIC_DIR"

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Enable maintenance mode
print_info "Enabling maintenance mode..."
php artisan down || print_error "Failed to enable maintenance mode"

# Backup database
print_info "Backing up database..."
mkdir -p "$BACKUP_DIR"
php artisan db:backup --path="$BACKUP_DIR/db_backup_$TIMESTAMP.sql" 2>/dev/null || {
    mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_DIR/db_backup_$TIMESTAMP.sql" 2>/dev/null || print_error "Database backup failed"
}
print_success "Database backed up to $BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# Pull latest code
print_info "Pulling latest code from GitHub..."
git pull origin main || {
    print_error "Git pull failed"
    php artisan up
    exit 1
}
print_success "Code updated"

# Install/Update Composer dependencies
print_info "Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader || {
    print_error "Composer install failed"
    php artisan up
    exit 1
}
print_success "Composer dependencies installed"

# Install/Update NPM dependencies
print_info "Installing NPM dependencies..."
npm ci --production || npm install --production || {
    print_error "NPM install failed"
    php artisan up
    exit 1
}
print_success "NPM dependencies installed"

# Build frontend assets
print_info "Building frontend assets..."
npm run build || {
    print_error "Asset build failed"
    php artisan up
    exit 1
}
print_success "Assets built"

# Run database migrations
print_info "Running database migrations..."
php artisan migrate --force || {
    print_error "Migration failed"
    php artisan up
    exit 1
}
print_success "Migrations completed"

# Clear all caches
print_info "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# Optimize for production
print_info "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

# Fix permissions
print_info "Setting correct permissions..."
chmod -R 755 "$APP_DIR"
chmod -R 775 "$STORAGE_DIR"
chmod -R 775 "$APP_DIR/bootstrap/cache"
print_success "Permissions set"

# Create storage link if not exists
if [ ! -L "$PUBLIC_DIR/storage" ]; then
    print_info "Creating storage symlink..."
    php artisan storage:link
    print_success "Storage link created"
fi

# Restart PHP-FPM (if you have sudo access)
print_info "Attempting to restart PHP-FPM..."
sudo systemctl reload php-fpm 2>/dev/null || sudo systemctl reload php8.4-fpm 2>/dev/null || print_info "Could not restart PHP-FPM (not critical)"

# Disable maintenance mode
print_info "Disabling maintenance mode..."
php artisan up
print_success "Maintenance mode disabled"

echo ""
print_success "🎉 Deployment completed successfully!"
echo ""
echo "📊 Deployment Summary:"
echo "   Time: $TIMESTAMP"
echo "   Backup: $BACKUP_DIR/db_backup_$TIMESTAMP.sql"
echo ""
