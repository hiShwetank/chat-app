<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat App - Home</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Chat App</h1>
        <div class="auth-container">
            <div class="login-form">
                <h2>Login</h2>
                <form id="login-form">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
            <div class="register-form">
                <h2>Register</h2>
                <form id="register-form">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>
    <script src="/assets/js/auth.js"></script>
</body>
</html>
