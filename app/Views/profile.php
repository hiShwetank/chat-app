<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile</title>
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-picture">
                <img src="<?= $user['profile_picture'] ? '/uploads/profiles/' . $user['profile_picture'] : '/assets/images/default-profile.png' ?>" 
                     alt="Profile Picture" 
                     class="profile-img">
                <form id="profile-pic-upload" enctype="multipart/form-data">
                    <input type="file" name="profile_picture" accept="image/*" class="file-input">
                    <button type="submit" class="upload-btn">Change Picture</button>
                </form>
            </div>
            
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <p>Member since: <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                <p>Status: <?= htmlspecialchars($user['status']) ?></p>
            </div>
        </div>

        <div class="profile-sections">
            <div class="groups-section">
                <h2>Groups</h2>
                <?php if (!empty($user['groups'])): ?>
                    <ul class="groups-list">
                        <?php foreach ($user['groups'] as $group): ?>
                            <li>
                                <strong><?= htmlspecialchars($group['name']) ?></strong>
                                <span><?= htmlspecialchars($group['role']) ?></span>
                                <p><?= htmlspecialchars($group['description'] ?? 'No description') ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>You are not a member of any groups yet.</p>
                <?php endif; ?>
            </div>

            <div class="friends-section">
                <h2>Friends</h2>
                <?php if (!empty($user['friends'])): ?>
                    <ul class="friends-list">
                        <?php foreach ($user['friends'] as $friend): ?>
                            <li>
                                <img src="<?= $friend['profile_picture'] ? '/uploads/profiles/' . $friend['profile_picture'] : '/assets/images/default-profile.png' ?>" 
                                     alt="<?= htmlspecialchars($friend['username']) ?>">
                                <div class="friend-info">
                                    <strong><?= htmlspecialchars($friend['username']) ?></strong>
                                    <p><?= htmlspecialchars($friend['status']) ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>You have no friends yet. Start connecting!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/assets/js/profile.js"></script>
</body>
</html>
