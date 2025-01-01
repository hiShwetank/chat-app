document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-btn');
    const loginEmailInput = document.getElementById('login-email');
    const loginPasswordInput = document.getElementById('login-password');
    const loginErrorElement = document.getElementById('login-error');
    const registerForm = document.getElementById('register-form');

    loginForm.addEventListener('click', async (e) => {
        e.preventDefault();
        
        const email = loginEmailInput.value;
        const password = loginPasswordInput.value;
        
        // Reset previous error
        loginErrorElement.textContent = '';
        loginForm.disabled = true;
        loginForm.innerHTML = 'Logging in...';
        
        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Store token in localStorage or cookie
                localStorage.setItem('auth_token', result.token);
                
                // Redirect to chat
                window.location.href = '/chat';
            } else {
                // Show error message
                loginErrorElement.textContent = result.message;
                loginForm.disabled = false;
                loginForm.innerHTML = 'Login';
            }
        } catch (error) {
            console.error('Login error:', error);
            loginErrorElement.textContent = 'An unexpected error occurred';
            loginForm.disabled = false;
            loginForm.innerHTML = 'Login';
        }
    });

    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        
        try {
            const response = await fetch('/register', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert('Registration successful! Please log in.');
                registerForm.reset();
            } else {
                alert(result.error || 'Registration failed');
            }
        } catch (error) {
            console.error('Registration error:', error);
        }
    });
});
