# Chat Application

## Prerequisites
- PHP 8.1+
- Composer
- Git

## Installation

### Windows
1. Clone the repository
```bash
git clone https://github.com/yourusername/chat-app.git
cd chat-app
```

2. Install Dependencies
```bash
composer install
```

3. Running the Application
- Use `run.bat` to manage services
- Double-click `run.bat`
- Choose from menu options

### Unix-like Systems (Linux/macOS)
1. Clone the repository
```bash
git clone https://github.com/yourusername/chat-app.git
cd chat-app
chmod +x run.sh
```

2. Install Dependencies
```bash
composer install
```

3. Running the Application
```bash
# Start all services
./run.sh

# Alternative: Use PHP directly
php run.php serve      # Start web server
php run.php websocket  # Start WebSocket server
php run.php mail       # Start SMTP debug server
```

## Service URLs
- Web Application: `http://localhost:8000`
- WebSocket Server: `ws://localhost:8080`
- SMTP Debug Server: `localhost:1025`

## Troubleshooting
- Ensure PHP is in system PATH
- Check `logs/` directory for error logs
- Verify all dependencies are installed

## Development
- Use `php run.php migrate` to run database migrations
- Use group management tools in `bin/group_manager.php`

## Security
- Never run scripts as root/administrator unless necessary
- Keep sensitive files (like `.env`) secure
- Regularly update dependencies
