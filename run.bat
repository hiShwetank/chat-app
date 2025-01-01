@echo off
setlocal enabledelayedexpansion

:: Change to the directory where the script is located
cd /d "%~dp0"

:: Diagnostic Batch Script

:: Check Windows Version
ver

:: Check System Environment
echo System Information:
systeminfo | findstr /C:"OS Name" /C:"OS Version"

:: Check PHP Installation
echo Checking PHP Installation:
where php
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH.
    echo Recommended PHP locations:
    echo - XAMPP: C:\xampp\php
    echo - WAMP: C:\wamp\bin\php
    echo - Manual installation path
    pause
    exit /b 1
)

php -v

:: Check Composer Installation
echo Checking Composer Installation:
where composer
if %errorlevel% neq 0 (
    echo WARNING: Composer is NOT installed or NOT in PATH.
    echo Download from: https://getcomposer.org/download/
)

:: Verify Project Structure
echo Checking Project Files:
if not exist "run.php" (
    echo ERROR: run.php not found in project directory
    echo Current Directory: %CD%
    echo Ensure you are in the correct project folder
    pause
    exit /b 1
)

if not exist "public\index.php" (
    echo ERROR: public\index.php not found
    pause
    exit /b 1
)

:: Start Services Function
:start_services
echo Starting Services...

:: Web Server
start "Web Server" cmd /c "php run.php serve"

:: WebSocket Server
start "WebSocket Server" cmd /c "php run.php websocket"

:: SMTP Debug Server
start "SMTP Debug Server" cmd /c "php run.php mail"

echo Services started:
echo - Web Server: http://localhost:8000
echo - WebSocket: ws://localhost:8080
echo - SMTP Debug: localhost:1025

:: Open Web Browser
start http://localhost:8000

echo Press any key to stop all services...
pause > nul

:: Stop all PHP processes
taskkill /F /IM php.exe > nul 2>&1
echo All services stopped.

pause
