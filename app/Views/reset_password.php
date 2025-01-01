<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App - Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f4f6f9;
            --text-color: #333;
            --white: #ffffff;
            --transition-speed: 0.6s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .container {
            position: relative;
            width: 100%;
            max-width: 1000px;
            height: 600px;
            background: var(--white);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-radius: 15px;
        }

        .forms-container {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            transition: all var(--transition-speed) ease-in-out;
        }

        .form {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0 5%;
            transition: all var(--transition-speed) ease-in-out;
            position: absolute;
            opacity: 1;
            pointer-events: all;
            z-index: 1;
        }

        .form h2 {
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .input-group {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 10px 0;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .btn {
            width: 100%;
            max-width: 400px;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-footer {
            margin-top: 20px;
            text-align: center;
        }

        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }

        .success-message {
            color: var(--secondary-color);
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forms-container">
            <div class="form reset-password-form">
                <h2>Reset Password</h2>
                <input type="hidden" id="reset-token" name="token" 
                       value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <div class="input-group">
                    <input 
                        type="password" 
                        id="new-password" 
                        class="form-input" 
                        placeholder="New Password" 
                        required 
                        minlength="8"
                    >
                </div>
                
                <div class="input-group">
                    <input 
                        type="password" 
                        id="confirm-password" 
                        class="form-input" 
                        placeholder="Confirm New Password" 
                        required 
                        minlength="8"
                    >
                </div>
                
                <button class="btn" id="reset-password-btn">Reset Password</button>
                
                <div id="password-error" class="error-message"></div>
                <div id="password-success" class="success-message"></div>
                
                <div class="form-footer">
                    <p>Remember your password? <a href="/login">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('reset-password-btn').addEventListener('click', async function() {
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const token = document.getElementById('reset-token').value;
        const errorElement = document.getElementById('password-error');
        const successElement = document.getElementById('password-success');
        const resetButton = document.getElementById('reset-password-btn');

        // Reset previous messages
        errorElement.textContent = '';
        successElement.textContent = '';

        // Client-side validation
        if (newPassword !== confirmPassword) {
            errorElement.textContent = 'Passwords do not match';
            return;
        }
        
        if (newPassword.length < 8) {
            errorElement.textContent = 'Password must be at least 8 characters long';
            return;
        }

        // Disable button and change text
        resetButton.disabled = true;
        resetButton.textContent = 'Resetting...';
        
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
                successElement.textContent = result.message;
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                errorElement.textContent = result.message;
                resetButton.disabled = false;
                resetButton.textContent = 'Reset Password';
            }
        } catch (error) {
            errorElement.textContent = 'An unexpected error occurred';
            resetButton.disabled = false;
            resetButton.textContent = 'Reset Password';
        }
    });
    </script>
</body>
</html>
