#!/bin/bash

# Chat Application Management Script

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] ${1}"
}

# Check PHP installation
check_php() {
    if ! command -v php &> /dev/null; then
        log "${RED}ERROR: PHP is not installed or not in PATH${NC}"
        echo "Please install PHP:"
        echo "- For Ubuntu/Debian: sudo apt-get install php"
        echo "- For macOS with Homebrew: brew install php"
        echo "- For CentOS/RHEL: sudo yum install php"
        exit 1
    fi
    php_version=$(php -v | head -n 1)
    log "${GREEN}PHP detected: $php_version${NC}"
}

# Check project structure
check_project_structure() {
    local required_files=(
        "run.php"
        "public/index.php"
        "bin/group_manager.php"
    )

    for file in "${required_files[@]}"; do
        if [ ! -f "$file" ]; then
            log "${RED}Missing required file: $file${NC}"
            exit 1
        fi
    done
}

# Start services
start_services() {
    log "${GREEN}Starting Services:${NC}"
    
    # Web Server
    php run.php serve &
    web_pid=$!
    
    # WebSocket Server
    php run.php websocket &
    websocket_pid=$!
    
    # SMTP Debug Server
    php run.php mail &
    mail_pid=$!

    echo "Services started:"
    echo "- Web Server: http://localhost:8000"
    echo "- WebSocket: ws://localhost:8080"
    echo "- SMTP Debug: localhost:1025"

    # Trap to ensure clean shutdown
    trap "kill $web_pid $websocket_pid $mail_pid" SIGINT SIGTERM EXIT

    # Wait for services
    wait
}

# Stop services
stop_services() {
    log "${YELLOW}Stopping all PHP services${NC}"
    pkill -f "php run.php"
}

# Main script
main() {
    # Check for root/sudo
    if [[ $EUID -eq 0 ]]; then
        log "${YELLOW}Warning: Running as root is not recommended${NC}"
    fi

    # Perform checks
    check_php
    check_project_structure

    # Start services
    start_services
}

# Execute main function
main
