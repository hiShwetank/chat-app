<?php
namespace App\Views\Components;

class FriendRequests {
    private $requests;
    private $currentUserId;

    public function __construct($requests = [], $currentUserId) {
        $this->requests = $requests ?? [];
        $this->currentUserId = $currentUserId;
    }

    public function render() {
        ?>
        <div class="friend-requests-container">
            <div class="friend-requests-header">
                <h3>Friend Requests</h3>
                <span class="request-count">
                    <?php echo count($this->requests); ?>
                </span>
            </div>

            <div class="friend-requests-list">
                <?php if (!is_array($this->requests) || count($this->requests) === 0): ?>
                    <p class="no-requests-message">No pending friend requests</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($this->requests as $request): ?>
                            <li class="friend-request-item" 
                                data-request-id="<?php echo $request['id'] ?? ''; ?>"
                                data-sender-id="<?php echo $request['sender_id'] ?? ''; ?>">
                                <div class="request-sender-avatar">
                                    <img src="/assets/img/avatars/<?php echo htmlspecialchars($request['username'] ?? ''); ?>.png" 
                                         onerror="this.src='/assets/img/default-avatar.png'" 
                                         alt="<?php echo htmlspecialchars($request['username'] ?? ''); ?>">
                                </div>
                                <div class="request-details">
                                    <h4><?php echo htmlspecialchars($request['username'] ?? ''); ?></h4>
                                    <p>Wants to connect</p>
                                </div>
                                <div class="request-actions">
                                    <button class="btn-accept-request">Accept</button>
                                    <button class="btn-decline-request">Decline</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
