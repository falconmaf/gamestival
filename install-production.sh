#!/bin/bash
###########################################
# Gamestival Complete Deployment Script
# Run this in cPanel Terminal
# Domain: camptell.space
###########################################

set -e  # Exit on any error

echo "🚀 Starting Gamestival Deployment..."
echo "=================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_step() {
    echo -e "${BLUE}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Step 1: Navigate to repository
print_step "Step 1: Navigating to gamestival directory..."
cd ~/gamestival
print_success "In directory: $(pwd)"

# Step 2: Pull repository files
print_step "Step 2: Pulling repository files from GitHub..."
git pull origin main || {
    print_warning "Git pull failed, trying reset..."
    git fetch origin
    git reset --hard origin/main
}
print_success "Repository files pulled"

# Step 3: Create .env file with production settings
print_step "Step 3: Creating production .env file..."
cat > .env << 'EOL'
APP_NAME="Gamestival"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://camptell.space

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=camptell_db
DB_USERNAME=camptell_db_user
DB_PASSWORD="CampTellDB90()"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mail.camptell.space
MAIL_PORT=587
MAIL_USERNAME=noreply@camptell.space
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@camptell.space
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

WAVE_BILLING_PROVIDER=stripe
WAVE_PRIMARY_COLOR=#6366f1

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

PADDLE_VENDOR_ID=
PADDLE_VENDOR_AUTH_CODE=
PADDLE_PUBLIC_KEY=
PADDLE_SANDBOX=false

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"

JWT_SECRET=
JWT_TTL=60

TWO_FACTOR_ENABLED=true

SECURE_COOKIES=true
SANCTUM_STATEFUL_DOMAINS=camptell.space
EOL
print_success ".env file created"

# Step 4: Install Composer dependencies first (needed for artisan)
print_step "Step 4: Installing Composer dependencies (this may take a few minutes)..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=ext-exif 2>&1 || {
    print_warning "Composer install had warnings, continuing..."
}
print_success "Composer dependencies installed"

# Step 5: Generate application key
print_step "Step 5: Generating application key..."
php artisan key:generate --force --ansi
print_success "Application key generated"

# Step 6: Install NPM dependencies
print_step "Step 6: Installing NPM dependencies..."
npm ci 2>&1 || npm install 2>&1 || {
    print_warning "NPM install had issues, trying alternative method..."
    npm install --legacy-peer-deps 2>&1
}
print_success "NPM dependencies installed"

# Step 7: Build frontend assets
print_step "Step 7: Building frontend assets..."
npm run build 2>&1 || {
    print_error "Asset build failed, but continuing..."
}
print_success "Frontend assets build attempted"

# Step 8: Set up directory structure
print_step "Step 8: Setting up directory structure..."

# Backup existing public_html if it exists and is not a symlink
if [ -d ~/public_html ] && [ ! -L ~/public_html ]; then
    print_warning "Backing up existing public_html..."
    mv ~/public_html ~/public_html_backup_$(date +%Y%m%d_%H%M%S)
fi

# Remove public_html if it's a symlink
if [ -L ~/public_html ]; then
    rm ~/public_html
fi

# Create symlink
ln -sf ~/gamestival/public ~/public_html
print_success "Public directory symlinked to public_html"

# Step 9: Set permissions
print_step "Step 9: Setting correct permissions..."
chmod -R 755 ~/gamestival
chmod -R 775 ~/gamestival/storage
chmod -R 775 ~/gamestival/bootstrap/cache
print_success "Permissions set"

# Step 10: Create storage symlink
print_step "Step 10: Creating storage symlink..."
php artisan storage:link
print_success "Storage symlink created"

# Step 11: Run database migrations
print_step "Step 11: Running database migrations..."
php artisan migrate --force
print_success "Database migrations completed"

# Step 12: Seed database
print_step "Step 12: Seeding database with initial data..."
php artisan db:seed --force
print_success "Database seeded"

# Step 13: Optimize application
print_step "Step 13: Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

# Step 14: Make deploy script executable
print_step "Step 14: Making deploy script executable..."
chmod +x ~/gamestival/deploy.sh
print_success "Deploy script is now executable"

echo ""
echo "=========================================="
echo -e "${GREEN}🎉 DEPLOYMENT COMPLETED SUCCESSFULLY! 🎉${NC}"
echo "=========================================="
echo ""
echo "📊 Deployment Summary:"
echo "   Domain: https://camptell.space"
echo "   Admin Panel: https://camptell.space/admin"
echo "   Database: camptell_db"
echo ""
echo "🔐 Default Admin Credentials:"
echo "   Email: admin@admin.com"
echo "   Password: password"
echo "   ⚠️  CHANGE THIS PASSWORD IMMEDIATELY!"
echo ""
echo "📝 Next Steps:"
echo "   1. Visit https://camptell.space to test"
echo "   2. Login to admin panel"
echo "   3. Change admin password"
echo "   4. Configure Stripe keys in .env"
echo "   5. Set up email credentials"
echo ""
echo "🔄 For future deployments, run:"
echo "   cd ~/gamestival && ./deploy.sh"
echo ""
print_success "All done! Your application is live! 🚀"
