# Gamestival - cPanel VPS Deployment Guide

Complete guide for deploying the Gamestival Laravel application to a cPanel VPS with automated GitHub deployments.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Initial Setup](#server-initial-setup)
3. [Database Setup](#database-setup)
4. [Email Configuration](#email-configuration)
5. [GitHub Deployment Setup](#github-deployment-setup)
6. [First Deployment](#first-deployment)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software
- cPanel VPS with root access
- Git installed on server
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/MariaDB
- SSH access enabled

### Domain Configuration
- Domain pointed to your VPS IP
- SSL certificate (Let's Encrypt via cPanel)

---

## Server Initial Setup

### 1. Connect to Your Server via SSH

```bash
ssh username@your-server-ip
# OR if using a specific port
ssh -p 2222 username@your-server-ip
```

### 2. Update System Packages

```bash
sudo yum update -y  # For CentOS/AlmaLinux
# OR
sudo apt update && sudo apt upgrade -y  # For Ubuntu/Debian
```

### 3. Install Required Software

```bash
# Install Git (if not installed)
sudo yum install git -y
# OR
sudo apt install git -y

# Verify installations
git --version
php --version  # Should be 8.2+
composer --version
node --version
npm --version
mysql --version
```

### 4. Set Up Directory Structure

```bash
# Navigate to home directory
cd ~

# Create application directory
mkdir -p ~/gamestival
cd ~/gamestival

# Clone repository
git clone https://github.com/falconmaf/gamestival.git .

# Set up public_html symlink
rm -rf ~/public_html  # Remove existing public_html
ln -s ~/gamestival/public ~/public_html

# Verify symlink
ls -la ~/public_html
```

### 5. Set Correct Permissions

```bash
cd ~/gamestival

# Set directory permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Set write permissions for Laravel
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If using www-data user (check with: ps aux | grep php-fpm)
sudo chown -R username:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

---

## Database Setup

### 1. Create Database via cPanel

**Option A: Using cPanel Interface**
1. Log into cPanel
2. Navigate to **MySQL® Databases**
3. Create new database: `username_gamestival`
4. Create new user: `username_gameuser`
5. Set a strong password
6. Add user to database with ALL PRIVILEGES

**Option B: Using Command Line**

```bash
# Log into MySQL
mysql -u root -p

# Create database
CREATE DATABASE cpanel_gamestival CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'cpanel_gameuser'@'localhost' IDENTIFIED BY 'your_secure_password';

# Grant privileges
GRANT ALL PRIVILEGES ON cpanel_gamestival.* TO 'cpanel_gameuser'@'localhost';

# Flush privileges
FLUSH PRIVILEGES;
EXIT;
```

### 2. Test Database Connection

```bash
mysql -u cpanel_gameuser -p cpanel_gamestival
# Enter password when prompted
# If successful, you'll see: mysql>
EXIT;
```

### 3. Import Initial Database (if needed)

```bash
# If you have a database dump
mysql -u cpanel_gameuser -p cpanel_gamestival < database.sql
```

---

## Email Configuration

### 1. Create Email Accounts in cPanel

1. Log into cPanel
2. Navigate to **Email Accounts**
3. Create the following accounts:
   - `noreply@yourdomain.com` (for automated emails)
   - `admin@yourdomain.com` (for admin notifications)
   - `support@yourdomain.com` (for customer support)

### 2. Configure Email Settings in .env

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Gamestival"
```

### 3. Test Email Configuration

```bash
cd ~/gamestival
php artisan tinker

# In tinker, run:
Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test from Gamestival');
});
# Press Ctrl+C to exit tinker
```

### 4. SPF and DKIM Records (Important for Deliverability)

**Add to your DNS records:**

**SPF Record:**
```
Type: TXT
Name: @
Value: v=spf1 include:yourdomain.com ~all
```

**DKIM Record:**
1. In cPanel, go to **Email Deliverability**
2. Click **Manage** next to your domain
3. Copy the DKIM record and add to your DNS

---

## GitHub Deployment Setup

### 1. Generate SSH Key on Server

```bash
# Generate SSH key (if not exists)
ssh-keygen -t ed25519 -C "deploy@yourdomain.com"
# Press Enter for all prompts (use default location)

# Display public key
cat ~/.ssh/id_ed25519.pub
# Copy this key
```

### 2. Add Deploy Key to GitHub

1. Go to GitHub repository: https://github.com/falconmaf/gamestival
2. Navigate to **Settings** → **Deploy keys**
3. Click **Add deploy key**
4. Paste the public key
5. Check **Allow write access** (if using GitHub Actions to push)
6. Click **Add key**

### 3. Configure GitHub Secrets

Go to GitHub repository → **Settings** → **Secrets and variables** → **Actions**

Add the following secrets:

| Secret Name | Value | Example |
|-------------|-------|---------|
| `SSH_HOST` | Your server IP or domain | `123.456.789.0` or `yourdomain.com` |
| `SSH_USERNAME` | Your cPanel username | `cpanelusername` |
| `SSH_PORT` | SSH port (usually 22) | `22` or `2222` |
| `SSH_PRIVATE_KEY` | Private SSH key from server | Contents of `~/.ssh/id_ed25519` |
| `DEPLOY_PATH` | Path to application | `/home/username/gamestival` |

**To get private key:**
```bash
cat ~/.ssh/id_ed25519
# Copy the ENTIRE output including BEGIN and END lines
```

### 4. Configure Allowed Hosts for SSH

```bash
# On your server, edit SSH config
nano ~/.ssh/config

# Add:
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519
    StrictHostKeyChecking no

# Save and exit (Ctrl+X, Y, Enter)

# Set correct permissions
chmod 600 ~/.ssh/config

# Test GitHub connection
ssh -T git@github.com
# You should see: "Hi falconmaf! You've successfully authenticated..."
```

---

## First Deployment

### 1. Set Up Environment File

```bash
cd ~/gamestival

# Copy production environment template
cp .env.production.example .env

# Edit environment file
nano .env
```

**Update these critical values:**
```env
APP_NAME="Gamestival"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=cpanel_gamestival
DB_USERNAME=cpanel_gameuser
DB_PASSWORD=your_database_password

MAIL_HOST=mail.yourdomain.com
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password

STRIPE_KEY=pk_live_your_key
STRIPE_SECRET=sk_live_your_secret
```

### 2. Generate Application Key

```bash
php artisan key:generate
```

### 3. Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies
npm ci

# Build frontend assets
npm run build
```

### 4. Run Database Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed database with initial data
php artisan db:seed --force
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Make Deploy Script Executable

```bash
chmod +x ~/gamestival/deploy.sh
```

### 8. Test the Application

Open your browser and visit:
- `https://yourdomain.com` - Homepage
- `https://yourdomain.com/admin` - Admin panel

**Default Admin Login:**
- Email: `admin@admin.com`
- Password: `password`

**⚠️ IMPORTANT: Change this password immediately!**

---

## SSL Certificate Setup

### 1. Install SSL via cPanel

1. Log into cPanel
2. Navigate to **SSL/TLS Status**
3. Select your domain
4. Click **Run AutoSSL**

### 2. Force HTTPS (Recommended)

Add to `.htaccess` in `public_html` (or `public` directory):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Existing Laravel rules below...
</IfModule>
```

---

## Automated Deployments

Once GitHub Actions is configured, deployments happen automatically:

### Automatic Deployment
1. Push code to `main` branch
2. GitHub Actions runs tests and builds
3. Code is automatically deployed to server
4. Application is optimized
5. You receive a notification

### Manual Deployment on Server

```bash
cd ~/gamestival
./deploy.sh
```

### Rollback (if needed)

```bash
cd ~/gamestival

# See git history
git log --oneline -10

# Rollback to previous commit
git reset --hard COMMIT_HASH

# Run deployment
./deploy.sh
```

---

## Monitoring & Maintenance

### 1. Set Up Cron Jobs (in cPanel)

1. Navigate to **Cron Jobs** in cPanel
2. Add the following:

**Laravel Scheduler (every minute):**
```bash
* * * * * cd /home/username/gamestival && php artisan schedule:run >> /dev/null 2>&1
```

**Queue Worker (every 5 minutes):**
```bash
*/5 * * * * cd /home/username/gamestival && php artisan queue:work --stop-when-empty --max-time=3600
```

**Database Backup (daily at 2 AM):**
```bash
0 2 * * * mysqldump -u cpanel_gameuser -p'password' cpanel_gamestival > /home/username/backups/db_$(date +\%Y\%m\%d).sql
```

### 2. Monitor Logs

```bash
# Application logs
tail -f ~/gamestival/storage/logs/laravel.log

# PHP-FPM logs
tail -f /var/log/php-fpm/error.log

# Apache logs
tail -f ~/logs/error_log
```

### 3. Clear Caches (if issues occur)

```bash
cd ~/gamestival
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Troubleshooting

### Issue: 500 Internal Server Error

**Check:**
```bash
# View Laravel logs
tail -100 ~/gamestival/storage/logs/laravel.log

# Check permissions
ls -la ~/gamestival/storage
ls -la ~/gamestival/bootstrap/cache

# Fix permissions
chmod -R 775 ~/gamestival/storage
chmod -R 775 ~/gamestival/bootstrap/cache
```

### Issue: Database Connection Errors

```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
# Press Ctrl+C to exit

# Verify .env credentials match cPanel database
cat .env | grep DB_
```

### Issue: White/Blank Page

```bash
# Enable debug mode temporarily
nano .env
# Change: APP_DEBUG=true

# Visit site and check error
# After fixing, set back to: APP_DEBUG=false
```

### Issue: Assets Not Loading

```bash
# Rebuild assets
npm run build

# Check public directory symlink
ls -la ~/public_html

# Recreate if needed
rm ~/public_html
ln -s ~/gamestival/public ~/public_html
```

### Issue: Email Not Sending

```bash
# Test email configuration
php artisan tinker

# Test SMTP connection
Mail::raw('Test', function($m) {
    $m->to('test@example.com')->subject('Test');
});

# Check mail logs
tail -50 /var/log/maillog
```

### Issue: GitHub Actions Failing

**Check:**
1. GitHub Secrets are set correctly
2. SSH key is added to server
3. Deploy path is correct
4. Server has Git, Composer, Node installed
5. Check Actions tab for specific error

---

## Security Best Practices

### 1. Change Default Admin Password
```bash
php artisan tinker
$admin = User::where('email', 'admin@admin.com')->first();
$admin->password = bcrypt('new_secure_password');
$admin->save();
```

### 2. Disable Debug Mode in Production
```env
APP_DEBUG=false
```

### 3. Set Proper File Permissions
```bash
find ~/gamestival -type f -exec chmod 644 {} \;
find ~/gamestival -type d -exec chmod 755 {} \;
chmod -R 775 ~/gamestival/storage
chmod -R 775 ~/gamestival/bootstrap/cache
```

### 4. Enable HTTPS Only
```env
SECURE_COOKIES=true
```

### 5. Regular Backups
- Database: Daily automated backups
- Files: Weekly backups via cPanel

---

## Performance Optimization

### 1. Enable OPcache (via cPanel)
1. **Select PHP Version** → **Options**
2. Enable `opcache`

### 2. Use Database Query Caching
Already configured in `.env`:
```env
CACHE_STORE=database
```

### 3. Queue Long-Running Tasks
```bash
# Start queue worker (add to supervisor or systemd)
php artisan queue:work --daemon
```

---

## Support & Resources

### Documentation Links
- Laravel: https://laravel.com/docs
- Wave: https://wave.devdojo.com/docs
- Filament: https://filamentphp.com/docs

### Common Commands Reference

```bash
# Application
php artisan down                    # Enable maintenance mode
php artisan up                      # Disable maintenance mode
php artisan migrate                 # Run migrations
php artisan db:seed                 # Seed database
php artisan cache:clear             # Clear cache
php artisan config:cache            # Cache config

# Deployment
./deploy.sh                         # Run deployment script
git pull origin main                # Pull latest code
composer install --no-dev           # Install dependencies
npm run build                       # Build assets

# Monitoring
tail -f storage/logs/laravel.log    # View application logs
php artisan queue:work              # Process queued jobs
php artisan schedule:run            # Run scheduled tasks
```

---

## Next Steps

After successful deployment:

1. ✅ Change default admin password
2. ✅ Configure Stripe/Paddle for payments
3. ✅ Set up email templates
4. ✅ Configure social authentication (optional)
5. ✅ Set up monitoring (UptimeRobot, etc.)
6. ✅ Enable backups
7. ✅ Test all functionality
8. ✅ Create documentation for your team

---

**Deployment Date:** [DATE]  
**Deployed By:** [YOUR NAME]  
**Version:** 1.0.0

For questions or issues, check the troubleshooting section or contact support.
