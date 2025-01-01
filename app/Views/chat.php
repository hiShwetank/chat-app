<?php
use App\Views\Components\UserProfile;
use App\Views\Components\FriendsList;
use App\Views\Components\FriendRequests;
use App\Views\Components\ChatWindow;

// Ensure variables are set with default values
$userDetails = $userDetails ?? [];
$friends = $friends ?? [];
$friendRequests = $friendRequests ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/chat.css">
</head>
<body>
    <div class="chat-app-container">
        <div class="sidebar">
            <?php 
            // Render user profile with null-safe method
            $userProfileComponent = new UserProfile($userDetails);
            echo $userProfileComponent->render(); 
            ?>

            <?php 
            $friendsListComponent = new FriendsList($friends, $userDetails['id'] ?? null);
            echo $friendsListComponent->render();
            $friendsListComponent->renderAddFriendModal();
            ?>

            <?php 
            // Render friend requests with null-safe method
            $friendRequestsComponent = new FriendRequests($friendRequests);
            echo $friendRequestsComponent->render(); 
            ?>
        </div>

        <div class="main-content">
            <?php 
            $chatWindowComponent = new ChatWindow($userDetails);
            $chatWindowComponent->render();
            ?>
        </div>
    </div>

    <script src="/assets/js/websocket.js"></script>
    <script src="/assets/js/chat.js"></script>
</body>
</html>
