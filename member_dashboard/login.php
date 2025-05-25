<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'db_cafedeflour';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';

if ($_POST) {
    $phone = trim($_POST['phone']);
    $name = trim($_POST['name']);
    
    if (!empty($phone) && !empty($name)) {
        // Check if member exists
        $stmt = $pdo->prepare("SELECT * FROM special_members WHERE nomor_hp = ? AND nama_member = ?");
        $stmt->execute([$phone, $name]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($member) {
            $_SESSION['member_id'] = $member['id_member'];
            $_SESSION['member_name'] = $member['nama_member'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials. Please check your name and phone number.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login - Membership Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
            min-height: 100vh;
            overflow-y: auto;
            padding: 10px;
            position: relative;
        }

        /* Animated background particles */
        .particle {
            position: fixed;
            width: 4px;
            height: 4px;
            background: rgba(139, 69, 19, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat 8s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Floating coffee elements */
        .floating-element {
            position: fixed;
            font-size: 1.5rem;
            opacity: 0.1;
            animation: floatAround 12s ease-in-out infinite;
            pointer-events: none;
            z-index: 1;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 15%;
            animation-delay: 4s;
        }

        .floating-element:nth-child(3) {
            bottom: 30%;
            left: 20%;
            animation-delay: 8s;
        }

        @keyframes floatAround {
            0%, 100% { transform: translateY(0px) translateX(0px) rotate(0deg); }
            25% { transform: translateY(-20px) translateX(10px) rotate(90deg); }
            50% { transform: translateY(-10px) translateX(-10px) rotate(180deg); }
            75% { transform: translateY(-15px) translateX(15px) rotate(270deg); }
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 320px;
            text-align: center;
            position: relative;
            border: 1px solid rgba(139, 69, 19, 0.1);
            animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4a574, #8b4513, #2c1810);
            border-radius: 20px 20px 0 0;
        }

        .login-container::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 165, 116, 0.05) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #8b4513, #d4a574, #2c1810);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            animation: logoFloat 3s ease-in-out infinite;
            position: relative;
        }

        @keyframes logoFloat {
            0%, 100% { 
                transform: translateY(0px) scale(1);
                filter: drop-shadow(0 5px 15px rgba(212, 165, 116, 0.2));
            }
            50% { 
                transform: translateY(-5px) scale(1.05);
                filter: drop-shadow(0 10px 25px rgba(212, 165, 116, 0.3));
            }
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: #2c1810;
            margin-bottom: 5px;
            font-weight: 600;
            letter-spacing: -0.5px;
            animation: titleSlide 0.8s ease-out 0.2s both;
        }

        @keyframes titleSlide {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .login-subtitle {
            color: #64748b;
            margin-bottom: 25px;
            font-size: 0.85rem;
            font-weight: 500;
            opacity: 0.8;
            animation: subtitleSlide 0.8s ease-out 0.4s both;
        }

        @keyframes subtitleSlide {
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
            margin-bottom: 18px;
            text-align: left;
            animation: formSlide 0.6s ease-out both;
        }

        .form-group:nth-child(1) { animation-delay: 0.6s; }
        .form-group:nth-child(2) { animation-delay: 0.7s; }

        @keyframes formSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
        }

        .input-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 165, 116, 0.1), transparent);
            transition: left 0.6s;
            z-index: 1;
        }

        .input-wrapper:focus-within::before {
            left: 100%;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid rgba(203, 213, 225, 0.5);
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }

        .form-group input:focus {
            outline: none;
            border-color: #d4a574;
            box-shadow: 
                0 0 0 3px rgba(212, 165, 116, 0.1),
                0 4px 15px rgba(212, 165, 116, 0.1);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-1px) scale(1.01);
        }

        .form-group input::placeholder {
            color: rgba(100, 116, 139, 0.6);
            font-weight: 400;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1rem;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .form-group input:focus + .input-icon {
            color: #d4a574;
            transform: translateY(-50%) scale(1.1);
            animation: iconBounce 0.6s ease;
        }

        @keyframes iconBounce {
            0%, 100% { transform: translateY(-50%) scale(1.1); }
            50% { transform: translateY(-50%) scale(1.3); }
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8b4513 0%, #d4a574 50%, #2c1810 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 8px;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Inter', sans-serif;
            animation: buttonSlide 0.6s ease-out 0.8s both;
        }

        @keyframes buttonSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 
                0 10px 25px rgba(139, 69, 19, 0.2),
                0 5px 15px rgba(212, 165, 116, 0.1);
        }

        .login-btn:active {
            transform: translateY(0) scale(0.98);
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #dc2626;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 18px;
            border: 1px solid #fecaca;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
            animation: errorSlide 0.5s ease-out, errorShake 0.6s ease-in-out 0.5s;
        }

        @keyframes errorSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-3px); }
            75% { transform: translateX(3px); }
        }

        .features {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid rgba(203, 213, 225, 0.3);
            animation: featuresSlide 0.6s ease-out 1s both;
        }

        @keyframes featuresSlide {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 6px 0;
            border-radius: 6px;
            animation: featureSlide 0.4s ease-out both;
        }

        .feature-item:nth-child(1) { animation-delay: 1.2s; }
        .feature-item:nth-child(2) { animation-delay: 1.3s; }
        .feature-item:nth-child(3) { animation-delay: 1.4s; }

        @keyframes featureSlide {
            from {
                opacity: 0;
                transform: translateX(-15px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .feature-item:hover {
            color: #374151;
            background: rgba(212, 165, 116, 0.1);
            padding-left: 6px;
            transform: translateX(3px);
        }

        .feature-item i {
            color: #d4a574;
            margin-right: 10px;
            width: 16px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .feature-item:hover i {
            color: #8b4513;
            transform: scale(1.1) rotate(5deg);
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
                margin: 5px;
                max-width: 300px;
            }
            
            .login-title {
                font-size: 1.4rem;
            }

            .logo {
                font-size: 2.2rem;
            }

            .form-group input {
                padding: 10px 12px 10px 40px;
            }

            .input-icon {
                left: 12px;
            }
        }

        /* Loading animation for button */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Scroll animations */
        .scroll-indicator {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(139, 69, 19, 0.3);
            font-size: 1.5rem;
            animation: scrollBounce 2s ease-in-out infinite;
        }

        @keyframes scrollBounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }

        /* Enhanced ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: rippleEffect 0.6s linear;
            pointer-events: none;
        }

        @keyframes rippleEffect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <i class="fas fa-coffee"></i>
            </div>
            <h1 class="login-title">Café de Flour</h1>
            <p class="login-subtitle">Member Portal Access</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrapper">
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-wrapper">
                        <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
                        <i class="fas fa-phone input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Access
                </button>
            </form>

            <div class="features">
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <span>View points & transactions</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-medal"></i>
                    <span>Check membership tier</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-history"></i>
                    <span>Track purchase history</span>
                </div>
            </div>
        </div>
    </div>

    <div class="scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>

</body>
</html>