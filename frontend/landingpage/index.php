<?php
// include "db.php";
session_start();

// Generate CSRF token for contact form
if (empty($_SESSION["csrf_token"])) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Display contact form messages
$contact_message = "";
$contact_message_type = "";
if (isset($_SESSION["contact_message"])) {
  $contact_message = $_SESSION["contact_message"];
  $contact_message_type = $_SESSION["contact_type"];
  unset($_SESSION["contact_message"]);
  unset($_SESSION["contact_type"]);
}

// Redirect to dashboard if already logged in
if (isset($_SESSION["user_id"])) {
  header("Location: dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gurkha Marga - Your Path to Army Recruitment</title>
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
            --danger: #ef4444;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--primary);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Sticky Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            box-shadow: var(--shadow-lg);
            background: rgba(15, 23, 42, 0.98);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-container img {
            height: 50px;
            width: auto;
            filter: drop-shadow(0 2px 8px rgba(251, 191, 36, 0.3));
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold) 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: width 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-primary);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }

        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: var(--text-primary);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* Buttons */
        .btn {
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .btn-outline-white {
            color: var(--text-primary);
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: transparent;
        }

        .btn-outline-white:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-primary);
        }

        .btn-primary-gradient {
            background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        /* Hero Section */
        .hero-modern {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-hero);
            z-index: 0;
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
        }

        .animated-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
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

        .shape-4 {
            width: 350px;
            height: 350px;
            background: #10b981;
            top: 20%;
            right: 20%;
            animation-delay: -15s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .hero-content-modern {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .badge-icon {
            font-size: 1.2rem;
        }

        .hero-title-modern {
            font-size: 4rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -2px;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--gold) 0%, #f59e0b 50%, #ef4444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle-modern {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }

        .hero-cta-modern {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 4rem;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
            color: white;
            padding: 16px 40px;
            font-size: 1.1rem;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 16px 40px;
            font-size: 1.1rem;
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
        }

        .hero-stats-modern {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .stat-item-modern {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .stat-item-modern:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon-wrapper {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gold);
            margin-bottom: 0.25rem;
        }

        .stat-content p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: var(--text-secondary);
            font-size: 0.85rem;
            z-index: 10;
        }

        .scroll-icon {
            width: 30px;
            height: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            position: relative;
        }

        .scroll-icon::after {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 10px;
            background: var(--text-secondary);
            border-radius: 3px;
            animation: scroll 2s infinite;
        }

        @keyframes scroll {
            0%, 100% { opacity: 0; transform: translateX(-50%) translateY(0); }
            50% { opacity: 1; transform: translateX(-50%) translateY(10px); }
        }

        /* Eligibility Checker Section */
        .eligibility-checker {
            background: var(--secondary);
            padding: 80px 0;
            position: relative;
        }

        .checker-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: var(--shadow-lg);
        }

        .checker-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .checker-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .checker-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .checker-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .checker-form {
            display: grid;
            gap: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group option {
            background: var(--secondary);
            color: var(--text-primary);
        }

        .btn-check-eligibility {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--gold) 0%, #f59e0b 100%);
            color: var(--primary);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.4);
            margin-top: 1rem;
        }

        .btn-check-eligibility:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.5);
        }

        .eligibility-result {
            margin-top: 2rem;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            display: none;
            animation: slideIn 0.4s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-eligible {
            background: rgba(16, 185, 129, 0.15);
            border: 2px solid var(--success);
        }

        .result-not-eligible {
            background: rgba(251, 191, 36, 0.15);
            border: 2px solid var(--gold);
        }

        .result-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .result-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .result-message {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .eligible-forces {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            margin: 1.5rem 0;
        }

        .force-badge {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .result-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-result {
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-register {
            background: var(--success);
            color: white;
        }

        .btn-register:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-explore {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-explore:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
        }

        /* Sections */
        section {
            padding: 100px 0;
            position: relative;
        }

        .section-header-modern {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 50px;
            color: var(--accent);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }

        .section-title-modern {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .section-subtitle-modern {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Features Section */
        .features-modern {
            background: var(--primary);
        }

        .features-grid-modern {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card-modern {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), #8b5cf6, #ec4899);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card-modern:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .feature-card-modern:hover::before {
            transform: scaleX(1);
        }

        .feature-icon-modern {
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }

        .icon-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
            border-radius: 16px;
            transform: rotate(45deg);
        }

        .feature-card-modern h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card-modern p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .feature-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .feature-link:hover {
            color: var(--gold);
            gap: 8px;
        }

        /* About Section */
        .about-modern {
            background: var(--secondary);
        }

        .about-grid-modern {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-content-modern {
            padding-right: 2rem;
        }

        .about-description {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .about-features {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .about-feature-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .check-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--success), #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .about-feature-item h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .about-feature-item p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .about-image-modern {
            position: relative;
        }

        .image-wrapper {
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(139, 92, 246, 0.3));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-wrapper:hover .image-overlay {
            opacity: 1;
        }

        .floating-stat {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem 2rem;
            border-radius: 16px;
            border: 2px solid var(--gold);
            box-shadow: var(--shadow-lg);
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gold);
            line-height: 1;
        }

        .stat-label {
            display: block;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        /* Testimonials */
        .testimonials-modern {
            background: var(--primary);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .testimonial-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .testimonial-info h4 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .testimonial-info p {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .quote-icon {
            position: absolute;
            right: 0;
            top: -10px;
            font-size: 3rem;
            color: rgba(251, 191, 36, 0.2);
            line-height: 1;
        }

        .testimonial-text {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .testimonial-rating {
            font-size: 1.2rem;
        }

        /* CTA Section */
        .cta-modern {
            background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
            padding: 80px 0;
        }

        .cta-content-modern {
            text-align: center;
        }

        .cta-content-modern h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-content-modern > p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn-cta-large {
            background: white;
            color: var(--accent);
            padding: 18px 50px;
            font-size: 1.2rem;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .btn-cta-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .cta-note {
            margin-top: 1rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Contact Section */
        .contact-modern {
            background: var(--secondary);
        }

        /* Contact Form Styles */
        .contact-form-wrapper {
            max-width: 700px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--text-secondary);
            opacity: 0.6;
        }

        .error-message {
            display: block;
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            min-height: 20px;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent) 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-text,
        .btn-loader {
            display: inline-block;
        }

        .btn-loader {
            display: none;
        }

        /* Footer Styles */
        .footer-modern {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #e2e8f0;
            padding: 60px 0 0;
            border-top: 1px solid rgba(251, 191, 36, 0.2);
            margin-top: 80px;
        }

        .footer-content-modern {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.1);
        }

        .footer-brand {
            max-width: 320px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .footer-logo img {
            height: 40px;
            width: auto;
        }

        .footer-logo span {
            font-size: 24px;
            font-weight: 700;
            color: #fbbf24;
            letter-spacing: -0.5px;
        }

        .footer-brand p {
            font-size: 14px;
            line-height: 1.6;
            color: #94a3b8;
        }

        .footer-column h4 {
            font-size: 16px;
            font-weight: 600;
            color: #fbbf24;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 12px;
        }

        .footer-column ul li a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer-column ul li a:hover {
            color: #fbbf24;
            transform: translateX(4px);
        }

        .footer-bottom-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 0;
        }

        .footer-bottom-modern p {
            font-size: 14px;
            color: #94a3b8;
        }

        .footer-social {
            display: flex;
            gap: 16px;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 8px;
            color: #fbbf24;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .footer-social a:hover {
            background: #fbbf24;
            color: #0f172a;
            transform: translateY(-3px);
        }

        .footer-social a svg {
            width: 20px;
            height: 20px;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(15, 23, 42, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                padding: 2rem;
                transition: left 0.3s ease;
                gap: 1rem;
            }

            .nav-menu.active {
                left: 0;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .hero-title-modern {
                font-size: 2.5rem;
            }

            .hero-subtitle-modern {
                font-size: 1rem;
            }

            .hero-stats-modern {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid-modern,
            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .about-grid-modern {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .about-content-modern {
                padding-right: 0;
            }

            .footer-content-modern {
                grid-template-columns: 1fr 1fr;
                gap: 30px;
            }

            .footer-brand {
                max-width: 100%;
            }

            .section-title-modern {
                font-size: 2rem;
            }

            .contact-form-wrapper,
            .checker-wrapper {
                padding: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .checker-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 640px) {
            .hero-title-modern {
                font-size: 2rem;
            }

            .hero-cta-modern {
                flex-direction: column;
            }

            .btn-hero-primary,
            .btn-hero-secondary {
                width: 100%;
                justify-content: center;
            }

            .footer-content-modern {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .footer-bottom-modern {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .footer-social {
                justify-content: center;
            }

            .contact-form-wrapper,
            .checker-wrapper {
                padding: 1.5rem;
            }

            .result-actions {
                flex-direction: column;
            }

            .btn-result {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sticky Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="nav-brand">
                <div class="logo-container">
                    <img src="\frontend\image\gurkhalogo.png" type="image" alt="Loading....">
                    <span class="brand-name">Gurkha Marga</span>
                </div>
            </div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#testimonials" class="nav-link">Success Stories</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                <li><a href="login.php" class="btn btn-outline-white">Login</a></li>
                <li><a href="register.php" class="btn btn-primary-gradient">Get Started</a></li>
            </ul>
            <div class="mobile-menu-toggle" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Animated Background -->
    <section id="home" class="hero-modern">
        <div class="hero-background">
            <div class="gradient-overlay"></div>
            <div class="animated-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
            </div>
        </div>
        
        <div class="container">
            <div class="hero-content-modern">
                <div class="hero-badge">
                    <span class="badge-icon">🎖️</span>
                    <span>Trusted by Aspirants</span>
                </div>
                
                <h1 class="hero-title-modern">
                    Transform Your Dream Into
                    <span class="gradient-text">Reality</span>
                </h1>
                
                <p class="hero-subtitle-modern">
                    Join Nepal's #1 free platform for army recruitment preparation. 
                    Get personalized training, expert guidance, and succeed in your journey to become a Gurkha.
                </p>
                
                <div class="hero-cta-modern">
                    <a href="#eligibility" class="btn btn-hero-primary">
                        <span>Check Your Eligibility</span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="#features" class="btn btn-hero-secondary">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 7V10L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>Watch Demo</span>
                    </a>
                </div>
                
                <div class="hero-stats-modern">
                    <div class="stat-item-modern">
                        <div class="stat-icon-wrapper">
                            <span class="stat-icon">👥</span>
                        </div>
                        <div class="stat-content">
                            <h3>10+</h3>
                            <p>Active Users</p>
                        </div>
                    </div>
                    <div class="stat-item-modern">
                        <div class="stat-icon-wrapper">
                            <span class="stat-icon">💪</span>
                        </div>
                        <div class="stat-content">
                            <h3>50+</h3>
                            <p>Exercise Guides</p>
                        </div>
                    </div>
                    <!-- <div class="stat-item-modern">
                        <div class="stat-icon-wrapper">
                            <span class="stat-icon"></span>
                        </div> -->
                        <div class="stat-content">
                            <h3>99%</h3>
                            <p>Free Forever</p>
                        </div>
                    </div>
                    <div class="stat-item-modern">
                        <div class="stat-icon-wrapper">
                            <span class="stat-icon">⭐</span>
                        </div>
                        <div class="stat-content">
                            <h3>4.9/5</h3>
                            <p>User Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <div class="scroll-icon"></div>
            <span>Scroll to explore</span>
        </div>
    </section>

    <!-- Eligibility Checker Section -->
    <section id="eligibility" class="eligibility-checker">
        <div class="container">
            <div class="checker-wrapper">
                <div class="checker-header">
                    <h2 class="checker-title">Check Your <span class="gradient-text">Eligibility</span></h2>
                    <p class="checker-subtitle">Find out if you meet the age requirements for army recruitment</p>
                </div>

                <form class="checker-form" id="eligibilityForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthYear">Birth Year *</label>
                            <input type="number" id="birthYear" name="birthYear" placeholder="Enter year (e.g., 2005)" min="1980" max="2020" required>
                        </div>
                        <div class="form-group">
                            <label for="birthMonth">Birth Month *</label>
                            <select id="birthMonth" name="birthMonth" required>
                                <option value="">Select Month</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthDay">Birth Day *</label>
                            <input type="number" id="birthDay" name="birthDay" placeholder="Day (1-31)" min="1" max="31" required>
                        </div>
                        <div class="form-group">
                            <label for="targetForce">Target Force *</label>
                            <select id="targetForce" name="targetForce" required>
                                <option value="">Select Force</option>
                                <option value="british">British Army</option>
                                <option value="nepal">Nepal Army</option>
                                <option value="indian">Indian Army</option>
                                <option value="singapore">Singapore Police Force</option>
                                <option value="french">French Foreign Legion</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-check-eligibility">
                        <span>Check My Eligibility</span>
                    </button>
                </form>

                <div id="eligibilityResult" class="eligibility-result"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-modern">
        <div class="container">
            <div class="section-header-modern">
                <span class="section-badge">FEATURES</span>
                <h2 class="section-title-modern">Everything You Need to <span class="gradient-text">Succeed</span></h2>
                <p class="section-subtitle-modern">Comprehensive tools and resources to prepare you for army recruitment</p>
            </div>
            
            <div class="features-grid-modern">
                <div class="feature-card-modern">
                    <div class="feature-icon-modern">
                        <div class="icon-bg"></div>
                        <span>📋</span>
                    </div>
                    <h3>Eligibility Checker</h3>
                    <p>Instantly verify if you meet the height, weight, and age requirements for your target service</p>
                    <a href="#eligibility" class="feature-link">Learn more →</a>
                </div>
                
                <div class="feature-card-modern">
                    <div class="feature-icon-modern">
                        <div class="icon-bg"></div>
                        <span>💪</span>
                    </div>
                    <h3>Smart Training Plans</h3>
                    <p>AI-powered personalized workout routines based on your fitness level and goals</p>
                    <a href="#" class="feature-link">Learn more →</a>
                </div>
                
                <div class="feature-card-modern">
                    <div class="feature-icon-modern">
                        <div class="icon-bg"></div>
                        <span>🥗</span>
                    </div>
                    <h3>Nutrition Guide</h3>
                    <p>Budget-friendly meal plans designed to fuel your training and optimize performance</p>
                    <a href="#" class="feature-link">Learn more →</a>
                </div>
                
                <div class="feature-card-modern">
                    <div class="feature-icon-modern">
                        <div class="icon-bg"></div>
                        <span>📚</span>
                    </div>
                    <h3>Video Library</h3>
                    <p>Step-by-step exercise tutorials with proper form and technique demonstrations</p>
                    <a href="#" class="feature-link">Learn more →</a>
                </div>
                
                <div class="feature-card-modern">
                    <div class="feature-icon-modern">
                        <div class="icon-bg"></div>
                        <span>📊</span>
                    </div>
                    <h3>Progress Tracking</h3>
                    <p>Visual analytics to monitor your improvement and stay motivated every day</p>
                    <a href="#" class="feature-link">Learn more →</a>
                </div>
                
                <div class="feature-card-modern">
                    <div class="feature-icon-modern">
                        <div class="icon-bg"></div>
                        <span>📄</span>
                    </div>
                    <h3>Document Guide</h3>
                    <p>Complete checklist of required documents with step-by-step preparation timeline</p>
                    <a href="#" class="feature-link">Learn more →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-modern">
        <div class="container">
            <div class="about-grid-modern">
                <div class="about-content-modern">
                    <span class="section-badge">ABOUT US</span>
                    <h2 class="section-title-modern">Why Choose <span class="gradient-text">Gurkha Marga</span>?</h2>
                    <p class="about-description">We understand the struggles aspiring soldiers face. That's why we created a completely free platform to democratize army recruitment preparation.</p>
                    
                    <div class="about-features">
                        <div class="about-feature-item">
                            <div class="check-icon">✓</div>
                            <div>
                                <h4>100% Free Access</h4>
                                <p>No hidden costs, no premium tiers. Everything is free forever.</p>
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <div class="check-icon">✓</div>
                            <div>
                                <h4>Expert Guidance</h4>
                                <p>Training plans designed by former military personnel and fitness experts.</p>
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <div class="check-icon">✓</div>
                            <div>
                                <h4>Proven Results</h4>
                                <p>500+ aspirants have successfully prepared using our platform.</p>
                            </div>
                        </div>
                        <div class="about-feature-item">
                            <div class="check-icon">✓</div>
                            <div>
                                <h4>Community Support</h4>
                                <p>Connect with fellow aspirants and share your journey.</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="register.php" class="btn btn-primary-gradient">Start Your Journey</a>
                </div>
                
                <div class="about-image-modern">
                    <div class="image-wrapper">
                        <img src="assets/images/training.jpg" alt="Training" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22600%22 height=%22700%22%3E%3Cdefs%3E%3ClinearGradient id=%22grad%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22%3E%3Cstop offset=%220%25%22 style=%22stop-color:%232c5f2d;stop-opacity:1%22 /%3E%3Cstop offset=%22100%25%22 style=%22stop-color:%231a3a1b;stop-opacity:1%22 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill=%22url(%23grad)%22 width=%22600%22 height=%22700%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22%23fbbf24%22 text-anchor=%22middle%22 dy=%22.3em%22%3ETraining Excellence%3C/text%3E%3C/svg%3E'">
                        <div class="image-overlay"></div>
                    </div>
                    <div class="floating-stat">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Success Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-modern">
        <div class="container">
            <div class="section-header-modern">
                <span class="section-badge">SUCCESS STORIES</span>
                <h2 class="section-title-modern">What Our <span class="gradient-text">Warriors</span> Say</h2>
                <p class="section-subtitle-modern">Real stories from real people who achieved their dreams</p>
            </div>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <span>RG</span>
                        </div>
                        <div class="testimonial-info">
                            <h4>Rajesh Gurung</h4>
                            <p>British Gurkha - 2024</p>
                        </div>
                        <div class="quote-icon">"</div>
                    </div>
                    <p class="testimonial-text">Gurkha Marga changed my life! The structured training and diet plans helped me pass the physical tests with flying colors.</p>
                    <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <span>ST</span>
                        </div>
                        <div class="testimonial-info">
                            <h4>Raj Rasaily</h4>
                            <p>Nepal Army - 2024</p>
                        </div>
                        <div class="quote-icon">"</div>
                    </div>
                    <p class="testimonial-text">Best free resource available! The eligibility checker helped me understand requirements clearly, and progress tracking kept me motivated.</p>
                    <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">
                            <span>BL</span>
                        </div>
                        <div class="testimonial-info">
                            <h4>Bikash Limbu</h4>
                            <p>Indian Gurkha - 2024</p>
                        </div>
                        <div class="quote-icon">"</div>
                    </div>
                    <p class="testimonial-text">I couldn't afford expensive training centers. Gurkha Marga gave me everything I needed for free. Forever grateful!</p>
                    <div class="testimonial-rating">⭐⭐⭐⭐⭐</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-modern">
        <div class="container">
            <div class="cta-content-modern">
                <h2>Ready to Start Your Journey?</h2>
                <p>Join 500+ aspirants who are already training with us</p>
                <a href="register.php" class="btn btn-cta-large">Create Free Account</a>
                <p class="cta-note">No credit card required • Start in 30 seconds</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-modern">
        <div class="container">
            <div class="section-header-modern">
                <span class="section-badge">CONTACT US</span>
                <h2 class="section-title-modern">Get In <span class="gradient-text">Touch</span></h2>
                <p class="section-subtitle-modern">Send us your queries and we'll get back to you soon.</p>
            </div>

            <div class="contact-form-wrapper">
                <?php if ($contact_message): ?>
                    <div class="alert alert-<?php echo htmlspecialchars(
                      $contact_message_type,
                    ); ?>">
                        <?php if ($contact_message_type === "success"): ?>
                            ✓
                        <?php else: ?>
                            ⚠
                        <?php endif; ?>
                        <?php echo htmlspecialchars($contact_message); ?>
                    </div>
                <?php endif; ?>

                <form action="process-contact.php" method="POST" id="contactForm" novalidate>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[
                      "csrf_token"
                    ]; ?>">

                    <div class="form-group">
                        <label for="username">Your Name *</label>
                        <input 
                            type="text" 
                            id="username"
                            name="username" 
                            placeholder="Enter your full name" 
                            required
                            minlength="2"
                            maxlength="100"
                            value="<?php echo isset(
                              $_SESSION["old_input"]["username"],
                            )
                              ? htmlspecialchars(
                                $_SESSION["old_input"]["username"],
                              )
                              : ""; ?>"
                        >
                        <span class="error-message" id="username-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input 
                            type="email" 
                            id="email"
                            name="email" 
                            placeholder="your.email@example.com" 
                            required
                            maxlength="255"
                            value="<?php echo isset(
                              $_SESSION["old_input"]["email"],
                            )
                              ? htmlspecialchars(
                                $_SESSION["old_input"]["email"],
                              )
                              : ""; ?>"
                        >
                        <span class="error-message" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="phone">Contact Number *</label>
                        <input 
                            type="tel" 
                            id="phone"
                            name="phone" 
                            placeholder="Your contact number" 
                            required
                            pattern="[0-9]{10,15}"
                            maxlength="15"
                            value="<?php echo isset(
                              $_SESSION["old_input"]["phone"],
                            )
                              ? htmlspecialchars(
                                $_SESSION["old_input"]["phone"],
                              )
                              : ""; ?>"
                        >
                        <span class="error-message" id="phone-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea 
                            name="message" 
                            id="message" 
                            placeholder="Type your query here..." 
                            required
                            minlength="10"
                            maxlength="1000"
                            rows="5"
                        ><?php echo isset($_SESSION["old_input"]["message"])
                          ? htmlspecialchars($_SESSION["old_input"]["message"])
                          : ""; ?></textarea>
                        <span class="error-message" id="message-error"></span>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text">Send Message</span>
                        <span class="btn-loader">Sending...</span>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="footer-content-modern">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <img src="\frontend\image\gurkhalogo.png" alt="Loading....">
                        <span>Gurkha Marga</span>
                    </div>
                    <p>Empowering youth with the right guidance for army recruitment. Your success is our mission.</p>
                </div>
                
                <div class="footer-column">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Training Tips</a></li>
                        <li><a href="#testimonials">Success Stories</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom-modern">
                <p>&copy; 2026 Gurkha Marga. All rights reserved. Built for providing guidance for Gurkhas 🪖</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/SahilShrestha" aria-label="Facebook" target="_blank">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/shresthasahil7/" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="YouTube">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const navMenu = document.getElementById('navMenu');

        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    navMenu.classList.remove('active');
                }
            });
        });

        // Active nav link on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const scrollY = window.pageYOffset;

            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 100;
                const sectionId = current.getAttribute('id');
                
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        });

        // Eligibility Checker
        document.getElementById('eligibilityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const year = parseInt(document.getElementById('birthYear').value);
            const month = parseInt(document.getElementById('birthMonth').value);
            const day = parseInt(document.getElementById('birthDay').value);
            const targetForce = document.getElementById('targetForce').value;
            
            // Calculate age
            const birthDate = new Date(year, month - 1, day);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            // Age criteria
            const criteria = {
                british: { min: 17, max: 21, name: "British Army" },
                nepal: { min: 18, max: 21, name: "Nepal Army" },
                indian: { min: 17, max: 21, name: "Indian Army" },
                singapore: { min: 18, max: 21, name: "Singapore Police Force" },
                french: { min: 17, max: 35, name: "French Foreign Legion" }
            };
            
            const resultDiv = document.getElementById('eligibilityResult');
            
            if (!targetForce) {
                resultDiv.innerHTML = `
                    <div class="result-not-eligible">
                        <div class="result-icon">⚠️</div>
                        <h3 class="result-title">Please Select a Force</h3>
                        <p class="result-message">Choose your target force to check eligibility.</p>
                    </div>
                `;
                resultDiv.style.display = 'block';
                return;
            }
            
            // Check which forces the user is eligible for
            const eligibleForces = [];
            for (const [key, value] of Object.entries(criteria)) {
                if (age >= value.min && age <= value.max) {
                    eligibleForces.push(value.name);
                }
            }
            
            const selected = criteria[targetForce];
            const isEligible = age >= selected.min && age <= selected.max;
            
            if (isEligible) {
                resultDiv.innerHTML = `
                    <div class="result-eligible">
                        <div class="result-icon">🎉</div>
                        <h3 class="result-title">Congratulations! You're Eligible</h3>
                        <p class="result-message">
                            You are <strong>${age} years old</strong> and meet the age requirements for <strong>${selected.name}</strong> 
                            (Age: ${selected.min}-${selected.max} years).
                        </p>
                        ${eligibleForces.length > 1 ? `
                            <p class="result-message">You're also eligible for:</p>
                            <div class="eligible-forces">
                                ${eligibleForces.filter(f => f !== selected.name).map(f => `<span class="force-badge">${f}</span>`).join('')}
                            </div>
                        ` : ''}
                        <div class="result-actions">
                            <a href="register.php" class="btn-result btn-register">Create Account & Start Training</a>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="result-not-eligible">
                        <div class="result-icon">ℹ️</div>
                        <h3 class="result-title">Not Eligible for ${selected.name}</h3>
                        <p class="result-message">
                            You are <strong>${age} years old</strong>. The ${selected.name} requires candidates to be between 
                            <strong>${selected.min}-${selected.max} years old</strong>.
                        </p>
                        ${eligibleForces.length > 0 ? `
                            <p class="result-message">However, you're eligible for:</p>
                            <div class="eligible-forces">
                                ${eligibleForces.map(f => `<span class="force-badge">${f}</span>`).join('')}
                            </div>
                            <div class="result-actions">
                                <a href="register.php" class="btn-result btn-register">Create Account & Start Training</a>
                            </div>
                        ` : `
                            <p class="result-message">
                                You can still explore our basic exercise plans and training resources to stay fit and prepared for future opportunities!
                            </p>
                            <div class="result-actions">
                                <a href="#features" class="btn-result btn-explore">Explore Training Plans</a>
                            </div>
                        `}
                    </div>
                `;
            }
            
            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        // Contact Form Validation
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Validate username
            const username = document.getElementById('username').value.trim();
            if (username.length < 2) {
                document.getElementById('username-error').textContent = 'Name must be at least 2 characters';
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email address';
                isValid = false;
            }
            
            // Validate phone
            const phone = document.getElementById('phone').value.trim();
            if (!/^[0-9]{10,15}$/.test(phone)) {
                document.getElementById('phone-error').textContent = 'Please enter a valid phone number (10-15 digits)';
                isValid = false;
            }
            
            // Validate message
            const message = document.getElementById('message').value.trim();
            if (message.length < 10) {
                document.getElementById('message-error').textContent = 'Message must be at least 10 characters';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            } else {
                // Show loading state
                document.querySelector('.btn-text').style.display = 'none';
                document.querySelector('.btn-loader').style.display = 'inline';
                document.getElementById('submitBtn').disabled = true;
            }
        });
    </script>
</body>
</html>

<?php // Clear old input after displaying
unset($_SESSION["old_input"]); ?>  