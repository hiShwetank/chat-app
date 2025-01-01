# Comprehensive Diagnostic and Troubleshooting Script

# Ensure detailed error reporting
$ErrorActionPreference = 'Continue'

# Color coding function
function Write-ColorOutput {
    param(
        [string]$Message, 
        [string]$Color = 'Green'
    )
    Write-Host $Message -ForegroundColor $Color
}

# Detailed System Diagnostic
function Invoke-SystemDiagnostic {
    Write-ColorOutput "=== System Diagnostic ===" 'Cyan'
    
    # PHP Check
    try {
        $phpPath = (Get-Command php -ErrorAction Stop).Source
        $phpVersion = php -v
        Write-ColorOutput "PHP Path: $phpPath" 'Green'
        Write-ColorOutput "PHP Version: $phpVersion" 'Green'
    }
    catch {
        Write-ColorOutput "ERROR: PHP not found!" 'Red'
        Write-Host "Possible PHP locations to check:"
        @(
            "C:\xampp\php\php.exe",
            "C:\wamp\bin\php\php.exe",
            "C:\Program Files\PHP\php.exe"
        ) | ForEach-Object { Write-Host "- $_" }
        return $false
    }

    # Composer Check
    try {
        $composerPath = (Get-Command composer -ErrorAction Stop).Source
        $composerVersion = composer --version
        Write-ColorOutput "Composer Path: $composerPath" 'Green'
        Write-ColorOutput "Composer Version: $composerVersion" 'Green'
    }
    catch {
        Write-ColorOutput "WARNING: Composer not found" 'Yellow'
    }

    # Project Structure Check
    Write-ColorOutput "`n=== Project Structure ===" 'Cyan'
    $requiredFiles = @(
        "run.php",
        "public\index.php",
        "config\database.php",
        "bin\group_manager.php"
    )

    $missingFiles = $requiredFiles | Where-Object { -not (Test-Path $_) }
    
    if ($missingFiles) {
        Write-ColorOutput "Missing Critical Files:" 'Red'
        $missingFiles | ForEach-Object { Write-Host "- $_" }
        return $false
    }
    else {
        Write-ColorOutput "All critical files present" 'Green'
    }

    # Dependency Check
    Write-ColorOutput "`n=== Dependency Check ===" 'Cyan'
    try {
        $composerDeps = composer show
        Write-ColorOutput "Composer Dependencies Installed" 'Green'
    }
    catch {
        Write-ColorOutput "ERROR: Dependencies not installed" 'Red'
        Write-Host "Run 'composer install' to resolve"
        return $false
    }

    return $true
}

# Service Startup Function
function Start-ChatServices {
    Write-ColorOutput "=== Starting Services ===" 'Cyan'
    
    # Web Server
    $webProcess = Start-Process php -ArgumentList "run.php serve" -PassThru -NoNewWindow
    Start-Sleep -Seconds 2
    
    # WebSocket Server
    $wsProcess = Start-Process php -ArgumentList "run.php websocket" -PassThru -NoNewWindow
    Start-Sleep -Seconds 2
    
    # Mail Server
    $mailProcess = Start-Process php -ArgumentList "run.php mail" -PassThru -NoNewWindow
    Start-Sleep -Seconds 2

    # Check Port Availability
    $ports = @(
        @{Port=8000; Name="Web Server"},
        @{Port=8080; Name="WebSocket Server"},
        @{Port=1025; Name="Mail Server"}
    )

    foreach ($portInfo in $ports) {
        $testResult = Test-NetConnection localhost -Port $portInfo.Port
        if ($testResult.TcpTestSucceeded) {
            Write-ColorOutput "$($portInfo.Name) running on port $($portInfo.Port)" 'Green'
        }
        else {
            Write-ColorOutput "FAILED: $($portInfo.Name) on port $($portInfo.Port)" 'Red'
        }
    }

    # Open Web Browser
    Start-Process "http://localhost:8000/debug.php"
}

# Error Logging Function
function Get-RecentErrorLogs {
    Write-ColorOutput "`n=== Recent Error Logs ===" 'Cyan'
    $logFiles = @(
        "error.log",
        "websocket.log",
        "php_error.log",
        "debug_log.txt"
    )

    foreach ($logFile in $logFiles) {
        if (Test-Path $logFile) {
            Write-ColorOutput "Errors in $logFile:" 'Yellow'
            Get-Content $logFile | Select-String -Pattern "Error|Exception" | Select-Object -Last 10
        }
    }
}

# Main Execution
function Invoke-ChatAppDiagnostic {
    # Change to script directory
    Set-Location $PSScriptRoot

    # System Check
    $systemCheckPassed = Invoke-SystemDiagnostic

    # Show Recent Logs
    Get-RecentErrorLogs
    
    # Prompt for Service Start
    if ($systemCheckPassed) {
        $choice = Read-Host "`nDo you want to start services? (Y/N)"
        if ($choice -eq 'Y') {
            Start-ChatServices
        }
    }
    else {
        Write-ColorOutput "System check failed. Please resolve issues before starting services." 'Red'
    }
}

# Run Diagnostic
Invoke-ChatAppDiagnostic
