class WebSocketManager {
    constructor(userId) {
        this.userId = userId;
        this.socket = null;
        this.connect();
    }

    connect() {
        this.socket = new WebSocket('ws://localhost:8080');

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
            // Attempt to reconnect after a delay
            setTimeout(() => this.connect(), 3000);
        };
    }

    authenticate() {
        this.sendMessage({
            type: 'authenticate',
            user_id: this.userId
        });
    }

    sendMessage(message) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(message));
        } else {
            console.error('WebSocket is not connected');
        }
    }

    sendFriendRequest(receiverId) {
        this.sendMessage({
            type: 'friend_request',
            sender_id: this.userId,
            receiver_id: receiverId
        });
    }

    sendPrivateMessage(receiverId, message) {
        this.sendMessage({
            type: 'message',
            sender_id: this.userId,
            receiver_id: receiverId,
            message: message
        });
    }

    sendGroupMessage(groupId, message) {
        this.sendMessage({
            type: 'group_message',
            sender_id: this.userId,
            group_id: groupId,
            message: message
        });
    }

    handleMessage(data) {
        switch (data.type) {
            case 'authentication':
                this.handleAuthentication(data);
                break;
            case 'friend_request':
                this.handleFriendRequest(data);
                break;
            case 'message':
                this.handlePrivateMessage(data);
                break;
            case 'group_message':
                this.handleGroupMessage(data);
                break;
        }
    }

    handleAuthentication(data) {
        console.log('Authentication status:', data.status);
    }

    handleFriendRequest(data) {
        // Create a notification for friend request
        this.createNotification({
            type: 'friend_request',
            senderId: data.sender_id,
            message: 'New friend request received'
        });
    }

    handlePrivateMessage(data) {
        // Create a notification for private message
        this.createNotification({
            type: 'private_message',
            senderId: data.sender_id,
            message: data.message
        });
    }

    handleGroupMessage(data) {
        // Create a notification for group message
        this.createNotification({
            type: 'group_message',
            groupId: data.group_id,
            senderId: data.sender_id,
            message: data.message
        });
    }

    createNotification(notificationData) {
        // Create a notification element
        const notification = document.createElement('div');
        notification.classList.add('notification');
        
        switch (notificationData.type) {
            case 'friend_request':
                notification.innerHTML = `New friend request from user ${notificationData.senderId}`;
                break;
            case 'private_message':
                notification.innerHTML = `New message from user ${notificationData.senderId}: ${notificationData.message}`;
                break;
            case 'group_message':
                notification.innerHTML = `New group message from user ${notificationData.senderId} in group ${notificationData.groupId}: ${notificationData.message}`;
                break;
        }

        // Add close button
        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.classList.add('close-notification');
        closeBtn.onclick = () => notification.remove();
        notification.appendChild(closeBtn);

        // Add to notification container
        const notificationContainer = document.getElementById('notification-container');
        if (notificationContainer) {
            notificationContainer.appendChild(notification);
        }
    }
}

// Initialize WebSocket when user is logged in
document.addEventListener('DOMContentLoaded', () => {
    const userId = document.body.getAttribute('data-user-id');
    if (userId) {
        window.webSocketManager = new WebSocketManager(userId);
    }
});
