#!/bin/bash

# Chat Application Runner for Unix-like Systems

# Color Codes
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Dependency Checks
check_dependencies() {
    # Check PHP
    if ! command -v php &> /dev/null; then
        echo -e "${RED}PHP is not installed!${NC}"
        exit 1
    fi

    # Check Composer
    if ! command -v composer &> /dev/null; then
        echo -e "${RED}Composer is not installed!${NC}"
        exit 1
    fi
}

# Start Servers
start_servers() {
    echo -e "${GREEN}Starting Chat Application...${NC}"
    
    # Install Dependencies
    composer install

    # Start WebSocket Server
    php src/WebSocket/server.php > websocket.log 2>&1 &
    WEBSOCKET_PID=$!
    echo $WEBSOCKET_PID > websocket.pid

    # Start PHP Built-in Server
    php -S localhost:8000 -t public > php_server.log 2>&1 &
    PHP_SERVER_PID=$!
    echo $PHP_SERVER_PID > php_server.pid

    echo -e "${GREEN}Application started!${NC}"
    echo "WebSocket Server: http://localhost:8080"
    echo "Web Application: http://localhost:8000"
}

# Stop Servers
stop_servers() {
    echo -e "${RED}Stopping Chat Application...${NC}"
    
    # Stop WebSocket Server
    if [ -f websocket.pid ]; then
        kill -9 $(cat websocket.pid)
        rm websocket.pid
    fi

    # Stop PHP Server
    if [ -f php_server.pid ]; then
        kill -9 $(cat php_server.pid)
        rm php_server.pid
    fi

    echo -e "${GREEN}Servers stopped.${NC}"
}

# Main Execution
main() {
    check_dependencies

    case "$1" in
        start)
            start_servers
            ;;
        stop)
            stop_servers
            ;;
        restart)
            stop_servers
            start_servers
            ;;
        *)
            echo "Usage: $0 {start|stop|restart}"
            exit 1
    esac
}

# Execute Main Function
main "${1:-start}"
