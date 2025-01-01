# Hosting Deployment Guide

## Hosting Requirements
- PHP 8.0+
- SQLite Support
- Composer
- WebSocket Support (Optional)

## cPanel Deployment Steps

### 1. Upload Files
1. Upload entire project directory to your hosting account
2. Ensure all files are uploaded correctly

### 2. Composer Dependencies
- Use SSH or cPanel's Terminal:
```bash
cd /path/to/your/chat-app
php composer.phar install --no-dev
```

### 3. Database Initialization
```bash
php database/init_db.php
```

### 4. Permissions
Set proper permissions:
```bash
chmod -R 755 storage
chmod -R 755 public/assets/uploads
```

### 5. WebSocket Configuration
- For WebSocket, you might need:
  - NodeJS
  - Separate WebSocket server
  - Nginx/Apache WebSocket proxy

### 6. Environment Configuration
- Edit `.env` file with your specific settings
- Replace placeholders with actual credentials

## Troubleshooting
- Check `error.log` for any deployment issues
- Verify PHP extensions are installed
- Ensure write permissions for storage directories

## Recommended Hosting Configurations
- Memory Limit: 256M
- Max Execution Time: 300 seconds
- Upload Max Filesize: 50M

## Security Notes
- Keep `.env` file private
- Regularly update dependencies
- Use strong, unique passwords

## WebSocket Hosting
For production, consider:
- Separate WebSocket server
- Load balancing
- SSL/TLS configuration

## Monitoring
- Set up application monitoring
- Regular backups
- Performance tracking
