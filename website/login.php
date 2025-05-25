<?php
session_start();
include 'config/koneksi.php'; // Pastikan file ini ada dan benar

$error = '';
$nama_kasir = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kasir = $_POST['nama_kasir'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query ke database
    $query = "SELECT * FROM kasir WHERE nama_kasir = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nama_kasir);
    $stmt->execute();
    $result = $stmt->get_result();
    $kasir = $result->fetch_assoc();

    if ($kasir && trim($kasir['password']) === trim($password)) {
        $_SESSION['nama_kasir'] = $kasir['nama_kasir'];
        $_SESSION['id_kasir'] = $kasir['id_kasir']; // Jika ingin dipakai nanti
        echo "
        <script>
        alert('Selamat Datang Kembali, {$kasir['nama_kasir']}!');
        window.location='purple-free/dist/index.php';
        </script>
        ";
    } else {
        echo "
        <script>
        alert('Username atau Password salah!');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Café de Flour</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c1810;
            --secondary-color: #8b4513;
            --accent-color: #d4a574;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #fefefe;
            --bg-cream: #faf8f5;
            --shadow-light: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --border-radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #faf8f5 0%, #f3f0eb 50%, #ede8e0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(212, 165, 116, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(139, 69, 19, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 0%, rgba(44, 24, 16, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Login Container */
        .login-container {
            max-width: 1000px;
            width: 100%;
            margin: 2rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            min-height: 600px;
        }

        /* Left Side - Branding */
        .brand-side {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .brand-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="coffee-pattern" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="white" opacity="0.1"/><circle cx="5" cy="5" r="1" fill="white" opacity="0.05"/><circle cx="15" cy="15" r="1.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23coffee-pattern)"/></svg>');
            opacity: 0.3;
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        .brand-logo {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .brand-logo i {
            font-size: 3rem;
            color: var(--accent-color);
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, white, var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 300px;
        }

        /* Right Side - Login Form */
        .form-side {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-subtitle {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control-modern {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(212, 165, 116, 0.1);
            background: white;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control-modern:focus + .input-icon {
            color: var(--accent-color);
        }

        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Loading State */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }

        @keyframes button-loading-spinner {
            from { transform: rotate(0turn); }
            to { transform: rotate(1turn); }
        }

        /* Error Message */
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #fecaca;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Success Message */
        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #bbf7d0;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #f1f5f9;
        }

        .footer-text {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .footer-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
            }

            .login-card {
                border-radius: 16px;
                min-height: auto;
            }

            .brand-side {
                padding: 2rem;
                order: 2;
            }

            .form-side {
                padding: 2rem;
                order: 1;
            }

            .brand-title {
                font-size: 2rem;
            }

            .form-title {
                font-size: 1.8rem;
            }

            .brand-logo {
                width: 80px;
                height: 80px;
                margin-bottom: 1.5rem;
            }

            .brand-logo i {
                font-size: 2rem;
            }
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s ease forwards;
        }

        .fade-in.delay-1 { animation-delay: 0.2s; }
        .fade-in.delay-2 { animation-delay: 0.4s; }
        .fade-in.delay-3 { animation-delay: 0.6s; }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>

<body>
    <!-- Floating Elements -->
    <div class="floating-element">
        <i class="fas fa-coffee" style="font-size: 2rem; color: var(--accent-color);"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-cookie-bite" style="font-size: 1.5rem; color: var(--secondary-color);"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-leaf" style="font-size: 1.8rem; color: var(--accent-color);"></i>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="row g-0 h-100">
                <!-- Brand Side -->
                <div class="col-lg-5">
                    <div class="brand-side h-100">
                        <div class="brand-content">
                            <div class="brand-logo fade-in">
                                <i class="fas fa-coffee"></i>
                            </div>
                            <h1 class="brand-title fade-in delay-1">Café de Flour</h1>
                            <p class="brand-subtitle fade-in delay-2">
                                Welcome back to your administration portal. 
                                Where warmth meets efficiency in café management.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Side -->
                <div class="col-lg-7">
                    <div class="form-side h-100">
                        <div class="form-header fade-in delay-1">
                            <h2 class="form-title">Admin Login</h2>
                            <p class="form-subtitle">Please sign in to access the dashboard</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="error-message fade-in delay-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/website/purple-free/dist/index.php" id="loginForm" class="fade-in delay-2">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <div class="input-wrapper">
                                    <input 
                                        type="text" 
                                        class="form-control-modern" 
                                        name="nama_kasir" 
                                        placeholder="Enter your username"
                                        value="<?php echo htmlspecialchars($nama_kasir); ?>"
                                        required
                                        autocomplete="username"
                                    >
                                    <i class="fas fa-user input-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <div class="input-wrapper">
                                    <input 
                                        type="password" 
                                        class="form-control-modern" 
                                        name="password" 
                                        placeholder="Enter your password"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <i class="fas fa-lock input-icon"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn-login" id="loginBtn">
                                <span class="btn-text">Sign In</span>
                            </button>
                        </form>

                        <div class="login-footer fade-in delay-3">
                            <p class="footer-text">
                                Need help? <a href="#" class="footer-link">Contact Support</a>
                            </p>
                            <p class="footer-text mt-2">
                                <i class="fas fa-shield-alt me-1"></i>
                                Secure login protected by encryption
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });

        // Form handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            
            // Add loading state
            loginBtn.classList.add('loading');
            btnText.textContent = 'Signing in...';
            
            // Add a small delay to show the loading state
            setTimeout(() => {
                // The form will submit normally after this delay
            }, 500);
        });

        // Input focus effects
        document.querySelectorAll('.form-control-modern').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });

        // Password visibility toggle (optional enhancement)
        function togglePasswordVisibility() {
            const passwordInput = document.querySelector('input[name="password"]');
            const passwordIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Add subtle animations to floating elements
        document.addEventListener('DOMContentLoaded', function() {
            const floatingElements = document.querySelectorAll('.floating-element');
            
            floatingElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 2}s`;
            });
        });

        // Add ripple effect to login button
        document.querySelector('.btn-login').addEventListener('click', function(e) {
            const button = e.currentTarget;
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });

        // Add CSS for ripple effect
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>