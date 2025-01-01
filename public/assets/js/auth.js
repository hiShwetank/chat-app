document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('/login', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                window.location.href = '/chat';
            } else {
                alert(result.error || 'Login failed');
            }
        } catch (error) {
            console.error('Login error:', error);
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
