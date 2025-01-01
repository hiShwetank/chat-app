<?php
namespace App\Views\Components;

class UserProfile {
    private $userDetails;

    public function __construct($userDetails = null) {
        $this->userDetails = $userDetails ?? [];
    }

    public function render() {
        $username = $this->userDetails['username'] ?? 'Guest';
        $email = $this->userDetails['email'] ?? 'N/A';
        $status = $this->userDetails['status'] ?? 'offline';
        $profilePicture = $this->userDetails['profile_picture'] ?? '/assets/images/default-profile.png';

        return "
        <div class='user-profile'>
            <img src='" . htmlspecialchars($profilePicture) . "' alt='Profile Picture' class='profile-image'>
            <div class='user-info'>
                <h3>" . htmlspecialchars($username) . "</h3>
                <p>Email: " . htmlspecialchars($email) . "</p>
                <p>Status: " . htmlspecialchars($status) . "</p>
            </div>
        </div>
        ";
    }
}
