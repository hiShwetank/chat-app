document.addEventListener('DOMContentLoaded', () => {
    const messageInput = document.getElementById('chat-message-input');
    const sendButton = document.querySelector('.chat-input-send');
    const emojiButton = document.querySelector('.chat-input-emoji');
    const attachmentButton = document.querySelector('.chat-input-attachment');
    const emojiPicker = document.querySelector('.emoji-picker');
    const attachmentPicker = document.querySelector('.attachment-picker');

    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        
        // Enable/disable send button based on input
        sendButton.disabled = this.value.trim().length === 0;
    });

    // Send message
    sendButton.addEventListener('click', () => {
        const message = messageInput.value.trim();
        if (message) {
            // TODO: Implement WebSocket message sending logic
            console.log('Sending message:', message);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendButton.disabled = true;
        }
    });

    // Emoji picker toggle
    emojiButton.addEventListener('click', () => {
        emojiPicker.classList.toggle('show');
        attachmentPicker.classList.remove('show');
    });

    // Attachment picker toggle
    attachmentButton.addEventListener('click', () => {
        attachmentPicker.classList.toggle('show');
        emojiPicker.classList.remove('show');
    });

    // Emoji selection
    document.querySelectorAll('.emoji-item').forEach(emoji => {
        emoji.addEventListener('click', () => {
            messageInput.value += emoji.textContent;
            messageInput.style.height = 'auto';
            messageInput.style.height = (messageInput.scrollHeight) + 'px';
            sendButton.disabled = false;
            emojiPicker.classList.remove('show');
        });
    });

    // Attachment selection
    document.querySelectorAll('.attachment-item').forEach(attachment => {
        attachment.addEventListener('click', () => {
            const attachmentType = attachment.textContent.trim();
            // TODO: Implement file upload logic for different attachment types
            console.log('Selected attachment:', attachmentType);
            attachmentPicker.classList.remove('show');
        });
    });

    // Close pickers when clicking outside
    document.addEventListener('click', (event) => {
        if (!emojiButton.contains(event.target) && !emojiPicker.contains(event.target)) {
            emojiPicker.classList.remove('show');
        }
        if (!attachmentButton.contains(event.target) && !attachmentPicker.contains(event.target)) {
            attachmentPicker.classList.remove('show');
        }
    });
});
