@echo off
:: Elevate to Admin and Run PowerShell Script
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process powershell -ArgumentList '-NoProfile -ExecutionPolicy Bypass -File ""run.ps1""' -Verb RunAs"

:: Chat Application Runner for Windows

:: Check PHP Installation
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo PHP is not installed or not in PATH.
    echo Please install PHP and add it to your system PATH.
    pause
    exit /b 1
)

:: Check Composer Installation
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo Composer is not installed or not in PATH.
    echo Please install Composer and add it to your system PATH.
    pause
    exit /b 1
)

:: Action Selection
set "action=%1"
if "%action%"=="" set "action=start"

:: Execute PHP Run Script
php run.php %action%

:: Keep console open if not explicitly closed
if "%action%"=="start" (
    echo Application is running. Press Ctrl+C to stop.
    pause
)

endlocal
