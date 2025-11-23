#!/bin/bash

# Fix Production Server Script
# Run this as ROOT on the production server

set -e

echo "🔍 Step 1: Checking PHP-FPM status..."
/scripts/php_fpm_config --check

echo ""
echo "🔄 Step 2: Rebuilding PHP-FPM config for camptell.space..."
/scripts/php_fpm_config --rebuild --domain=camptell.space

echo ""
echo "🔧 Step 3: Fixing socket permissions..."
chmod 777 /opt/cpanel/ea-php83/root/usr/var/run/php-fpm/ 2>/dev/null || true
find /opt/cpanel/ea-php83/root/usr/var/run/php-fpm/ -name "*.sock" -exec chmod 666 {} \; 2>/dev/null || true

echo ""
echo "🔄 Step 4: Restarting PHP-FPM..."
/scripts/restartsrv_apache_php_fpm

echo ""
echo "🔄 Step 5: Restarting Apache..."
systemctl reload httpd

echo ""
echo "📝 Step 6: Checking error log..."
tail -20 /usr/local/apache/logs/error_log

echo ""
echo "✅ Done! Now check camptell.space in your browser."
