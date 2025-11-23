#!/bin/bash

# Update production .env to use MySQL
# Run as camptell user

cd ~/repositories/gamestival

echo "🔧 Updating .env to use MySQL..."

# Backup current .env
cp .env .env.backup

# Update database settings
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/#DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/' .env
sed -i 's/#DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/#DB_DATABASE=database/DB_DATABASE=camptell_db/' .env
sed -i 's/#DB_USERNAME=root/DB_USERNAME=camptell_db_user/' .env
sed -i 's/#DB_PASSWORD=/DB_PASSWORD=CampTellDB90()/' .env
sed -i 's/#DB_COLLATION=utf8mb4_unicode_ci/DB_COLLATION=utf8mb4_general_ci/' .env

echo "✓ .env updated"

echo ""
echo "🗄️  Testing database connection..."
php artisan migrate:status || echo "Migration status check failed - database might not exist"

echo ""
echo "🔄 Running migrations..."
php artisan migrate --force

echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "✅ Done! Check browser now."
