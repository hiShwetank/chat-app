<?php
namespace App\Views\Components;

class ChatWindow {
    private $currentUser;
    private $activeChatPartner;

    public function __construct($currentUser, $activeChatPartner = null) {
        $this->currentUser = $currentUser;
        $this->activeChatPartner = $activeChatPartner;
    }

    public function render() {
        ?>
        <div class="chat-window-container">
            <?php if ($this->activeChatPartner): ?>
                <div class="chat-header">
                    <div class="chat-partner-info">
                        <img src="/assets/img/avatars/<?php echo htmlspecialchars($this->activeChatPartner['username']); ?>.png" 
                             onerror="this.src='/assets/img/default-avatar.png'" 
                             alt="<?php echo htmlspecialchars($this->activeChatPartner['username']); ?>">
                        <div class="partner-details">
                            <h3><?php echo htmlspecialchars($this->activeChatPartner['username']); ?></h3>
                            <span class="partner-status <?php echo $this->activeChatPartner['status'] ?? 'offline'; ?>">
                                <?php echo ucfirst($this->activeChatPartner['status'] ?? 'offline'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="btn-voice-call">📞 Voice Call</button>
                        <button class="btn-video-call">🎥 Video Call</button>
                        <button class="btn-more-options">⋯</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-chat-selected">
                    <h2>Select a friend to start chatting</h2>
                    <p>Choose someone from your friends list to begin a conversation.</p>
                </div>
            <?php endif; ?>

            <div class="messages-container" id="messages-container">
                <?php if ($this->activeChatPartner): ?>
                    <!-- Messages will be dynamically loaded here -->
                    <div class="message-loader">Loading chat history...</div>
                <?php endif; ?>
            </div>

            <?php if ($this->activeChatPartner): ?>
                <div class="message-input-container">
                    <div class="input-actions">
                        <button class="btn-attach">📎</button>
                        <button class="btn-emoji">😊</button>
                    </div>
                    <textarea 
                        id="message-input" 
                        placeholder="Type a message..."
                        rows="1"
                    ></textarea>
                    <button class="btn-send-message">Send</button>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
