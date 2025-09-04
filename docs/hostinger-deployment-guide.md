# Deploying Laravel Project to Hostinger Shared Hosting with SQLite

This guide provides step-by-step instructions for deploying the Hidden Treasures Dashboard Laravel application to Hostinger shared hosting using SQLite as the database.

## Prerequisites

-   **Hostinger Shared Hosting Plan**: Premium or Business plan (required for SSH access)
-   **PHP Version**: 8.2 or higher (Laravel 12 requirement)
-   **Domain**: Configured in Hostinger hPanel
-   **SSH Access**: Enabled in Hostinger account
-   **Git Repository**: Project code pushed to a Git repository

## Project Analysis

### Technology Stack

-   **Backend**: Laravel 12.x with PHP 8.2+
-   **Frontend**: Vue.js 3.x with Inertia.js
-   **Database**: SQLite (file-based, no MySQL required)
-   **Build Tool**: Vite
-   **Styling**: Tailwind CSS

### Key Configuration Files

-   `composer.json`: PHP dependencies
-   `package.json`: Node.js dependencies
-   `vite.config.js`: Build configuration
-   `config/database.php`: Database configuration (already configured for SQLite)
-   `.env.example`: Environment template

## Deployment Steps

### Step 1: Prepare Your Project Locally

Before uploading to Hostinger, prepare your project for production:

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install

# Build production assets
npm run build

# Generate application key (if not already done)
php artisan key:generate

# Clear and optimize caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### Step 2: Access Hostinger via SSH

1. Log in to your Hostinger hPanel
2. Navigate to **Files** → **SSH Access**
3. Copy the SSH connection command
4. Open your terminal and connect:

```bash
ssh u123456789@your-domain.com
```

Replace `u123456789` with your actual user ID and `your-domain.com` with your domain.

### Step 3: Install Required Tools on Hostinger

#### Install Composer

```bash
# Download Composer installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Verify installer
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Install Composer
php composer-setup.php

# Move to home directory for easy access
mv composer.phar ~/

# Clean up
php -r "unlink('composer-setup.php');"
```

#### Install Node.js and NPM

```bash
# Install NVM (Node Version Manager)
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.11/install.sh | bash

# Add NVM to profile
echo 'export NVM_DIR="$HOME/.nvm"' >> ~/.profile
echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> ~/.profile
echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> ~/.profile

# Reload profile
source ~/.profile

# Install Node.js
nvm install node

# Verify installations
node -v
npm -v
```

### Step 4: Upload Project Files

#### Option A: Upload via File Manager (Recommended)

1. In Hostinger hPanel, go to **Files** → **File Manager**
2. Navigate to your domain's root directory
3. Delete all existing files in the root directory
4. Upload your entire Laravel project folder
5. Extract/unzip if uploaded as archive

#### Option B: Upload via Git

```bash
# Navigate to domains directory
cd domains/your-domain.com

# Clone your repository
git clone https://github.com/your-username/your-repo.git .

# If cloned into subdirectory, move files
# mv your-repo/* . && mv your-repo/.* . 2>/dev/null; rm -rf your-repo
```

### Step 5: Configure File Structure

1. **Rename public folder to public_html**:

    ```bash
    mv public public_html
    ```

2. **Create .htaccess file in public_html**:

    ```bash
    nano public_html/.htaccess
    ```

    Add the following content:

    ```apache
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteRule ^(.*)$ public/$1 [L]
    </IfModule>
    ```

    Save and exit (Ctrl+X, Y, Enter)

### Step 6: Configure Environment

1. **Copy environment file**:

    ```bash
    cp .env.example .env
    ```

2. **Edit .env file**:

    ```bash
    nano .env
    ```

    Update the following variables:

    ```env
    APP_NAME="Hidden Treasures Dashboard"
    APP_ENV=production
    APP_KEY=base64:your-generated-key-here
    APP_DEBUG=false
    APP_URL=https://your-domain.com

    # SQLite Database Configuration
    DB_CONNECTION=sqlite
    DB_DATABASE=/home/u123456789/domains/your-domain.com/database/database.sqlite

    # Session and Cache (using database for shared hosting)
    SESSION_DRIVER=database
    CACHE_STORE=database
    QUEUE_CONNECTION=database

    # Other settings
    LOG_CHANNEL=daily
    SANCTUM_STATEFUL_DOMAINS=your-domain.com
    SESSION_DOMAIN=.your-domain.com
    ```

    **Important**: Replace the following:

    - `your-generated-key-here`: Run `php artisan key:generate` to get a new key
    - `u123456789`: Your Hostinger user ID
    - `your-domain.com`: Your actual domain

### Step 7: Set Up Database

1. **Create SQLite database file**:

    ```bash
    mkdir -p database
    touch database/database.sqlite
    ```

2. **Set proper permissions**:
    ```bash
    chmod 755 database/
    chmod 644 database/database.sqlite
    ```

### Step 8: Install Dependencies and Run Migrations

1. **Install PHP dependencies**:

    ```bash
    php ~/composer.phar install --no-dev --optimize-autoloader
    ```

2. **Install Node.js dependencies**:

    ```bash
    npm install
    ```

3. **Build production assets**:

    ```bash
    npm run build
    ```

4. **Run database migrations**:

    ```bash
    php artisan migrate --force
    ```

5. **Seed database (optional)**:
    ```bash
    php artisan db:seed --force
    ```

### Step 9: Configure Storage and Permissions

1. **Update filesystems.php for storage links**:

    ```bash
    nano config/filesystems.php
    ```

    Find the `links` array and update it:

    ```php
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
    ```

    Change to:

    ```php
    'links' => [
        base_path('public_html/storage') => storage_path('app/public'),
    ],
    ```

2. **Create storage symlink**:

    ```bash
    php artisan storage:link
    ```

    If symlink fails, use manual approach:

    ```bash
    ln -s ../storage/app/public public_html/storage
    ```

3. **Set proper permissions**:
    ```bash
    chmod -R 755 storage/
    chmod -R 755 bootstrap/cache/
    chown -R u123456789:u123456789 .
    ```

### Step 10: Final Configuration

1. **Generate application key** (if not done):

    ```bash
    php artisan key:generate
    ```

2. **Optimize Laravel for production**:

    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
    ```

3. **Clear all caches**:
    ```bash
    php artisan optimize:clear
    ```

### Step 11: Set Up Cron Jobs (Optional)

For scheduled tasks, set up a cron job in Hostinger hPanel:

1. Go to **Advanced** → **Cron Jobs**
2. Create a new cron job:
    - **Command**: `/usr/bin/php /home/u123456789/domains/your-domain.com/artisan schedule:run`
    - **Schedule**: Every minute (`* * * * *`)

## Alternative: Deployment Without SSH

If you don't have SSH access (Single Web Hosting plan):

1. Upload files via File Manager
2. Use Hostinger's Cron Jobs to run artisan commands:

    - Command: `domains/your-domain.com/artisan migrate`
    - Schedule: Run once, then disable

3. For storage links, use manual symlink creation via Cron Jobs:
    - Command: `ln -s /home/u123456789/domains/your-domain.com/storage/app/public /home/u123456789/domains/your-domain.com/public_html/storage`

## Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

-   Check `.env` file configuration
-   Verify file permissions: `chmod -R 755 .`
-   Clear caches: `php artisan optimize:clear`

#### 2. Database Connection Error

-   Ensure `database/database.sqlite` exists
-   Check `.env` DB_DATABASE path is correct
-   Verify file permissions on database directory

#### 3. Assets Not Loading

-   Run `npm run build` to rebuild assets
-   Check `public_html/build/` directory exists
-   Clear browser cache

#### 4. Storage Link Issues

-   Manual symlink: `ln -s ../storage/app/public public_html/storage`
-   Check `config/filesystems.php` configuration

#### 5. Permission Denied Errors

```bash
# Fix storage permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Fix database permissions
chmod 755 database/
chmod 644 database/database.sqlite
```

### File Structure After Deployment

```
your-domain.com/
├── app/
├── bootstrap/
├── config/
├── database/
│   └── database.sqlite
├── public_html/          # (renamed from public)
│   ├── index.php
│   ├── .htaccess
│   └── storage/          # (symlink to ../storage/app/public)
├── resources/
├── routes/
├── storage/
│   ├── app/
│   │   └── public/
│   └── logs/
├── vendor/
├── .env
├── artisan
└── composer.json
```

## Security Considerations

1. **Environment Variables**:

    - Set `APP_DEBUG=false` in production
    - Use strong `APP_KEY`
    - Keep `.env` file secure

2. **File Permissions**:

    - Storage: 755
    - Database: 644
    - Bootstrap cache: 755

3. **HTTPS**: Ensure SSL certificate is installed

## Performance Optimization

1. **Enable OPcache** (usually enabled by default on Hostinger)
2. **Use database for sessions and cache** (already configured)
3. **Optimize assets**: Run `npm run build` for minified assets
4. **Enable compression** in Hostinger hPanel

## Backup Strategy

1. **Database**: Download `database/database.sqlite` regularly
2. **Files**: Backup important files via File Manager
3. **Environment**: Keep `.env` backed up securely

## Support

If you encounter issues:

1. Check Hostinger's knowledge base
2. Review Laravel logs: `storage/logs/laravel.log`
3. Verify PHP version compatibility
4. Test locally before deploying

## Default Login Credentials

After successful deployment and database seeding:

-   **Admin**: admin@hiddentreasures.com / password
-   **Manager**: manager@hiddentreasures.com / password
-   **VA**: va@hiddentreasures.com / password

---

**Note**: This guide is specific to Hostinger shared hosting with SQLite. For VPS or dedicated hosting, the process would be different and simpler.
