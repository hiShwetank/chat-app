<?php
namespace App\Views\Components;

class FriendsList {
    private $friends;
    private $currentUserId;

    public function __construct($friends, $currentUserId) {
        $this->friends = $friends;
        $this->currentUserId = $currentUserId;
    }

    public function render() {
        ?>
        <div class="friends-container">
            <div class="friends-header">
                <h3>Friends</h3>
                <div class="friends-actions">
                    <button id="add-friend-btn" class="btn-add-friend" title="Add New Friend">
                        <i class="icon-plus"></i>
                    </button>
                    <button id="friend-search-btn" class="btn-search-friend" title="Search Friends">
                        <i class="icon-search"></i>
                    </button>
                </div>
            </div>

            <div class="friends-list">
                <?php if (empty($this->friends)): ?>
                    <p class="no-friends-message">No friends yet. Start connecting!</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($this->friends as $friend): ?>
                            <li class="friend-item" 
                                data-user-id="<?php echo $friend['id']; ?>"
                                data-username="<?php echo htmlspecialchars($friend['username']); ?>">
                                <div class="friend-avatar">
                                    <img src="/assets/img/avatars/<?php echo htmlspecialchars($friend['username']); ?>.png" 
                                         onerror="this.src='/assets/img/default-avatar.png'" 
                                         alt="<?php echo htmlspecialchars($friend['username']); ?>">
                                    <span class="status-indicator <?php echo $friend['status'] ?? 'offline'; ?>"></span>
                                </div>
                                <div class="friend-details">
                                    <h4><?php echo htmlspecialchars($friend['username']); ?></h4>
                                    <p class="friend-status"><?php echo $friend['status'] ?? 'Offline'; ?></p>
                                </div>
                                <div class="friend-actions">
                                    <button class="btn-chat" title="Start Chat">💬</button>
                                    <button class="btn-call" title="Voice Call">📞</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function renderAddFriendModal() {
        ?>
        <div id="add-friend-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Add New Friend</h2>
                <form id="add-friend-form">
                    <input type="text" 
                           id="friend-username" 
                           placeholder="Enter friend's username" 
                           required>
                    <button type="submit">Send Friend Request</button>
                </form>
            </div>
        </div>
        <?php
    }
}
