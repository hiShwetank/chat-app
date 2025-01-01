class ChatWebSocket {
    constructor(url) {
        this.socket = new WebSocket(url);
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.socket.onopen = () => {
            console.log('WebSocket connection established');
            this.authenticate();
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.socket.onclose = () => {
            console.log('WebSocket connection closed');
            // Attempt to reconnect
            setTimeout(() => this.connect(), 3000);
        };
    }

    authenticate() {
        const token = this.getAuthToken();
        if (token) {
            this.socket.send(JSON.stringify({
                type: 'authenticate',
                token: token
            }));
        }
    }

    sendPrivateMessage(receiverId, message) {
        this.socket.send(JSON.stringify({
            type: 'private_message',
            receiver_id: receiverId,
            message: message
        }));
    }

    sendGroupMessage(groupId, message) {
        this.socket.send(JSON.stringify({
            type: 'group_message',
            group_id: groupId,
            message: message
        }));
    }

    handleMessage(data) {
        switch (data.type) {
            case 'private_message':
                this.displayPrivateMessage(data);
                break;
            case 'group_message':
                this.displayGroupMessage(data);
                break;
            case 'user_status':
                this.updateUserStatus(data);
                break;
        }
    }

    displayPrivateMessage(data) {
        // Implement private message display logic
    }

    displayGroupMessage(data) {
        // Implement group message display logic
    }

    updateUserStatus(data) {
        // Implement user status update logic
    }

    getAuthToken() {
        // Retrieve JWT from cookie or localStorage
        return document.cookie.replace(/(?:(?:^|.*;\s*)auth_token\s*\=\s*([^;]*).*$)|^.*$/, "$1");
    }
}

// Initialize WebSocket connection
const chatSocket = new ChatWebSocket('ws://localhost:8080');
