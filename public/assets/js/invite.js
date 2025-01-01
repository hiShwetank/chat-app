document.addEventListener('DOMContentLoaded', () => {
    const inviteModal = document.getElementById('invite-modal');
    const inviteFriendBtn = document.getElementById('invite-friend-btn');
    const inviteModalClose = document.getElementById('invite-modal-close');
    
    const inviteOptions = document.querySelectorAll('.invite-option');
    const inviteEmailForm = document.getElementById('invite-email-form');
    const inviteLinkForm = document.getElementById('invite-link-form');
    
    const sendInviteEmailBtn = document.getElementById('send-invite-email');
    const inviteEmailInput = document.getElementById('invite-email-input');
    const inviteMessageInput = document.getElementById('invite-message-input');
    
    const inviteLinkDisplay = document.getElementById('invite-link-display');
    const copyInviteLinkBtn = document.getElementById('copy-invite-link');

    // Open invite modal
    inviteFriendBtn.addEventListener('click', () => {
        inviteModal.style.display = 'flex';
        
        // Generate invite link when modal opens
        generateInviteLink();
    });

    // Close invite modal
    inviteModalClose.addEventListener('click', () => {
        inviteModal.style.display = 'none';
    });

    // Close modal when clicking outside
    inviteModal.addEventListener('click', (event) => {
        if (event.target === inviteModal) {
            inviteModal.style.display = 'none';
        }
    });

    // Switch between email and link invite options
    inviteOptions.forEach(option => {
        option.addEventListener('click', () => {
            // Remove active class from all options
            inviteOptions.forEach(opt => opt.classList.remove('active'));
            
            // Add active class to clicked option
            option.classList.add('active');
            
            // Show/hide appropriate form
            const inviteType = option.dataset.type;
            if (inviteType === 'email') {
                inviteEmailForm.style.display = 'flex';
                inviteLinkForm.style.display = 'none';
            } else {
                inviteEmailForm.style.display = 'none';
                inviteLinkForm.style.display = 'flex';
            }
        });
    });

    // Send email invite
    sendInviteEmailBtn.addEventListener('click', () => {
        const email = inviteEmailInput.value.trim();
        const message = inviteMessageInput.value.trim();

        if (!validateEmail(email)) {
            showAlert('Please enter a valid email address', 'error');
            return;
        }

        // Disable button during request
        sendInviteEmailBtn.disabled = true;
        sendInviteEmailBtn.textContent = 'Sending...';

        fetch('/invite/email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${getAuthToken()}`
            },
            body: JSON.stringify({
                email: email,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Invite sent successfully!', 'success');
                inviteEmailInput.value = '';
                inviteMessageInput.value = '';
                inviteModal.style.display = 'none';
            } else {
                showAlert(data.message || 'Failed to send invite', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while sending the invite', 'error');
        })
        .finally(() => {
            sendInviteEmailBtn.disabled = false;
            sendInviteEmailBtn.textContent = 'Send Invite';
        });
    });

    // Generate invite link
    function generateInviteLink() {
        inviteLinkDisplay.textContent = 'Generating invite link...';

        fetch('/invite/generate-link', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${getAuthToken()}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                inviteLinkDisplay.textContent = data.link;
            } else {
                inviteLinkDisplay.textContent = 'Failed to generate invite link';
                showAlert(data.message || 'Error generating invite link', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            inviteLinkDisplay.textContent = 'Error generating invite link';
            showAlert('Network error occurred', 'error');
        });
    }

    // Copy invite link
    copyInviteLinkBtn.addEventListener('click', () => {
        const inviteLink = inviteLinkDisplay.textContent;
        
        // Use Clipboard API
        navigator.clipboard.writeText(inviteLink).then(() => {
            showAlert('Invite link copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showAlert('Failed to copy invite link', 'error');
        });
    });

    // Email validation helper
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    // Get auth token from storage
    function getAuthToken() {
        return localStorage.getItem('auth_token') || '';
    }

    // Show alert messages
    function showAlert(message, type = 'info') {
        // Create alert element if it doesn't exist
        let alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            alertContainer.style.position = 'fixed';
            alertContainer.style.top = '20px';
            alertContainer.style.right = '20px';
            alertContainer.style.zIndex = '1000';
            document.body.appendChild(alertContainer);
        }

        // Create alert
        const alert = document.createElement('div');
        alert.textContent = message;
        alert.style.padding = '10px';
        alert.style.marginBottom = '10px';
        alert.style.borderRadius = '5px';
        alert.style.color = 'white';
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.3s ease';

        // Set color based on type
        switch(type) {
            case 'success':
                alert.style.backgroundColor = '#28a745';
                break;
            case 'error':
                alert.style.backgroundColor = '#dc3545';
                break;
            default:
                alert.style.backgroundColor = '#17a2b8';
        }

        // Add to container
        alertContainer.appendChild(alert);

        // Animate in
        requestAnimationFrame(() => {
            alert.style.opacity = '1';
        });

        // Remove after 3 seconds
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alertContainer.removeChild(alert);
            }, 300);
        }, 3000);
    }
});
