#!/bin/sh

# Install dependencies if vendor directory is missing
if [ ! -d "vendor" ]; then
    composer install
fi

# Copy .env example if .env is missing
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Generate key if not set
if ! grep -q "APP_KEY=base64" .env; then
    php artisan key:generate
fi

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
max_tries=30
count=0
while ! php -r "try { new PDO('mysql:host=db;port=3306;dbname=aethron', 'laravel', 'password'); echo 'Connected'; exit(0); } catch(Exception \$e) { echo 'Connection failed: ' . \$e->getMessage(); exit(1); }" > /dev/null 2>&1; do
    echo "Waiting for database connection... ($count/$max_tries)"
    sleep 2
    count=$((count+1))
    if [ $count -gt $max_tries ]; then
        echo "Error: Database connection timed out."
        # Don't exit, try to proceed anyway, maybe it's just slow
        break
    fi
done
echo "MySQL connected or timed out, proceeding..."

# Run migrations
php artisan migrate --force

# Clear caches
php artisan optimize:clear

# Start PHP-FPM
php-fpm
