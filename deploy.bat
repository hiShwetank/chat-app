@echo off
setlocal enabledelayedexpansion

REM Deployment Script for Chat Application (Windows)

REM Check PHP version
for /f "tokens=2 delims= " %%a in ('php -v ^| findstr /C:"PHP"') do set PHP_VERSION=%%a
echo PHP Version: !PHP_VERSION!

REM Create necessary directories
if not exist "storage\logs" mkdir storage\logs
if not exist "storage\database" mkdir storage\database
if not exist "public\assets\uploads" mkdir public\assets\uploads

REM Set permissions (Windows equivalent)
icacls storage /grant Everyone:R
icacls public\assets\uploads /grant Everyone:R

REM Install Composer dependencies
if exist composer.phar (
    php composer.phar install --no-dev --optimize-autoloader
) else if exist "%USERPROFILE%\AppData\Roaming\Composer\vendor\bin\composer" (
    composer install --no-dev --optimize-autoloader
) else (
    echo Downloading Composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php composer.phar install --no-dev --optimize-autoloader
    del composer-setup.php
)

REM Initialize database if not exists
if not exist "storage\database\chat.sqlite" (
    php database\init_db.php
)

REM Generate .htaccess for routing
(
echo RewriteEngine On
echo RewriteCond %%{REQUEST_FILENAME} !-f
echo RewriteCond %%{REQUEST_FILENAME} !-d
echo RewriteRule ^(.*)$ index.php [L]
) > public\.htaccess

REM Create deployment info file
(
echo Deployment Date: %date% %time%
echo PHP Version: !PHP_VERSION!
echo Deployment Status: Success
echo Application: Chat WebSocket Application
) > DEPLOYMENT_INFO.txt

echo Deployment completed successfully!
