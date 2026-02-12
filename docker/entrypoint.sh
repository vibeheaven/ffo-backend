#!/bin/sh

set -e

echo "🚀 Starting FFO Backend..."

# Install/Update dependencies if vendor is missing or outdated
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "📦 Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "✅ Composer dependencies already installed"
fi

# Copy .env if missing
if [ ! -f ".env" ]; then
    echo "⚙️  Creating .env file..."
    if [ -f ".env.production" ]; then
        cp .env.production .env
    elif [ -f ".env.example" ]; then
        cp .env.example .env
    else
        echo "❌ No .env template found!"
        exit 1
    fi
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Wait for PostgreSQL
echo "⏳ Waiting for PostgreSQL..."
max_tries=30
count=0

while ! php artisan db:show > /dev/null 2>&1; do
    echo "   Waiting for database connection... ($count/$max_tries)"
    sleep 2
    count=$((count+1))
    if [ $count -gt $max_tries ]; then
        echo "❌ Database connection timed out!"
        exit 1
    fi
done
echo "✅ PostgreSQL connected!"

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force --no-interaction

# Clear and cache config for production
echo "🧹 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if not exists
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Set proper permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "✨ Application ready!"

# Start PHP-FPM
exec php-fpm
