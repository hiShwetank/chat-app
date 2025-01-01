# Comprehensive Error Handling and Logging
$ErrorActionPreference = 'Stop'
$LogFile = "e:/ai/chat/chat-app/deployment_log.txt"

# Logging Function
function Write-Log {
    param(
        [string]$Message,
        [string]$Level = "INFO"
    )
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogMessage = "[$Timestamp] [$Level] $Message"
    Add-Content -Path $LogFile -Value $LogMessage
    
    # Console Output
    switch ($Level) {
        "ERROR" { Write-Host $LogMessage -ForegroundColor Red }
        "WARNING" { Write-Host $LogMessage -ForegroundColor Yellow }
        "SUCCESS" { Write-Host $LogMessage -ForegroundColor Green }
        default { Write-Host $LogMessage }
    }
}

# Comprehensive Dependency Check
function Verify-Dependencies {
    # PHP Check
    try {
        $phpVersion = php -v
        if ($phpVersion -match 'PHP (\d+\.\d+)') {
            $version = $Matches[1]
            if ([double]$version -lt 8.0) {
                throw "PHP version too low. Required 8.0+"
            }
            Write-Log "PHP Version $version detected" "SUCCESS"
        }
    }
    catch {
        Write-Log "PHP not found or incompatible: $_" "ERROR"
        
        # Attempt to install PHP
        try {
            Write-Log "Attempting to install PHP via Chocolatey" "WARNING"
            choco install php -y
            refreshenv
        }
        catch {
            Write-Log "Failed to install PHP: $_" "ERROR"
            throw "PHP installation failed"
        }
    }

    # Composer Check
    try {
        $composerVersion = composer --version
        Write-Log "Composer detected" "SUCCESS"
    }
    catch {
        Write-Log "Composer not found" "WARNING"
        try {
            Write-Log "Attempting to install Composer" "WARNING"
            php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
            php composer-setup.php
            php -r "unlink('composer-setup.php');"
            refreshenv
        }
        catch {
            Write-Log "Composer installation failed: $_" "ERROR"
            throw "Composer installation failed"
        }
    }

    # Install Dependencies
    try {
        Write-Log "Installing project dependencies" "INFO"
        composer install --no-interaction
        Write-Log "Dependencies installed successfully" "SUCCESS"
    }
    catch {
        Write-Log "Dependency installation failed: $_" "ERROR"
        throw "Dependency installation error"
    }
}

# Database Initialization
function Initialize-Database {
    try {
        Write-Log "Initializing database" "INFO"
        
        # Ensure database directory exists
        $dbDir = "e:/ai/chat/chat-app/database"
        if (!(Test-Path $dbDir)) {
            New-Item -ItemType Directory -Path $dbDir
        }

        # Run database initialization script
        php "e:/ai/chat/chat-app/database/init_db.php"
        Write-Log "Database initialized successfully" "SUCCESS"
    }
    catch {
        Write-Log "Database initialization failed: $_" "ERROR"
        throw "Database initialization error"
    }
}

# Start WebSocket Server
function Start-WebSocket-Server {
    try {
        Write-Log "Starting WebSocket server" "INFO"
        $websocketProcess = Start-Process php -ArgumentList "e:/ai/chat/chat-app/src/WebSocket/server.php" -PassThru -NoNewWindow -RedirectStandardOutput "websocket.log" -RedirectStandardError "websocket_error.log"
        
        # Wait and verify server start
        Start-Sleep -Seconds 3
        $port = 8080
        $process = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
        
        if ($process) {
            Write-Log "WebSocket server running on port $port" "SUCCESS"
            $websocketProcess.Id | Out-File -FilePath "websocket.pid"
        }
        else {
            throw "WebSocket server failed to start"
        }
    }
    catch {
        Write-Log "WebSocket server startup failed: $_" "ERROR"
        throw "WebSocket server startup error"
    }
}

# Start PHP Built-in Server
function Start-PHP-Server {
    try {
        Write-Log "Starting PHP built-in server" "INFO"
        $phpServerProcess = Start-Process php -ArgumentList "-S", "localhost:8000", "-t", "e:/ai/chat/chat-app/public" -PassThru -NoNewWindow -RedirectStandardOutput "php_server.log" -RedirectStandardError "php_server_error.log"
        
        # Wait and verify server start
        Start-Sleep -Seconds 3
        $port = 8000
        $process = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
        
        if ($process) {
            Write-Log "PHP server running on port $port" "SUCCESS"
            $phpServerProcess.Id | Out-File -FilePath "php_server.pid"
            
            # Open browser
            Start-Process "http://localhost:8000"
        }
        else {
            throw "PHP server failed to start"
        }
    }
    catch {
        Write-Log "PHP server startup failed: $_" "ERROR"
        throw "PHP server startup error"
    }
}

# Main Execution Function
function Start-Application {
    try {
        # Clear previous logs
        if (Test-Path $LogFile) { Clear-Content $LogFile }
        
        Write-Log "Starting Chat Application Deployment" "INFO"
        
        # Comprehensive Setup
        Verify-Dependencies
        Initialize-Database
        Start-WebSocket-Server
        Start-PHP-Server
        
        Write-Log "Chat Application Deployment Completed Successfully" "SUCCESS"
    }
    catch {
        Write-Log "Deployment Failed: $_" "ERROR"
        throw "Application deployment error"
    }
}

# Execute
Start-Application
