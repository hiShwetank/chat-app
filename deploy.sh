#!/bin/bash

# Deployment Script for Chat Application

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1-2)
echo "PHP Version: $PHP_VERSION"

# Create necessary directories
mkdir -p storage/logs
mkdir -p storage/database
mkdir -p public/assets/uploads

# Set proper permissions
chmod -R 755 storage
chmod -R 755 public/assets/uploads

# Install Composer dependencies
if [ -f composer.phar ]; then
    php composer.phar install --no-dev --optimize-autoloader
elif command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
else
    echo "Downloading Composer"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php composer.phar install --no-dev --optimize-autoloader
    rm composer-setup.php
fi

# Initialize database if not exists
if [ ! -f storage/database/chat.sqlite ]; then
    php database/init_db.php
fi

# Generate .htaccess for routing
cat > public/.htaccess << EOL
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
EOL

# Create a simple deployment info file
cat > DEPLOYMENT_INFO.txt << EOL
Deployment Date: $(date)
PHP Version: $PHP_VERSION
Deployment Status: Success
Application: Chat WebSocket Application
EOL

echo "Deployment completed successfully!"
