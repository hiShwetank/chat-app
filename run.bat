@echo off
setlocal enabledelayedexpansion

:: Check PHP installation
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo Error: PHP is not installed or not in system PATH.
    echo Please install PHP and add it to your system PATH.
    pause
    exit /b 1
)

:: Check project directory
if not exist "%~dp0public\index.php" (
    echo Error: Cannot find index.php in the public directory.
    echo Make sure you are running this from the correct project root.
    pause
    exit /b 1
)

:: Set window title
title ChatApp Development Server

:: Display startup message
echo Starting ChatApp Development Server...
echo.
echo Server will be accessible at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

:: Start PHP built-in server
php -S localhost:8000 -t public

:: Handle server exit
if %errorlevel% neq 0 (
    echo.
    echo Server stopped with an error. Check your PHP configuration.
    pause
)
