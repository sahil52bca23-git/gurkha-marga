<!-- <?php
session_start();
require_once '../config/database.php';
require_once '../includes/jwt.php';

// Check authentication
$token = JWT::getTokenFromCookie();
if (!$token) {
    header('Location: login.php');
    exit();
}

$user = JWT::getUserFromToken($token);
if (!$user) {
    JWT::clearTokenCookie();
    header('Location: login.php');
    exit();
}

// Fetch user's complete profile
$pdo = getDB();
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = query($sql, [$user['id']]);
$userProfile = $stmt->fetch();

// Calculate BMI if height and weight exist
$bmi = null;
$bmiCategory = '';
if (!empty($userProfile['height']) && !empty($userProfile['weight'])) {
    $heightInMeters = $userProfile['height'] / 100;
    $bmi = round($userProfile['weight'] / ($heightInMeters * $heightInMeters), 1);
    
    if ($bmi < 18.5) {
        $bmiCategory = 'Underweight';
    } elseif ($bmi < 25) {
        $bmiCategory = 'Normal';
    } elseif ($bmi < 30) {
        $bmiCategory = 'Overweight';
    } else {
        $bmiCategory = 'Obese';
    }
}

// Get recent login logs
$sql = "SELECT * FROM login_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = query($sql, [$user['id']]);
$recentLogins = $stmt->fetchAll();

// Mock data for workout stats (replace with real database queries)
$workoutStats = [
    'total_workouts' => 24,
    'this_week' => 5,
    'this_month' => 18,
    'calories_burned' => 3420,
    'active_minutes' => 1250,
    'streak_days' => 7
];

$upcomingWorkouts = [
    ['name' => 'Upper Body Strength', 'date' => 'Today', 'time' => '6:00 PM', 'duration' => '45 min'],
    ['name' => 'Cardio HIIT', 'date' => 'Tomorrow', 'time' => '7:00 AM', 'duration' => '30 min'],
    ['name' => 'Leg Day', 'date' => 'Jan 06', 'time' => '6:00 PM', 'duration' => '60 min']
];

// Recent achievements
$recentAchievements = [
    ['title' => '7 Day Streak', 'icon' => '🔥', 'date' => 'Today'],
    ['title' => '50 Workouts', 'icon' => '🏆', 'date' => '2 days ago'],
    ['title' => 'Early Bird', 'icon' => '🌅', 'date' => '5 days ago']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gurkha Marga</title>
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
            --accent-hover: #2563eb;
            --gold: #fbbf24;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --sidebar-bg: rgba(15, 23, 42, 0.95);
            --card-bg: rgba(30, 41, 59, 0.8);
            --hover-bg: rgba(59, 130, 246, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold) 0%, #f59e0b 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 3px solid rgba(59, 130, 246, 0.3);
        }

        .user-details h3 {
            font-size: 1rem;
            font-weight: 600;
        }

        .user-details p {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .nav-menu {
            padding: 2rem 0;
        }

        .nav-item {
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--hover-bg);
            color: var(--text-primary);
            border-left: 3px solid var(--accent);
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .topbar {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .topbar h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .topbar-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.blue {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent);
        }

        .stat-icon.green {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .stat-icon.yellow {
            background: rgba(251, 191, 36, 0.2);
            color: var(--gold);
        }

        .stat-icon.red {
            background: rgba(239, 68, 68, 0.2);
            color: var(--error);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-action {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .workout-item {
            padding: 1rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .workout-item:hover {
            background: rgba(15, 23, 42, 0.7);
            transform: translateX(5px);
        }

        .workout-info h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .workout-details {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .workout-details span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .bmi-display {
            text-align: center;
            padding: 2rem 1rem;
        }

        .bmi-value {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .bmi-category {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .bmi-category.normal {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .bmi-category.underweight {
            background: rgba(59, 130, 246, 0.2);
            color: var(--accent);
        }

        .bmi-category.overweight {
            background: rgba(251, 191, 36, 0.2);
            color: var(--warning);
        }

        .bmi-category.obese {
            background: rgba(239, 68, 68, 0.2);
            color: var(--error);
        }

        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), #8b5cf6);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .login-item {
            padding: 1rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .login-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--success);
        }

        .login-status.failed {
            background: var(--error);
        }

        .login-details span {
            display: block;
            font-size: 0.85rem;
        }

        .login-details .time {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .achievement-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .achievement-item:hover {
            background: rgba(15, 23, 42, 0.7);
            transform: scale(1.02);
        }

        .achievement-icon {
            font-size: 2rem;
        }

        .achievement-info h4 {
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .achievement-date {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge.success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }

        .empty-state svg {
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            z-index: 999;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                gap: 1rem;
            }

            .topbar-actions {
                width: 100%;
                justify-content: space-between;
            }

            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="18" fill="#2c5f2d" stroke="#fbbf24" stroke-width="2"/>
                    <path d="M20 8L24 16H16L20 8Z" fill="#fbbf24"/>
                    <rect x="18" y="16" width="4" height="12" fill="#fbbf24"/>
                    <path d="M12 28L20 24L28 28" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="brand-name">Gurkha Marga</span>
            </div>
            <div class="user-info">
                <div class="avatar"><?php echo strtoupper(substr($userProfile['full_name'], 0, 1)); ?></div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($userProfile['full_name']); ?></h3>
                    <p><?php echo htmlspecialchars($userProfile['email']); ?></p>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="workouts.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Workouts
            </a>
            <a href="nutrition.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Nutrition
            </a>
            <a href="progress.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Progress
            </a>
            <a href="profile.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>
            <a href="settings.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
            <a href="logout.php" class="nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Welcome back, <?php echo explode(' ', $userProfile['full_name'])[0]; ?>! 👋</h1>
                <p style="color: var(--text-secondary); margin-top: 0.5rem;">
                    <?php echo date('l, F j, Y'); ?>
                </p>
            </div>
            <div class="topbar-actions">
                <button class="btn btn-outline">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                </button>
                <a href="workouts.php" class="btn btn-primary">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Start Workout
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $workoutStats['total_workouts']; ?></div>
                        <div class="stat-label">Total Workouts</div>
                    </div>
                    <div class="stat-icon blue">💪</div>
                </div>
                <div class="stat-change positive">
                    <span>↑</span> 12% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($workoutStats['calories_burned']); ?></div>
                        <div class="stat-label">Calories Burned</div>
                    </div>
                    <div class="stat-icon red">🔥</div>
                </div>
                <div class="stat-change positive">
                    <span>↑</span> 8% from last week
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo number_format($workoutStats['active_minutes']); ?></div>
                        <div class="stat-label">Active Minutes</div>
                    </div>
                    <div class="stat-icon green">⏱️</div>
                </div>
                <div class="stat-change positive">
                    <span>↑</span> 15% from last week
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $workoutStats['streak_days']; ?> Days</div>
                        <div class="stat-label">Current Streak</div>
                    </div>
                    <div class="stat-icon yellow">⚡</div>
                </div>
                <div class="stat-change positive">
                    <span>↑</span> Keep it going!
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Upcoming Workouts</h2>
                    <a href="workouts.php" class="card-action">View All →</a>
                </div>
                
                <?php if (empty($upcomingWorkouts)): ?>
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M128v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>No upcoming workouts scheduled</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingWorkouts as $workout): ?>
                    <div class="workout-item">
                        <div class="workout-info">
                            <h4><?php echo htmlspecialchars($workout['name']); ?></h4>
                            <div class="workout-details">
                                <span>📅 <?php echo htmlspecialchars($workout['date']); ?></span>
                                <span>🕐 <?php echo htmlspecialchars($workout['time']); ?></span>
                                <span>⏱️ <?php echo htmlspecialchars($workout['duration']); ?></span>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-small">Start</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div style="margin-top: 1.5rem;">
                    <h3 style="font-size: 1rem; margin-bottom: 1rem;">Weekly Progress</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">
                            <?php echo $workoutStats['this_week']; ?> of 6 workouts completed
                        </span>
                        <span style="font-weight: 600;">83%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 83%;"></div>
                    </div>
                </div>
            </div>

            <div>
                <?php if ($bmi): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h2 class="card-title">Body Mass Index</h2>
                        <a href="profile.php" class="card-action">Update →</a>
                    </div>
                    <div class="bmi-display">
                        <div class="bmi-value"><?php echo $bmi; ?></div>
                        <div class="stat-label">BMI Score</div>
                        <div class="bmi-category <?php echo strtolower($bmiCategory); ?>">
                            <?php echo $bmiCategory; ?>
                        </div>
                        <div style="margin-top: 1.5rem; text-align: left;">
                            <p style="font-size: 0.85rem; color: var(--text-secondary);">
                                Height: <?php echo $userProfile['height']; ?> cm<br>
                                Weight: <?php echo $userProfile['weight']; ?> kg
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Achievements</h2>
                        <a href="progress.php" class="card-action">View All →</a>
                    </div>
                    
                    <?php if (empty($recentAchievements)): ?>
                        <div class="empty-state">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <p>Start working out to earn achievements!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentAchievements as $achievement): ?>
                        <div class="achievement-item">
                            <div class="achievement-icon"><?php echo $achievement['icon']; ?></div>
                            <div class="achievement-info">
                                <h4><?php echo htmlspecialchars($achievement['title']); ?></h4>
                                <div class="achievement-date"><?php echo htmlspecialchars($achievement['date']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Activity</h2>
                    <a href="progress.php" class="card-action">View All →</a>
                </div>
                
                <div class="workout-item">
                    <div class="workout-info">
                        <h4>💪 Completed Upper Body Workout</h4>
                        <div class="workout-details">
                            <span>📅 Today</span>
                            <span>⏱️ 45 minutes</span>
                            <span>🔥 520 cal</span>
                        </div>
                    </div>
                    <span class="badge success">Completed</span>
                </div>

                <div class="workout-item">
                    <div class="workout-info">
                        <h4>🏃 Morning Run</h4>
                        <div class="workout-details">
                            <span>📅 Yesterday</span>
                            <span>⏱️ 30 minutes</span>
                            <span>🔥 380 cal</span>
                        </div>
                    </div>
                    <span class="badge success">Completed</span>
                </div>

                <div class="workout-item">
                    <div class="workout-info">
                        <h4>🧘 Yoga Session</h4>
                        <div class="workout-details">
                            <span>📅 Jan 02</span>
                            <span>⏱️ 20 minutes</span>
                            <span>🔥 120 cal</span>
                        </div>
                    </div>
                    <span class="badge success">Completed</span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Login History</h2>
                </div>
                
                <?php if (empty($recentLogins)): ?>
                    <div class="empty-state">
                        <p>No login history available</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentLogins as $login): ?>
                    <div class="login-item">
                        <div class="login-info">
                            <div class="login-status <?php echo $login['status'] !== 'success' ? 'failed' : ''; ?>"></div>
                            <div class="login-details">
                                <span><?php echo htmlspecialchars($login['ip_address'] ?? 'Unknown'); ?></span>
                                <span class="time"><?php echo date('M j, Y g:i A', strtotime($login['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html> -->