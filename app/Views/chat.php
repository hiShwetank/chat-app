<?php
use App\Views\Components\UserProfile;
use App\Views\Components\FriendsList;
use App\Views\Components\ChatWindow;

// Ensure variables are set with default values
$userDetails = $userDetails ?? [];
$friends = $friends ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link rel="stylesheet" href="/assets/css/chat.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="chat-app-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <?php 
                // Render user profile with null-safe method
                $userProfileComponent = new UserProfile($userDetails);
                echo $userProfileComponent->render(); 
                ?>
                <div class="sidebar-actions">
                    <button class="sidebar-action-btn" id="invite-friend-btn" title="Invite Friend">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button class="sidebar-action-btn" id="create-group-btn" title="Create Group">
                        <i class="fas fa-users"></i>
                    </button>
                </div>
            </div>

            <?php 
            $friendsListComponent = new FriendsList($friends, $userDetails['id'] ?? null);
            echo $friendsListComponent->render();
            $friendsListComponent->renderAddFriendModal();
            ?>
        </div>

        <div class="main-content">
            <?php 
            $chatWindowComponent = new ChatWindow($userDetails);
            $chatWindowComponent->render();
            ?>
            
            <div class="chat-input">
                <div class="chat-input-actions">
                    <button class="chat-input-attachment" title="Attach File">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <button class="chat-input-emoji" title="Choose Emoji">
                        <i class="far fa-smile"></i>
                    </button>
                </div>
                <textarea 
                    id="chat-message-input" 
                    class="chat-input-field" 
                    placeholder="Type a message" 
                    rows="1"
                ></textarea>
                <button class="chat-input-send" disabled title="Send Message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>

            <div class="emoji-picker">
                <div class="emoji-picker-grid">
                    <div class="emoji-item">😀</div>
                    <div class="emoji-item">👍</div>
                    <div class="emoji-item">❤️</div>
                    <div class="emoji-item">😂</div>
                    <div class="emoji-item">🤔</div>
                    <div class="emoji-item">😍</div>
                    <div class="emoji-item">🎉</div>
                    <div class="emoji-item">👏</div>
                </div>
            </div>

            <div class="attachment-picker">
                <div class="attachment-item">
                    <i class="fas fa-image"></i> Photo
                </div>
                <div class="attachment-item">
                    <i class="fas fa-file"></i> Document
                </div>
                <div class="attachment-item">
                    <i class="fas fa-music"></i> Audio
                </div>
                <div class="attachment-item">
                    <i class="fas fa-map-marker-alt"></i> Location
                </div>
            </div>
        </div>
    </div>

    <div class="invite-modal" id="invite-modal">
        <div class="invite-modal-content">
            <div class="invite-modal-header">
                <h2>Invite Friends</h2>
                <button class="invite-modal-close" id="invite-modal-close">&times;</button>
            </div>
            
            <div class="invite-options">
                <div class="invite-option active" data-type="email">
                    <i class="fas fa-envelope"></i> Email
                </div>
                <div class="invite-option" data-type="link">
                    <i class="fas fa-link"></i> Invite Link
                </div>
            </div>

            <div class="invite-form" id="invite-email-form">
                <input 
                    type="email" 
                    class="invite-input" 
                    placeholder="Enter friend's email" 
                    id="invite-email-input"
                >
                <textarea 
                    class="invite-input" 
                    placeholder="Optional personal message" 
                    id="invite-message-input" 
                    rows="3"
                ></textarea>
                <button class="invite-submit" id="send-invite-email">
                    Send Invite
                </button>
            </div>

            <div class="invite-form" id="invite-link-form" style="display: none;">
                <div class="invite-link-section">
                    <div class="invite-link" id="invite-link-display">
                        Loading invite link...
                    </div>
                    <button class="invite-link-copy" id="copy-invite-link">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="invite-link-description">
                    Share this link with friends. It will expire in 7 days.
                </p>
            </div>
        </div>
    </div>

    <script src="/assets/js/websocket.js"></script>
    <script src="/assets/js/chat.js"></script>
    <script src="/assets/js/invite.js"></script>
</body>
</html>
