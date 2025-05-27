<?php
session_start();
include 'config/koneksi.php';

$error = '';
$success = '';
$login_type = $_GET['type'] ?? 'cashier'; // Default to cashier login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['login_type'] ?? 'cashier';
    
    if ($type === 'cashier') {
        // Cashier Login Logic
        $nama_kasir = trim($_POST['nama_kasir'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (!empty($nama_kasir) && !empty($password)) {
            $query = "SELECT * FROM kasir WHERE nama_kasir = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $nama_kasir);
            $stmt->execute();
            $result = $stmt->get_result();
            $kasir = $result->fetch_assoc();
            
            if ($kasir && trim($kasir['password']) === trim($password)) {
                $_SESSION['nama_kasir'] = $kasir['nama_kasir'];
                $_SESSION['id_kasir'] = $kasir['id_kasir'];
                $success = "Welcome back, {$kasir['nama_kasir']}!";
                echo "<script>
                    setTimeout(() => {
                        window.location='purple-free/dist/index.php';
                    }, 1500);
                </script>";
            } else {
                $error = 'Invalid username or password for cashier login.';
            }
        } else {
            $error = 'Please fill in all cashier login fields.';
        }
    } else {
        // Member Login Logic
        $phone = trim($_POST['phone'] ?? '');
        $name = trim($_POST['name'] ?? '');
        
        if (!empty($phone) && !empty($name)) {
            try {
                $pdo = new PDO("mysql:host=localhost;dbname=db_cafedeflour", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare("SELECT * FROM special_members WHERE nomor_hp = ? AND nama_member = ?");
                $stmt->execute([$phone, $name]);
                $member = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($member) {
                    $_SESSION['member_id'] = $member['id_member'];
                    $_SESSION['member_name'] = $member['nama_member'];
                    $success = "Welcome back, {$member['nama_member']}!";
                    echo "<script>
                        setTimeout(() => {
                            window.location='/member_dashboard/dashboard.php';
                        }, 1500);
                    </script>";
                } else {
                    $error = 'Invalid credentials for member login.';
                }
            } catch(PDOException $e) {
                $error = 'Database connection error.';
            }
        } else {
            $error = 'Please fill in all member login fields.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café de Flour - Elegant Login Portal</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            /* Elegant Café Color Palette */
            --coffee-dark: #2c1810;
            --coffee-medium: #4a2c2a;
            --coffee-light: #6b4423;
            --espresso: #3c2415;
            --mocha: #8b4513;
            --latte: #d2b48c;
            --cream: #f5f5dc;
            --gold: #d4af37;
            --gold-light: #f4e4bc;
            --copper: #b87333;
            
            /* Neutral Tones */
            --warm-white: #faf8f5;
            --soft-beige: #f7f3e9;
            --light-brown: #e8dcc0;
            --medium-brown: #c8a882;
            --dark-brown: #5d4037;
            
            /* Text Colors */
            --text-primary: #2c1810;
            --text-secondary: #5d4037;
            --text-light: #8d6e63;
            --text-white: #faf8f5;
            
            /* Status Colors */
            --success: #4caf50;
            --error: #d32f2f;
            --warning: #ff9800;
            
            /* Shadows & Effects */
            --shadow-soft: 0 2px 8px rgba(44, 24, 16, 0.08);
            --shadow-medium: 0 4px 16px rgba(44, 24, 16, 0.12);
            --shadow-strong: 0 8px 32px rgba(44, 24, 16, 0.16);
            --shadow-elegant: 0 12px 40px rgba(44, 24, 16, 0.2);
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 20px;
            --radius-xl: 24px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Coffee Bean Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 69, 19, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(184, 115, 51, 0.08) 0%, transparent 50%);
            animation: backgroundFloat 15s ease-in-out infinite;
            z-index: -2;
        }

        @keyframes backgroundFloat {
            0%, 100% { 
                transform: translateX(0) translateY(0) scale(1);
            }
            50% { 
                transform: translateX(30px) translateY(-30px) scale(1.05);
            }
        }

        /* Floating Coffee Elements */
        .coffee-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .coffee-bean {
            position: absolute;
            width: 12px;
            height: 18px;
            background: var(--coffee-dark);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            opacity: 0.1;
            animation: floatBean 12s ease-in-out infinite;
        }

        .coffee-bean::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2px;
            height: 12px;
            background: var(--coffee-light);
            transform: translate(-50%, -50%);
            border-radius: 2px;
        }

        .coffee-bean:nth-child(1) {
            top: 15%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 10s;
        }

        .coffee-bean:nth-child(2) {
            top: 70%;
            right: 15%;
            animation-delay: 3s;
            animation-duration: 14s;
        }

        .coffee-bean:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 6s;
            animation-duration: 12s;
        }

        .coffee-bean:nth-child(4) {
            top: 40%;
            right: 25%;
            animation-delay: 9s;
            animation-duration: 16s;
        }

        @keyframes floatBean {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg);
            }
            25% { 
                transform: translateY(-20px) rotate(90deg);
            }
            50% { 
                transform: translateY(-10px) rotate(180deg);
            }
            75% { 
                transform: translateY(-25px) rotate(270deg);
            }
        }

        /* Main Container */
        .login-container {
            width: 100%;
            max-width: 1000px;
            background: var(--warm-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-elegant);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 700px;
            position: relative;
            animation: containerSlideIn 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes containerSlideIn {
            from {
                opacity: 0;
                transform: translateY(60px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Left Side - Elegant Branding */
        .brand-section {
            background: linear-gradient(135deg, 
                var(--coffee-dark) 0%, 
                var(--espresso) 30%, 
                var(--coffee-medium) 70%, 
                var(--mocha) 100%);
            padding: 4rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: var(--text-white);
            position: relative;
            overflow: hidden;
        }

        .brand-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="coffee-pattern" width="30" height="30" patternUnits="userSpaceOnUse"><circle cx="15" cy="15" r="3" fill="white" opacity="0.05"/><circle cx="5" cy="5" r="1.5" fill="white" opacity="0.03"/><circle cx="25" cy="25" r="2" fill="white" opacity="0.04"/></pattern></defs><rect width="100" height="100" fill="url(%23coffee-pattern)"/></svg>');
            animation: patternDrift 20s linear infinite;
        }

        @keyframes patternDrift {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(30px) translateY(30px); }
        }

        .brand-content {
            position: relative;
            z-index: 2;
            animation: brandContentSlide 1.2s ease-out 0.3s both;
        }

        @keyframes brandContentSlide {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-logo {
            font-size: 5rem;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, var(--gold), var(--gold-light), var(--cream));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: logoGlow 3s ease-in-out infinite alternate;
            filter: drop-shadow(0 4px 8px rgba(212, 175, 55, 0.3));
        }

        @keyframes logoGlow {
            from {
                filter: drop-shadow(0 4px 8px rgba(212, 175, 55, 0.3));
            }
            to {
                filter: drop-shadow(0 8px 16px rgba(212, 175, 55, 0.5));
            }
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            animation: titleShimmer 4s ease-in-out infinite;
        }

        @keyframes titleShimmer {
            0%, 100% { 
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }
            50% { 
                text-shadow: 0 4px 8px rgba(0, 0, 0, 0.4), 0 0 20px rgba(212, 175, 55, 0.2);
            }
        }

        .brand-subtitle {
            font-family: 'Crimson Text', serif;
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 320px;
            font-style: italic;
            animation: subtitleFade 1.2s ease-out 0.6s both;
        }

        @keyframes subtitleFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 0.9;
                transform: translateY(0);
            }
        }

        .brand-decorative {
            margin: 2rem 0;
            font-size: 1.5rem;
            opacity: 0.6;
            animation: decorativeFloat 6s ease-in-out infinite;
        }

        @keyframes decorativeFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        /* Right Side - Elegant Form */
        .form-section {
            padding: 4rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: var(--warm-white);
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: formHeaderSlide 1.2s ease-out 0.5s both;
        }

        @keyframes formHeaderSlide {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--coffee-dark), var(--mocha));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-family: 'Crimson Text', serif;
            font-style: italic;
        }

        /* Elegant Toggle Tabs */
        .login-tabs {
            display: flex;
            background: var(--soft-beige);
            border-radius: var(--radius-lg);
            padding: 0.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            box-shadow: inset 0 2px 4px rgba(44, 24, 16, 0.1);
            animation: tabsSlide 1.2s ease-out 0.7s both;
        }

        @keyframes tabsSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-indicator {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            width: calc(50% - 0.25rem);
            height: calc(100% - 1rem);
            background: linear-gradient(135deg, var(--gold), var(--copper));
            border-radius: calc(var(--radius-lg) - 4px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-medium);
        }

        .tab-indicator.member {
            transform: translateX(calc(100% + 0.25rem));
        }

        .tab-button {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: calc(var(--radius-lg) - 4px);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            font-family: 'Inter', sans-serif;
        }

        .tab-button.active {
            color: var(--text-white);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .tab-button:hover:not(.active) {
            color: var(--text-primary);
            background: rgba(212, 175, 55, 0.1);
        }

        /* FIXED: Elegant Form Container */
        .forms-container {
            position: relative;
            min-height: 400px; /* Ensure container maintains height */
            animation: formsSlide 1.2s ease-out 0.9s both;
        }

        @keyframes formsSlide {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* FIXED: Form wrapper positioning */
        .form-wrapper {
            opacity: 0;
            visibility: hidden;
            transform: translateX(30px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
        }

        .form-wrapper.active {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
            position: relative;
        }

        /* Elegant Form Elements */
        .form-group {
            margin-bottom: 2rem;
            animation: formGroupSlide 0.8s ease-out both;
        }

        .form-group:nth-child(1) { animation-delay: 1.1s; }
        .form-group:nth-child(2) { animation-delay: 1.2s; }
        .form-group:nth-child(3) { animation-delay: 1.3s; }

        @keyframes formGroupSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .input-group {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 1.25rem 1.25rem 1.25rem 3.5rem;
            border: 2px solid var(--light-brown);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            background: var(--warm-white);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            box-shadow: inset 0 2px 4px rgba(44, 24, 16, 0.05);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 
                0 0 0 4px rgba(212, 175, 55, 0.15),
                inset 0 2px 4px rgba(44, 24, 16, 0.05);
            transform: translateY(-2px);
            background: var(--cream);
        }

        .form-input:focus + .input-icon {
            color: var(--gold);
            transform: translateY(-50%) scale(1.1);
        }

        .form-input:focus ~ .form-label {
            color: var(--gold);
        }

        .form-input::placeholder {
            color: var(--text-light);
            font-style: italic;
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .password-toggle {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .password-toggle:hover {
            color: var(--gold);
            background: rgba(212, 175, 55, 0.1);
            transform: translateY(-50%) scale(1.1);
        }

        /* Elegant Submit Button */
        .submit-btn {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--coffee-dark), var(--mocha), var(--gold));
            color: var(--text-white);
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Inter', sans-serif;
            animation: buttonSlide 0.8s ease-out 1.4s both;
            box-shadow: var(--shadow-medium);
        }

        @keyframes buttonSlide {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.8s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, var(--espresso), var(--coffee-dark), var(--copper));
            transform: translateY(-3px);
            box-shadow: var(--shadow-strong);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Elegant Alert Messages */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: alertSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        @keyframes alertSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: alertShimmer 3s ease-in-out infinite;
        }

        @keyframes alertShimmer {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }

        .alert-error {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-success {
            background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        /* Elegant Features */
        .features {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--light-brown);
            animation: featuresSlide 1.2s ease-out 1.6s both;
        }

        @keyframes featuresSlide {
            from {
                opacity: 0;
                transform: translateY(25px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: var(--soft-beige);
            border-radius: var(--radius-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: featureSlideIn 0.8s ease-out both;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--light-brown);
        }

        .feature-item:nth-child(1) { animation-delay: 1.7s; }
        .feature-item:nth-child(2) { animation-delay: 1.8s; }
        .feature-item:nth-child(3) { animation-delay: 1.9s; }
        .feature-item:nth-child(4) { animation-delay: 2s; }

        @keyframes featureSlideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .feature-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
            transition: left 0.8s;
        }

        .feature-item:hover::before {
            left: 100%;
        }

        .feature-item:hover {
            background: var(--gold-light);
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            border-color: var(--gold);
        }

        .feature-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, var(--coffee-dark), var(--gold));
            color: var(--text-white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
        }

        .feature-item:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-medium);
        }

        .feature-text {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        /* Loading Animation */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 1.2rem;
            height: 1.2rem;
            margin: -0.6rem 0 0 -0.6rem;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: elegantSpin 1s linear infinite;
        }

        @keyframes elegantSpin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 450px;
                min-height: auto;
            }

            .brand-section {
                padding: 3rem 2rem;
                order: 2;
            }

            .form-section {
                padding: 3rem 2rem;
                order: 1;
            }

            .brand-title {
                font-size: 2.2rem;
            }

            .brand-logo {
                font-size: 4rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .form-section,
            .brand-section {
                padding: 2rem 1.5rem;
            }

            .form-title {
                font-size: 1.6rem;
            }

            .brand-title {
                font-size: 2rem;
            }

            .brand-logo {
                font-size: 3.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Floating Coffee Elements -->
    <div class="coffee-elements">
        <div class="coffee-bean"></div>
        <div class="coffee-bean"></div>
        <div class="coffee-bean"></div>
        <div class="coffee-bean"></div>
    </div>

    <div class="login-container">
        <!-- Elegant Brand Section -->
        <div class="brand-section">
            <div class="brand-content">
                <div class="brand-logo">
                    <i class="fas fa-coffee"></i>
                </div>
                <h1 class="brand-title">Café de Flour</h1>
                <div class="brand-decorative">
                    ❦ ❦ ❦
                </div>
                <p class="brand-subtitle">
                    Where every cup tells a story of excellence, 
                    and every moment is crafted with passion. 
                    Welcome to our elegant management portal.
                </p>
            </div>
        </div>

        <!-- Elegant Form Section -->
        <div class="form-section">
            <div class="form-header">
                <h2 class="form-title">Welcome Back</h2>
                <p class="form-subtitle">Please sign in to continue your journey</p>
            </div>

            <!-- Elegant Login Type Tabs -->
            <div class="login-tabs">
                <div class="tab-indicator <?= $login_type === 'member' ? 'member' : '' ?>"></div>
                <button type="button" class="tab-button <?= $login_type === 'cashier' ? 'active' : '' ?>" 
                        onclick="switchTab('cashier')">
                    <i class="fas fa-user-tie me-2"></i>
                    Staff Portal
                </button>
                <button type="button" class="tab-button <?= $login_type === 'member' ? 'active' : '' ?>" 
                        onclick="switchTab('member')">
                    <i class="fas fa-crown me-2"></i>
                    Member Portal
                </button>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Forms Container -->
            <div class="forms-container">
                <!-- Cashier Login Form -->
                <div class="form-wrapper <?= $login_type === 'cashier' ? 'active' : '' ?>" id="cashierForm">
                    <form method="POST" action="" id="cashierLoginForm">
                        <input type="hidden" name="login_type" value="cashier">
                        
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-input" 
                                       name="nama_kasir" 
                                       placeholder="Enter your username"
                                       required
                                       autocomplete="username">
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-input" 
                                       name="password" 
                                       id="cashierPassword"
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" onclick="togglePassword('cashierPassword')">
                                    <i class="fas fa-eye" id="cashierPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="cashierSubmitBtn">
                            Access Dashboard
                        </button>
                        <a  href="index.php">Back</a>

                    </form>
                </div>

                <!-- Member Login Form -->
                <div class="form-wrapper <?= $login_type === 'member' ? 'active' : '' ?>" id="memberForm">
                    <form method="POST" action="" id="memberLoginForm">
                        <input type="hidden" name="login_type" value="member">
                        
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-input" 
                                       name="name" 
                                       placeholder="Enter your full name"
                                       required
                                       autocomplete="name">
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <div class="input-group">
                                <input type="tel" 
                                       class="form-input" 
                                       name="phone" 
                                       placeholder="Enter your phone number"
                                       required
                                       autocomplete="tel">
                                <i class="fas fa-phone input-icon"></i>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="memberSubmitBtn">
                            Access Member Portal
                        </button>
                    </form>
                </div>
            </div>

            <!-- Features Section -->
            <div class="features">
                <div class="features-grid" id="featuresGrid">
                    <!-- Features will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Features data
        const features = {
            cashier: [
                { icon: 'fas fa-chart-line', text: 'Sales Analytics' },
                { icon: 'fas fa-users-cog', text: 'Staff Management' },
                { icon: 'fas fa-warehouse', text: 'Inventory Control' },
                { icon: 'fas fa-receipt', text: 'Transaction Records' }
            ],
            member: [
                { icon: 'fas fa-gem', text: 'Loyalty Rewards' },
                { icon: 'fas fa-history', text: 'Order History' },
                { icon: 'fas fa-gift', text: 'Exclusive Offers' },
                { icon: 'fas fa-crown', text: 'VIP Benefits' }
            ]
        };

        // FIXED: Switch between tabs with proper form handling
        function switchTab(type) {
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('type', type);
            window.history.pushState({}, '', url);

            // Animate tab indicator
            const indicator = document.querySelector('.tab-indicator');
            if (type === 'member') {
                indicator.classList.add('member');
            } else {
                indicator.classList.remove('member');
            }

            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');

            // FIXED: Proper form switching logic
            const allForms = document.querySelectorAll('.form-wrapper');
            const targetForm = document.getElementById(type + 'Form');

            // Hide all forms first
            allForms.forEach(form => {
                form.classList.remove('active');
            });

            // Show target form after a brief delay
            setTimeout(() => {
                targetForm.classList.add('active');
                
                // Reset form values to prevent data persistence issues
                const formInputs = targetForm.querySelectorAll('.form-input');
                formInputs.forEach(input => {
                    if (input.type !== 'hidden') {
                        input.value = '';
                    }
                });

                // Reset animations for new form
                const formGroups = targetForm.querySelectorAll('.form-group');
                formGroups.forEach((group, index) => {
                    group.style.animation = 'none';
                    group.offsetHeight; // Trigger reflow
                    group.style.animation = `formGroupSlide 0.8s ease-out ${0.1 + index * 0.1}s both`;
                });
                
                const submitBtn = targetForm.querySelector('.submit-btn');
                if (submitBtn) {
                    submitBtn.style.animation = 'none';
                    submitBtn.offsetHeight; // Trigger reflow
                    submitBtn.style.animation = 'buttonSlide 0.8s ease-out 0.4s both';
                }
            }, 100);

            // Update features
            updateFeatures(type);

            // Clear alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.animation = 'alertSlideOut 0.4s ease-out forwards';
                setTimeout(() => alert.remove(), 400);
            });
        }

        // Update features with staggered animation
        function updateFeatures(type) {
            const grid = document.getElementById('featuresGrid');
            const typeFeatures = features[type] || features.cashier;
            
            // Fade out current features
            const currentFeatures = grid.querySelectorAll('.feature-item');
            currentFeatures.forEach((item, index) => {
                item.style.animation = `featureSlideOut 0.4s ease-out ${index * 0.05}s forwards`;
            });
            
            setTimeout(() => {
                grid.innerHTML = typeFeatures.map((feature, index) => `
                    <div class="feature-item" style="animation: featureSlideIn 0.8s ease-out ${0.1 + index * 0.1}s both;">
                        <div class="feature-icon">
                            <i class="${feature.icon}"></i>
                        </div>
                        <div class="feature-text">${feature.text}</div>
                    </div>
                `).join('');
            }, 300);
        }

        // Add slide out animations
        const slideOutStyle = document.createElement('style');
        slideOutStyle.textContent = `
            @keyframes featureSlideOut {
                to {
                    opacity: 0;
                    transform: translateX(30px);
                }
            }
            @keyframes alertSlideOut {
                to {
                    opacity: 0;
                    transform: translateY(-30px) scale(0.95);
                }
            }
        `;
        document.head.appendChild(slideOutStyle);

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + 'Icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            const currentType = new URLSearchParams(window.location.search).get('type') || 'cashier';
            updateFeatures(currentType);

            // Form submission handling
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('.submit-btn');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    
                    setTimeout(() => {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                    }, 3000);
                });
            });
        });

        // Auto-hide success messages
        setTimeout(() => {
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(alert => {
                alert.style.animation = 'alertSlideOut 0.6s ease-out forwards';
                setTimeout(() => alert.remove(), 600);
            });
        }, 4000);
    </script>
</body>
</html>