<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome — Sign In or Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a0a0a;
            --secondary: #ff3366;
            --accent: #00d4aa;
            --bg: #fafafa;
            --text: #1a1a1a;
            --text-light: #666;
            --border: #e0e0e0;
            --success: #00d4aa;
            --error: #ff3366;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background shapes */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 51, 102, 0.1) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0, 212, 170, 0.1) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 50px) scale(1.1); }
        }

        .container {
            position: relative;
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 24px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .logo p {
            color: var(--text-light);
            font-size: 14px;
        }

        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            background: var(--bg);
            padding: 6px;
            border-radius: 12px;
        }

        .tab {
            flex: 1;
            padding: 12px;
            background: transparent;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tab.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .form-container {
            position: relative;
            overflow: hidden;
        }

        .form {
            display: none;
            animation: fadeInForm 0.4s ease-out;
        }

        .form.active {
            display: block;
        }

        @keyframes fadeInForm {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            color: var(--text);
            transition: all 0.3s ease;
            background: white;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(0, 212, 170, 0.1);
        }

        input.error {
            border-color: var(--error);
        }

        input.error:focus {
            box-shadow: 0 0 0 4px rgba(255, 51, 102, 0.1);
        }

        .error-message {
            color: var(--error);
            font-size: 13px;
            margin-top: 6px;
            display: none;
        }

        .error-message.show {
            display: block;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--text);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent);
        }

        .checkbox-group label {
            margin: 0;
            font-size: 14px;
            font-weight: 400;
            color: var(--text-light);
            cursor: pointer;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 24px;
        }

        .forgot-password a {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-password a:hover {
            color: var(--secondary);
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #2a2a2a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 32px 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .social-login {
            display: flex;
            gap: 12px;
        }

        .social-btn {
            flex: 1;
            padding: 14px;
            background: white;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .social-btn:hover {
            border-color: var(--accent);
            background: rgba(0, 212, 170, 0.05);
        }

        .switch-form {
            text-align: center;
            margin-top: 24px;
            color: var(--text-light);
            font-size: 14px;
        }

        .switch-form a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .switch-form a:hover {
            color: var(--secondary);
        }

        .success-message, .general-error {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: none;
            animation: slideDown 0.4s ease;
        }

        .success-message {
            background: rgba(0, 212, 170, 0.1);
            border: 2px solid var(--success);
            color: var(--success);
        }

        .general-error {
            background: rgba(255, 51, 102, 0.1);
            border: 2px solid var(--error);
            color: var(--error);
        }

        .success-message.show, .general-error.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .container {
                padding: 40px 24px;
            }

            .logo h1 {
                font-size: 28px;
            }

            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Welcome Back</h1>
            <p>Access your account securely</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('login')">Sign In</button>
            <button class="tab" onclick="switchTab('register')">Sign Up</button>
        </div>

        <div class="form-container">
            <!-- Login Form -->
            <form id="loginForm" class="form active" onsubmit="handleLogin(event)">
                <div class="success-message" id="loginSuccess">
                    ✓ Successfully logged in!
                </div>
                <div class="general-error" id="loginError"></div>

                <div class="form-group">
                    <label for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" placeholder="your@email.com" required>
                    <div class="error-message" id="loginEmailError">Please enter a valid email</div>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="loginPassword" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword')">
                            Show
                        </button>
                    </div>
                    <div class="error-message" id="loginPasswordError">Password is required</div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="rememberMe">
                    <label for="rememberMe">Remember me for 30 days</label>
                </div>

                <div class="forgot-password">
                    <a href="#" onclick="showForgotPassword(event)">Forgot password?</a>
                </div>

                <button type="submit" class="submit-btn" id="loginBtn">Sign In</button>

                <div class="divider">or continue with</div>

                <div class="social-login">
                    <button type="button" class="social-btn" onclick="socialLogin('google')">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M17.64 9.2c0-.64-.06-1.26-.16-1.84H9v3.48h4.84c-.21 1.12-.84 2.08-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z" fill="#4285F4"/>
                            <path d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.82.86-3.04.86-2.34 0-4.32-1.58-5.02-3.7H.98v2.33C2.46 15.98 5.48 18 9 18z" fill="#34A853"/>
                            <path d="M3.98 10.72c-.18-.54-.28-1.12-.28-1.72s.1-1.18.28-1.72V4.95H.98C.36 6.19 0 7.56 0 9s.36 2.81.98 4.05l3-2.33z" fill="#FBBC05"/>
                            <path d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0 5.48 0 2.46 2.02.98 4.95l3 2.33c.7-2.12 2.68-3.7 5.02-3.7z" fill="#EA4335"/>
                        </svg>
                        Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialLogin('apple')">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                            <path d="M14.94 13.17c-.31.67-.67 1.29-1.09 1.86-.58.78-1.06 1.32-1.43 1.62-.58.53-1.2.8-1.86.82-.48 0-1.05-.14-1.72-.41-.67-.28-1.29-.41-1.86-.41-.6 0-1.24.14-1.93.41-.69.28-1.24.43-1.67.45-.64.04-1.28-.24-1.9-.85-.41-.32-.91-.88-1.52-1.68C.32 13.89 0 12.67 0 11.51c0-1.29.28-2.4.84-3.33.7-1.16 1.63-1.74 2.79-1.76.55 0 1.26.17 2.15.5.89.34 1.46.51 1.71.51.19 0 .83-.2 1.92-.6 1.03-.38 1.9-.53 2.61-.47 1.93.16 3.38.93 4.35 2.32-1.73 1.05-2.58 2.52-2.56 4.41.02 1.47.55 2.7 1.59 3.67.47.45 1 .8 1.59 1.05-.13.38-.27.74-.41 1.08zM11.87 0c0 1.15-.42 2.23-1.26 3.22-.1.12-.22.23-.34.33-.93.85-2.04 1.26-3.25 1.18-.02-.15-.02-.31-.02-.48 0-1.1.48-2.29 1.34-3.25.43-.49.97-.9 1.63-1.23.66-.32 1.28-.5 1.87-.52.02.17.03.33.03.5z"/>
                        </svg>
                        Apple
                    </button>
                </div>
            </form>

            <!-- Registration Form -->
            <form id="registerForm" class="form" onsubmit="handleRegister(event)">
                <div class="success-message" id="registerSuccess">
                    ✓ Account created successfully!
                </div>
                <div class="general-error" id="registerError"></div>

                <div class="form-group">
                    <label for="registerName">Full Name</label>
                    <input type="text" id="registerName" placeholder="John Doe" required>
                    <div class="error-message" id="registerNameError">Please enter your name</div>
                </div>

                <div class="form-group">
                    <label for="registerEmail">Email Address</label>
                    <input type="email" id="registerEmail" placeholder="your@email.com" required>
                    <div class="error-message" id="registerEmailError">Please enter a valid email</div>
                </div>

                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="registerPassword" placeholder="Create a strong password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('registerPassword')">
                            Show
                        </button>
                    </div>
                    <div class="error-message" id="registerPasswordError">Password must be at least 8 characters</div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="confirmPassword" placeholder="Re-enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                            Show
                        </button>
                    </div>
                    <div class="error-message" id="confirmPasswordError">Passwords do not match</div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="agreeTerms" required>
                    <label for="agreeTerms">I agree to the <a href="#" style="color: var(--accent);">Terms & Conditions</a></label>
                </div>

                <button type="submit" class="submit-btn" id="registerBtn">Create Account</button>

                <div class="divider">or sign up with</div>

                <div class="social-login">
                    <button type="button" class="social-btn" onclick="socialLogin('google')">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M17.64 9.2c0-.64-.06-1.26-.16-1.84H9v3.48h4.84c-.21 1.12-.84 2.08-1.8 2.72v2.26h2.92c1.7-1.57 2.68-3.88 2.68-6.62z" fill="#4285F4"/>
                            <path d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.82.86-3.04.86-2.34 0-4.32-1.58-5.02-3.7H.98v2.33C2.46 15.98 5.48 18 9 18z" fill="#34A853"/>
                            <path d="M3.98 10.72c-.18-.54-.28-1.12-.28-1.72s.1-1.18.28-1.72V4.95H.98C.36 6.19 0 7.56 0 9s.36 2.81.98 4.05l3-2.33z" fill="#FBBC05"/>
                            <path d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0 5.48 0 2.46 2.02.98 4.95l3 2.33c.7-2.12 2.68-3.7 5.02-3.7z" fill="#EA4335"/>
                        </svg>
                        Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialLogin('apple')">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">
                            <path d="M14.94 13.17c-.31.67-.67 1.29-1.09 1.86-.58.78-1.06 1.32-1.43 1.62-.58.53-1.2.8-1.86.82-.48 0-1.05-.14-1.72-.41-.67-.28-1.29-.41-1.86-.41-.6 0-1.24.14-1.93.41-.69.28-1.24.43-1.67.45-.64.04-1.28-.24-1.9-.85-.41-.32-.91-.88-1.52-1.68C.32 13.89 0 12.67 0 11.51c0-1.29.28-2.4.84-3.33.7-1.16 1.63-1.74 2.79-1.76.55 0 1.26.17 2.15.5.89.34 1.46.51 1.71.51.19 0 .83-.2 1.92-.6 1.03-.38 1.9-.53 2.61-.47 1.93.16 3.38.93 4.35 2.32-1.73 1.05-2.58 2.52-2.56 4.41.02 1.47.55 2.7 1.59 3.67.47.45 1 .8 1.59 1.05-.13.38-.27.74-.41 1.08zM11.87 0c0 1.15-.42 2.23-1.26 3.22-.1.12-.22.23-.34.33-.93.85-2.04 1.26-3.25 1.18-.02-.15-.02-.31-.02-.48 0-1.1.48-2.29 1.34-3.25.43-.49.97-.9 1.63-1.23.66-.32 1.28-.5 1.87-.52.02.17.03.33.03.5z"/>
                        </svg>
                        Apple
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(tab) {
            const tabs = document.querySelectorAll('.tab');
            const forms = document.querySelectorAll('.form');
            
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(f => f.classList.remove('active'));
            
            // Hide all messages
            document.querySelectorAll('.success-message, .general-error').forEach(el => el.classList.remove('show'));
            
            if (tab === 'login') {
                tabs[0].classList.add('active');
                document.getElementById('loginForm').classList.add('active');
            } else {
                tabs[1].classList.add('active');
                document.getElementById('registerForm').classList.add('active');
            }
        }

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Hide';
            } else {
                input.type = 'password';
                button.textContent = 'Show';
            }
        }

        // Show error message
        function showError(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = message;
                element.classList.add('show');
            }
        }

        // Hide all error messages
        function hideAllErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('input').forEach(el => el.classList.remove('error'));
            document.querySelectorAll('.general-error').forEach(el => el.classList.remove('show'));
        }

        // Handle login
        async function handleLogin(event) {
            event.preventDefault();
            hideAllErrors();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const rememberMe = document.getElementById('rememberMe').checked;
            const btn = document.getElementById('loginBtn');
            const originalText = btn.innerHTML;
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Signing in...';
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        rememberMe: rememberMe
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('loginSuccess').classList.add('show');
                    setTimeout(() => {
                        // Redirect to dashboard or home page
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showError('loginError', data.message);
                }
            } catch (error) {
                showError('loginError', 'An error occurred. Please try again.');
                console.error('Error:', error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Handle registration
        async function handleRegister(event) {
            event.preventDefault();
            hideAllErrors();
            
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const btn = document.getElementById('registerBtn');
            const originalText = btn.innerHTML;
            
            // Client-side validation
            let isValid = true;
            
            if (password !== confirmPassword) {
                document.getElementById('confirmPassword').classList.add('error');
                showError('confirmPasswordError', 'Passwords do not match');
                isValid = false;
            }
            
            if (password.length < 8) {
                document.getElementById('registerPassword').classList.add('error');
                showError('registerPasswordError', 'Password must be at least 8 characters');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="loading"></span> Creating account...';
            
            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name,
                        email: email,
                        password: password,
                        confirmPassword: confirmPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('registerSuccess').classList.add('show');
                    setTimeout(() => {
                        // Redirect to dashboard or home page
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showError('registerError', data.message);
                }
            } catch (error) {
                showError('registerError', 'An error occurred. Please try again.');
                console.error('Error:', error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Forgot password
        function showForgotPassword(event) {
            event.preventDefault();
            alert('Password reset link would be sent to your email. (Demo only)');
        }

        // Social login
        function socialLogin(provider) {
            alert(`Signing in with ${provider}... (Demo only)`);
        }

        // Add focus animations
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Check session on page load
        window.addEventListener('load', async function() {
            try {
                const response = await fetch('check-session.php');
                const data = await response.json();
                
                if (data.logged_in) {
                    // User is already logged in, redirect to dashboard
                    window.location.href = 'dashboard.php';
                }
            } catch (error) {
                console.error('Error checking session:', error);
            }
        });
    </script>
</body>
</html>
