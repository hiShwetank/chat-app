# Chat Application Deployment Guide

## Prerequisites
- Windows 10/11
- PowerShell 5.1+
- PHP 8.0+
- Composer
- Chocolatey (recommended for easy dependency management)

## Quick Start

### 1. First-Time Setup
1. Open PowerShell as Administrator
2. Install Chocolatey (if not installed):
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

3. Install Dependencies:
```powershell
choco install php composer -y
refreshenv
```

### 2. Application Startup
- Double-click `run.bat`
- OR run in PowerShell:
```powershell
.\run.ps1 start
```

### Supported Commands
- `start`: Launch application
- `stop`: Stop all servers
- `restart`: Restart application

## Troubleshooting
- Ensure PHP and Composer are in system PATH
- Check `websocket.log` and `php_server.log` for errors
- Verify port 8000 and 8080 are available

## Development Notes
- WebSocket Server: `localhost:8080`
- Web Application: `localhost:8000`
- Default Database: SQLite in `database/chat.sqlite`

## Security
- Keep `.env` file secure
- Use strong, unique passwords
- Regularly update dependencies

## Contributing
1. Fork Repository
2. Create Feature Branch
3. Commit Changes
4. Push to Branch
5. Create Pull Request
