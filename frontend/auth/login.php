<?php
session_start();
// require_once '../config/database.php';
// require_once '../includes/jwt.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Regular login
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        
        // TODO: Database connection and validation
        // This is a placeholder - implement your database logic
        
        // Example validation (replace with actual database check)
        if (empty($email) || empty($password)) {
            $error_message = 'Please fill in all fields';
        } else {
            // TODO: Verify credentials against database
            // If user has 2FA enabled, redirect to 2FA verification
            // $_SESSION['temp_user_id'] = $user_id;
            // header('Location: login.php?step=2fa');
            
            // For demo purposes:
            $error_message = 'Invalid email or password';
        }
    } elseif (isset($_POST['verify_2fa'])) {
        // 2FA verification
        $code = $_POST['code'];
        
        // TODO: Verify 2FA code
        if (empty($code)) {
            $error_message = 'Please enter verification code';
        } else {
            // TODO: Verify code and log user in
            // $_SESSION['user_id'] = $_SESSION['temp_user_id'];
            // unset($_SESSION['temp_user_id']);
            // header('Location: dashboard.php');
            
            $error_message = 'Invalid verification code';
        }
    }
}

// Check if we're on 2FA step
$show_2fa = isset($_GET['step']) && $_GET['step'] === '2fa';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gurkha Marga</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --gold: #fbbf24;
            --success: #10b981;
            --error: #ef4444;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-hero);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.3;
            animation: float 20s infinite ease-in-out;
        }

        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--accent);
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 300px;
            height: 300px;
            background: #8b5cf6;
            top: 50%;
            right: -150px;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 500px;
            height: 500px;
            background: #ec4899;
            bottom: -250px;
            left: 30%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-lg);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .brand-name {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold) 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.95rem;
            color: var(--text-primary);
        }

        .form-label .required {
            color: var(--error);
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(15, 23, 42, 0.7);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--text-secondary);
            user-select: none;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        /* 2FA Code Input */
        .code-inputs {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            background: rgba(15, 23, 42, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(15, 23, 42, 0.7);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .custom-checkbox {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
        }

        .custom-checkbox input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background: rgba(15, 23, 42, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-checkbox input:checked ~ .checkmark {
            background: var(--accent);
            border-color: var(--accent);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 5px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            cursor: pointer;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-google {
            background: white;
            color: #1f2937;
            border: 2px solid rgba(255, 255, 255, 0.1);
            margin-top: 1rem;
        }

        .btn-google:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 2px solid rgba(255, 255, 255, 0.1);
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .divider span {
            padding: 0 1rem;
        }

        /* Links */
        .form-links {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .form-link {
            color: var(--accent);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .form-link:hover {
            color: var(--gold);
        }

        .text-center {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .text-center a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .text-center a:hover {
            color: var(--gold);
        }

        /* Back to Home Link */
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a:hover {
            color: var(--text-primary);
        }

        /* 2FA Info */
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Resend Code */
        .resend-code {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .resend-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .resend-link:hover {
            color: var(--gold);
        }

        .resend-link.disabled {
            color: var(--text-secondary);
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }

            .code-inputs {
                gap: 8px;
            }

            .code-input {
                width: 45px;
                height: 55px;
                font-size: 1.3rem;
            }
        }

        /* Loading Spinner */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="18" fill="#2c5f2d" stroke="#fbbf24" stroke-width="2"/>
                        <path d="M20 8L24 16H16L20 8Z" fill="#fbbf24"/>
                        <rect x="18" y="16" width="4" height="12" fill="#fbbf24"/>
                        <path d="M12 28L20 24L28 28" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span class="brand-name">Gurkha Marga</span>
                </div>
            </div>

            <?php if (!$show_2fa): ?>
            <!-- Regular Login Form -->
            <div class="login-header">
                <h1>Welcome Back!</h1>
                <p>Login to continue your training journey</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <span>✓</span>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="email">
                        Email Address <span class="required">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Enter your email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        Password <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            required
                        >
                        <span class="password-toggle" onclick="togglePassword()">👁️</span>
                    </div>
                </div>

                <div class="form-links">
                    <div class="checkbox-group">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkmark"></span>
                        </label>
                        <label class="checkbox-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="form-link">Forgot Password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-primary">
                    <span>Login</span>
                    <span>→</span>
                </button>

                <div class="divider">
                    <span>OR</span>
                </div>

                <button type="button" class="btn btn-google" onclick="loginWithGoogle()">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M19.6 10.2273C19.6 9.51825 19.5364 8.83643 19.4182 8.18188H10V12.0501H15.3818C15.15 13.3001 14.4455 14.3592 13.3864 15.0682V17.5773H16.6182C18.5091 15.8364 19.6 13.2728 19.6 10.2273Z" fill="#4285F4"/>
                        <path d="M10 20C12.7 20 14.9636 19.1045 16.6182 17.5773L13.3864 15.0682C12.4909 15.6682 11.3455 16.0227 10 16.0227C7.39545 16.0227 5.19091 14.2636 4.40455 11.9H1.06364V14.4909C2.70909 17.7591 6.09091 20 10 20Z" fill="#34A853"/>
                        <path d="M4.40455 11.9C4.20455 11.3 4.09091 10.6591 4.09091 10C4.09091 9.34091 4.20455 8.7 4.40455 8.1V5.50909H1.06364C0.386364 6.85909 0 8.38636 0 10C0 11.6136 0.386364 13.1409 1.06364 14.4909L4.40455 11.9Z" fill="#FBBC04"/>
                        <path d="M10 3.97727C11.4682 3.97727 12.7864 4.48182 13.8227 5.47273L16.6909 2.60455C14.9591 0.990909 12.6955 0 10 0C6.09091 0 2.70909 2.24091 1.06364 5.50909L4.40455 8.1C5.19091 5.73636 7.39545 3.97727 10 3.97727Z" fill="#EA4335"/>
                    </svg>
                    <span>Continue with Google</span>
                </button>
            </form>

            <div class="text-center">
                Don't have an account? <a href="register.php">Sign up</a>
            </div>

            <?php else: ?>
            <!-- 2FA Verification Form -->
            <div class="login-header">
                <h1>Two-Factor Authentication</h1>
                <p>Enter the 6-digit code from your authenticator app</p>
            </div>

            <div class="info-box">
                <span class="info-icon">🔒</span>
                <div>
                    <strong>Security Check</strong><br>
                    We've sent a verification code to your registered device. Please enter it below to continue.
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php?step=2fa" id="twoFactorForm">
                <div class="code-inputs">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="code1" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="code2" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="code3" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="code4" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="code5" autocomplete="off">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" inputmode="numeric" id="code6" autocomplete="off">
                </div>
                <input type="hidden" name="code" id="fullCode">

                <button type="submit" name="verify_2fa" class="btn btn-primary">
                    <span>Verify Code</span>
                    <span>✓</span>
                </button>

                <button type="button" class="btn btn-secondary" onclick="window.location.href='login.php'">
                    <span>← Back to Login</span>
                </button>

                <div class="resend-code">
                    Didn't receive code? <a href="#" class="resend-link" id="resendLink" onclick="resendCode(event)">Resend Code</a>
                    <span id="timer" style="display: none;"></span>
                </div>
            </form>
            <?php endif; ?>

            <div class="back-link">
                <a href="index.php">
                    <span>←</span>
                    <span>Back to Home</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggle.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggle.textContent = '👁️';
            }
        }

        // Google Login (placeholder)
        function loginWithGoogle() {
            // TODO: Implement Google OAuth
            alert('Google login will be implemented with OAuth 2.0');
            // In production, redirect to Google OAuth endpoint:
            // window.location.href = 'google-oauth.php';
        }

        // 2FA Code Input Handler
        <?php if ($show_2fa): ?>
        const codeInputs = document.querySelectorAll('.code-input');
        const fullCodeInput = document.getElementById('fullCode');

        codeInputs.forEach((input, index) => {
            // Auto-focus next input
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1) {
                    if (index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus();
                    }
                }
                updateFullCode();
            });

            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '') {
                    if (index > 0) {
                        codeInputs[index - 1].focus();
                    }
                }
            });

            // Only allow numbers
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/\D/g, '');
                const chars = pasteData.split('').slice(0, 6);
                
                chars.forEach((char, i) => {
                    if (codeInputs[i]) {
                        codeInputs[i].value = char;
                    }
                });
                
                if (chars.length > 0) {
                    const lastIndex = Math.min(chars.length, 6) - 1;
                    codeInputs[lastIndex].focus();
                }
                
                updateFullCode();
            });
        });

        function updateFullCode() {
            let code = '';
            codeInputs.forEach(input => {
                code += input.value;
            });
            fullCodeInput.value = code;
        }

        // Auto-submit when all digits entered
        codeInputs[5].addEventListener('input', () => {
            updateFullCode();
            if (fullCodeInput.value.length === 6) {
                // Optional: Auto-submit
                // document.getElementById('twoFactorForm').submit();
            }
        });

        // Resend Code with Timer
        let resendTimer;
        let resendSeconds = 60;

        function resendCode(e) {
            e.preventDefault();
            const resendLink = document.getElementById('resendLink');
            const timer = document.getElementById('timer');
            
            if (resendLink.classList.contains('disabled')) {
                return;
            }
            
            // TODO: Implement actual resend logic
            // Send AJAX request to resend code
            fetch('resend-2fa-code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'resend_2fa'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Code resent successfully!', 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to resend code', 'error');
            });
            
            // Disable resend link
            resendLink.classList.add('disabled');
            resendLink.style.pointerEvents = 'none';
            timer.style.display = 'inline';
            
            resendSeconds = 60;
            updateTimer();
            
            resendTimer = setInterval(() => {
                resendSeconds--;
                updateTimer();
                
                if (resendSeconds <= 0) {
                    clearInterval(resendTimer);
                    resendLink.classList.remove('disabled');
                    resendLink.style.pointerEvents = 'auto';
                    timer.style.display = 'none';
                }
            }, 1000);
        }

        function updateTimer() {
            const timer = document.getElementById('timer');
            timer.textContent = `(${resendSeconds}s)`;
        }

        // Focus first input on load
        if (codeInputs.length > 0) {
            codeInputs[0].focus();
        }

        // Add enter key support for 2FA inputs
        codeInputs.forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && fullCodeInput.value.length === 6) {
                    e.preventDefault();
                    document.getElementById('twoFactorForm').submit();
                }
            });
        });
        <?php endif; ?>

        // Form validation
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    showNotification('Please fill in all required fields', 'error');
                    return false;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    showNotification('Please enter a valid email address', 'error');
                    return false;
                }
                
                // Show loading state
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                const originalContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner"></div><span>Logging in...</span>';
                
                // Reset button after 3 seconds if form doesn't submit
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }, 3000);
            });
        }

        // 2FA Form validation
        const twoFactorForm = document.getElementById('twoFactorForm');
        if (twoFactorForm) {
            twoFactorForm.addEventListener('submit', function(e) {
                const code = document.getElementById('fullCode').value;
                
                if (code.length !== 6) {
                    e.preventDefault();
                    showNotification('Please enter the complete 6-digit code', 'error');
                    return false;
                }
                
                // Show loading state
                const submitBtn = twoFactorForm.querySelector('button[type="submit"]');
                const originalContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner"></div><span>Verifying...</span>';
                
                // Reset button after 3 seconds if form doesn't submit
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalContent;
                }, 3000);
            });
        }

        // Notification system
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${type === 'error' ? 'rgba(239, 68, 68, 0.95)' : 'rgba(16, 185, 129, 0.95)'};
                color: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(10px);
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
                max-width: 350px;
                font-size: 0.95rem;
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            
            const icon = type === 'error' ? '⚠️' : '✓';
            notification.innerHTML = `<span style="font-size: 1.2rem;">${icon}</span><span>${message}</span>`;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Prevent multiple form submissions
        let formSubmitted = false;
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                if (formSubmitted) {
                    return false;
                }
                formSubmitted = true;
                setTimeout(() => {
                    formSubmitted = false;
                }, 3000);
            });
        });

        // Auto-clear error messages on input
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(() => alert.remove(), 300);
                });
            });
        });

        // Session timeout warning (optional)
        let sessionTimeout;
        function resetSessionTimeout() {
            clearTimeout(sessionTimeout);
            // Warn after 25 minutes of inactivity
            sessionTimeout = setTimeout(() => {
                showNotification('Your session will expire soon due to inactivity', 'error');
            }, 25 * 60 * 1000);
        }

        // Reset timeout on user activity
        ['mousedown', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetSessionTimeout, true);
        });
        
        resetSessionTimeout();

        // Detect caps lock
        document.querySelectorAll('input[type="password"]').forEach(input => {
            input.addEventListener('keyup', function(e) {
                const capsLockOn = e.getModifierState && e.getModifierState('CapsLock');
                let warning = input.parentElement.querySelector('.caps-warning');
                
                if (capsLockOn) {
                    if (!warning) {
                        warning = document.createElement('div');
                        warning.className = 'caps-warning';
                        warning.style.cssText = `
                            color: #fbbf24;
                            font-size: 0.85rem;
                            margin-top: 0.5rem;
                            display: flex;
                            align-items: center;
                            gap: 6px;
                        `;
                        warning.innerHTML = '<span>⚠️</span><span>Caps Lock is on</span>';
                        input.parentElement.appendChild(warning);
                    }
                } else {
                    if (warning) {
                        warning.remove();
                    }
                }
            });
        });

        // Remember me functionality
        const rememberCheckbox = document.getElementById('remember');
        const emailInput = document.getElementById('email');
        
        if (rememberCheckbox && emailInput) {
            // Load saved email
            const savedEmail = localStorage.getItem('rememberedEmail');
            if (savedEmail) {
                emailInput.value = savedEmail;
                rememberCheckbox.checked = true;
            }
            
            // Save/clear email on form submit
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    if (rememberCheckbox.checked) {
                        localStorage.setItem('rememberedEmail', emailInput.value);
                    } else {
                        localStorage.removeItem('rememberedEmail');
                    }
                });
            }
        }

        // Accessibility: Announce errors to screen readers
        function announceError(message) {
            const announcement = document.createElement('div');
            announcement.setAttribute('role', 'alert');
            announcement.setAttribute('aria-live', 'assertive');
            announcement.className = 'sr-only';
            announcement.textContent = message;
            announcement.style.cssText = `
                position: absolute;
                left: -10000px;
                width: 1px;
                height: 1px;
                overflow: hidden;
            `;
            document.body.appendChild(announcement);
            setTimeout(() => announcement.remove(), 3000);
        }

        // Log any JavaScript errors for debugging
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.message, 'at', e.filename, 'line', e.lineno);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>