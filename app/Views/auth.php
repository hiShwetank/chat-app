<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App - Authentication</title>
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
            color: var(--text-color);
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
            perspective: 1000px;
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
            opacity: 0;
            pointer-events: none;
            transform: rotateY(-90deg);
        }

        .form.active {
            opacity: 1;
            pointer-events: all;
            transform: rotateY(0);
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

        /* Loader */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--primary-color);
            border-top: 5px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Ensure error message is safely handled
        $errorMessage = htmlspecialchars($errorMessage ?? '');
        ?>
        <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo $errorMessage; ?>
        </div>
        <?php endif; ?>

        <div class="forms-container">
            <!-- Login Form -->
            <div class="form login-form active">
                <h2>Login</h2>
                <div class="input-group">
                    <input type="email" class="form-input" id="login-email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="password" class="form-input" id="login-password" placeholder="Password" required>
                </div>
                <button class="btn" id="login-btn">Login</button>
                <div class="error-message" id="login-error"></div>
                <div class="form-footer">
                    <a href="#" id="forgot-password-link">Forgot Password?</a>
                    <p>Don't have an account? <a href="#" id="register-link">Register</a></p>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="form register-form">
                <h2>Register</h2>
                <div class="input-group">
                    <input type="text" class="form-input" id="register-username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="email" class="form-input" id="register-email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="password" class="form-input" id="register-password" placeholder="Password" required>
                </div>
                <div class="input-group">
                    <input type="password" class="form-input" id="register-confirm-password" placeholder="Confirm Password" required>
                </div>
                <button class="btn" id="register-btn">Register</button>
                <div class="error-message" id="register-error"></div>
                <div class="form-footer">
                    <p>Already have an account? <a href="#" id="login-link">Login</a></p>
                </div>
            </div>

            <!-- Forgot Password Form -->
            <div class="form forgot-password-form">
                <h2>Forgot Password</h2>
                <div class="input-group">
                    <input type="email" class="form-input" id="forgot-email" placeholder="Enter your email" required>
                </div>
                <button class="btn" id="forgot-password-btn">Send Reset Link</button>
                <div class="error-message" id="forgot-password-error"></div>
                <div class="form-footer">
                    <p>Remember your password? <a href="#" id="back-to-login-link">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div class="loader" id="global-loader">
        <div class="loader-spinner"></div>
    </div>

    <script>
        // Form Navigation
        const loginForm = document.querySelector('.login-form');
        const registerForm = document.querySelector('.register-form');
        const forgotPasswordForm = document.querySelector('.forgot-password-form');

        // Navigation Links
        const registerLink = document.getElementById('register-link');
        const loginLink = document.getElementById('login-link');
        const forgotPasswordLink = document.getElementById('forgot-password-link');
        const backToLoginLink = document.getElementById('back-to-login-link');

        // Toggle Forms
        function switchForm(hideForm, showForm) {
            hideForm.classList.remove('active');
            showForm.classList.add('active');
        }

        registerLink.addEventListener('click', (e) => {
            e.preventDefault();
            switchForm(loginForm, registerForm);
        });

        loginLink.addEventListener('click', (e) => {
            e.preventDefault();
            switchForm(registerForm, loginForm);
        });

        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            switchForm(loginForm, forgotPasswordForm);
        });

        backToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            switchForm(forgotPasswordForm, loginForm);
        });

        // Loader Utility
        function showLoader() {
            document.getElementById('global-loader').style.display = 'flex';
        }

        function hideLoader() {
            document.getElementById('global-loader').style.display = 'none';
        }

        // Form Submission Handlers
        document.getElementById('login-btn').addEventListener('click', () => {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const errorEl = document.getElementById('login-error');

            // Reset error
            errorEl.textContent = '';

            // Validate inputs
            if (!email || !password) {
                errorEl.textContent = 'Please fill in all fields';
                return;
            }

            // Show loader
            showLoader();

            // Send login request
            fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            })
            .then(response => response.json())
            .then(data => {
                // Hide loader
                hideLoader();

                if (data.success) {
                    // Store token and redirect
                    localStorage.setItem('auth_token', data.token);
                    window.location.href = '/chat';
                } else {
                    errorEl.textContent = data.message;
                }
            })
            .catch(error => {
                // Hide loader
                hideLoader();
                errorEl.textContent = 'Network error. Please try again.';
                console.error('Login error:', error);
            });
        });

        document.getElementById('register-btn').addEventListener('click', () => {
            const username = document.getElementById('register-username').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            const errorEl = document.getElementById('register-error');

            // Reset error
            errorEl.textContent = '';

            // Validate inputs
            if (!username || !email || !password || !confirmPassword) {
                errorEl.textContent = 'Please fill in all fields';
                return;
            }

            if (password !== confirmPassword) {
                errorEl.textContent = 'Passwords do not match';
                return;
            }

            // Show loader
            showLoader();

            // Send registration request
            fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, email, password })
            })
            .then(response => response.json())
            .then(data => {
                // Hide loader
                hideLoader();

                if (data.success) {
                    // Switch to login form
                    switchForm(registerForm, loginForm);
                    document.getElementById('login-email').value = email;
                    document.getElementById('login-error').textContent = 'Registration successful. Please log in.';
                } else {
                    errorEl.textContent = data.message;
                }
            })
            .catch(error => {
                // Hide loader
                hideLoader();
                errorEl.textContent = 'Network error. Please try again.';
                console.error('Registration error:', error);
            });
        });

        document.getElementById('forgot-password-btn').addEventListener('click', () => {
            const email = document.getElementById('forgot-email').value;
            const errorEl = document.getElementById('forgot-password-error');

            // Reset error
            errorEl.textContent = '';

            // Validate input
            if (!email) {
                errorEl.textContent = 'Please enter your email';
                return;
            }

            // Show loader
            showLoader();

            // Send forgot password request
            fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            })
            .then(response => response.json())
            .then(data => {
                // Hide loader
                hideLoader();

                if (data.success) {
                    errorEl.style.color = 'green';
                    errorEl.textContent = 'Password reset link sent to your email';
                } else {
                    errorEl.textContent = data.message;
                }
            })
            .catch(error => {
                // Hide loader
                hideLoader();
                errorEl.textContent = 'Network error. Please try again.';
                console.error('Forgot password error:', error);
            });
        });
    </script>
</body>
</html>
