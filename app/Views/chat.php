<?php
// Ensure user is authenticated
$userDetails = $_SESSION['user_details'] ?? null;
if (!$userDetails) {
    header('Location: /login');
    exit;
}

// Import required models
use App\Models\UserModel;
use App\Models\GroupModel;
use App\Models\FriendModel;

// Initialize models
$db = \App\Services\DatabaseService::getConnection();
$userModel = new UserModel($db);
$groupModel = new GroupModel($db);
$friendModel = new FriendModel($db);

// Get user's groups and friends
try {
    $userGroups = $groupModel->getUserGroups($userDetails['id']);
} catch (Exception $e) {
    $userGroups = [];
}

try {
    $friends = $friendModel->getUserFriends($userDetails['id']);
} catch (Exception $e) {
    $friends = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Dashboard</title>
    <link rel="stylesheet" href="/css/chat.css">
</head>
<body data-user-id="<?= $userDetails['id'] ?>">
    <!-- Notification Container -->
    <div id="notification-container" class="notification-container"></div>

    <!-- Main Chat Container -->
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- User Profile -->
            <div class="user-profile">
                <img src="<?= $userDetails['profile_picture'] ?? '/images/default-avatar.png' ?>" alt="Profile">
                <h2><?= htmlspecialchars($userDetails['username']) ?></h2>
                <p><?= htmlspecialchars($userDetails['email']) ?></p>
                <span class="status <?= $userDetails['status'] ?>">●</span>
            </div>

            <!-- Friends Section -->
            <div class="friends-section">
                <h3>Friends 
                    <button id="add-friend-btn">+</button>
                </h3>
                <div class="friends-list">
                    <?php foreach ($friends as $friend): ?>
                        <div class="friend-item" data-user-id="<?= $friend['id'] ?>">
                            <img src="<?= $friend['profile_picture'] ?? '/images/default-avatar.png' ?>" alt="Friend">
                            <span><?= htmlspecialchars($friend['username']) ?></span>
                            <span class="status <?= $friend['status'] ?>">●</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Groups Section -->
            <div class="groups-section">
                <h3>Groups 
                    <button id="create-group-btn">+</button>
                </h3>
                <div class="groups-list">
                    <?php foreach ($userGroups as $group): ?>
                        <div class="group-item" data-group-id="<?= $group['id'] ?>">
                            <span><?= htmlspecialchars($group['name']) ?></span>
                            <span class="group-role"><?= $group['role'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <!-- Selected Chat Header -->
            <div class="chat-header">
                <h2 id="chat-title">Select a chat</h2>
            </div>

            <!-- Messages Container -->
            <div class="messages-container" id="messages-container">
                <!-- Messages will be dynamically loaded here -->
            </div>

            <!-- Message Input -->
            <div class="message-input">
                <input type="text" id="message-input" placeholder="Type a message...">
                <button id="send-message-btn">Send</button>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="add-friend-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Friend</h2>
            <input type="text" id="friend-username" placeholder="Enter friend's username">
            <button id="send-friend-request">Send Request</button>
        </div>
    </div>

    <div id="create-group-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create New Group</h2>
            <input type="text" id="group-name" placeholder="Group Name">
            <textarea id="group-description" placeholder="Group Description"></textarea>
            <button id="create-group">Create Group</button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/js/websocket.js"></script>
    <script src="/js/chat.js"></script>
</body>
</html>
