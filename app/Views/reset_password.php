<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="reset-password-form">
            <h2>Reset Your Password</h2>
            
            <form id="reset-password-form">
                <input type="hidden" id="reset-token" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input 
                        type="password" 
                        id="new-password" 
                        name="new_password" 
                        required 
                        minlength="8" 
                        placeholder="Enter new password"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="confirm-password" 
                        name="confirm_password" 
                        required 
                        minlength="8" 
                        placeholder="Confirm new password"
                    >
                </div>
                
                <div id="password-error" class="error-message"></div>
                
                <button type="submit" class="btn-submit">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('reset-password-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const token = document.getElementById('reset-token').value;
        const errorElement = document.getElementById('password-error');
        
        // Client-side validation
        if (newPassword !== confirmPassword) {
            errorElement.textContent = 'Passwords do not match';
            return;
        }
        
        if (newPassword.length < 8) {
            errorElement.textContent = 'Password must be at least 8 characters long';
            return;
        }
        
        try {
            const response = await fetch('/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    new_password: newPassword
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = '/login';
            } else {
                errorElement.textContent = result.message;
            }
        } catch (error) {
            errorElement.textContent = 'An unexpected error occurred';
        }
    });
    </script>
</body>
</html>
