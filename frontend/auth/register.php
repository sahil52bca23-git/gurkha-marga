<?php
session_start();
// require_once '../config/database.php';
// require_once '../includes/jwt.php';
ob_start(); // Add output buffering to prevent header issues

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';
$success_message = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // STEP 1 SUBMISSION
    if (isset($_POST['step1'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($full_name)) {
            $error_message = 'Full name is required';
        } elseif (empty($email)) {
            $error_message = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Invalid email format';
        } elseif (empty($password)) {
            $error_message = 'Password is required';
        } elseif (strlen($password) < 8) {
            $error_message = 'Password must be at least 8 characters';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Passwords do not match';
        } else {
            // SUCCESS - Store data and redirect
            $_SESSION['registration_step1'] = [
                'full_name' => $full_name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'timestamp' => time()
            ];
            
            // Force redirect
            ob_end_clean();
            header('Location: register.php?step=2', true, 302);
            die();
        }
    }
    
    // STEP 2 SUBMISSION
    elseif (isset($_POST['step2'])) {
        $age = (int)($_POST['age'] ?? 0);
        $gender = trim($_POST['gender'] ?? '');
        $height = (float)($_POST['height'] ?? 0);
        $weight = (float)($_POST['weight'] ?? 0);
        $fitness_goal = trim($_POST['fitness_goal'] ?? '');
        $experience_level = trim($_POST['experience_level'] ?? '');
        
        // Validate
        if ($age < 13 || $age > 120) {
            $error_message = 'Invalid age (must be 13-120)';
        } elseif (empty($gender)) {
            $error_message = 'Gender is required';
        } elseif ($height < 100 || $height > 250) {
            $error_message = 'Invalid height (must be 100-250 cm)';
        } elseif ($weight < 30 || $weight > 300) {
            $error_message = 'Invalid weight (must be 30-300 kg)';
        } elseif (empty($fitness_goal)) {
            $error_message = 'Fitness goal is required';
        } elseif (empty($experience_level)) {
            $error_message = 'Experience level is required';
        } elseif (!isset($_SESSION['registration_step1'])) {
            $error_message = 'Session expired. Please start over.';
            $step = 1;
        } else {
            // SUCCESS - Combine data
            $step1_data = $_SESSION['registration_step1'];
            $complete_data = [
                'full_name' => $step1_data['full_name'],
                'email' => $step1_data['email'],
                'password' => $step1_data['password'],
                'age' => $age,
                'gender' => $gender,
                'height' => $height,
                'weight' => $weight,
                'fitness_goal' => $fitness_goal,
                'experience_level' => $experience_level
            ];
            
            // TODO: INSERT INTO DATABASE HERE
            /*
            require_once 'config/database.php';
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, age, gender, height, weight, fitness_goal, experience_level, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $complete_data['full_name'],
                $complete_data['email'],
                $complete_data['password'],
                $complete_data['age'],
                $complete_data['gender'],
                $complete_data['height'],
                $complete_data['weight'],
                $complete_data['fitness_goal'],
                $complete_data['experience_level']
            ]);
            */
            
            // Clear registration data
            unset($_SESSION['registration_step1']);
            
            // Set success message
            $_SESSION['registration_success'] = 'Registration successful! Please login.';
            
            // Redirect to login
            ob_end_clean();
            header('Location: login.php', true, 302);
            die();
        }
    }
}

// Force redirect to step 1 if on step 2 without completing step 1
if ($step === 2 && !isset($_SESSION['registration_step1'])) {
    ob_end_clean();
    header('Location: register.php?step=1', true, 302);
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gurkha Marga</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --gold: #fbbf24;
            --success: #10b981;
            --error: #ef4444;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-name {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold) 0%, #f59e0b 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-secondary);
        }

        .progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .progress::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 0;
            height: 2px;
            background: var(--accent);
            width: <?php echo $step === 2 ? '100%' : '0%'; ?>;
            transition: width 0.5s;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .step.active .step-circle {
            background: var(--accent);
            border-color: var(--accent);
        }

        .step.completed .step-circle {
            background: var(--success);
            border-color: var(--success);
        }

        .step-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-unit {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .form-input.with-unit {
            padding-right: 50px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: transform 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .text-center {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
        }

        .text-center a {
            color: var(--accent);
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .container {
                padding: 2rem 1.5rem;
            }
            .form-row, .btn-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="brand-name">Gurkha Marga</div>
        </div>

        <div class="progress">
            <div class="progress-line"></div>
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <div class="step-circle"><?php echo $step > 1 ? '✓' : '1'; ?></div>
                <div class="step-label">Account</div>
            </div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                <div class="step-circle">2</div>
                <div class="step-label">Profile</div>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="alert">⚠️ <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
        <!-- STEP 1 -->
        <div class="header">
            <h1>Create Account</h1>
            <p>Start your fitness journey</p>
        </div>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-input" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" placeholder="Minimum 8 characters" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter password" required>
            </div>

            <button type="submit" name="step1" class="btn btn-primary">Continue →</button>
        </form>

        <div class="text-center">
            Already have an account? <a href="login.php">Login</a>
        </div>

        <?php else: ?>
        <!-- STEP 2 -->
        <div class="header">
            <h1>Personal Information</h1>
            <p>Complete your profile</p>
        </div>

        <form method="POST" action="register.php?step=2">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Age *</label>
                    <input type="number" name="age" class="form-input" placeholder="Your age" min="13" max="120" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender *</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Height *</label>
                    <div class="input-wrapper">
                        <input type="number" name="height" class="form-input with-unit" placeholder="Height" step="0.1" min="100" max="250" required>
                        <span class="input-unit">cm</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Weight *</label>
                    <div class="input-wrapper">
                        <input type="number" name="weight" class="form-input with-unit" placeholder="Weight" step="0.1" min="30" max="300" required>
                        <span class="input-unit">kg</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Fitness Goal *</label>
                <select name="fitness_goal" class="form-select" required>
                    <option value="">Select your goal</option>
                    <option value="weight_loss">Weight Loss</option>
                    <option value="muscle_gain">Muscle Gain</option>
                    <option value="endurance">Build Endurance</option>
                    <option value="strength">Increase Strength</option>
                    <option value="flexibility">Improve Flexibility</option>
                    <option value="general_fitness">General Fitness</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Experience Level *</label>
                <select name="experience_level" class="form-select" required>
                    <option value="">Select your level</option>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                    <option value="expert">Expert</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='register.php?step=1'">← Back</button>
                <button type="submit" name="step2" class="btn btn-primary">Complete ✓</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>