<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit;
}

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

// Get member data
$stmt = $pdo->prepare("SELECT * FROM special_members WHERE id_member = ?");
$stmt->execute([$_SESSION['member_id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get recent transactions
$transactions = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id_member = ? ORDER BY transaction_date DESC LIMIT 10");
    $stmt->execute([$_SESSION['member_id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Transactions table might not exist
}

// Get available rewards for point redemption
$rewards = [
    ['id' => 1, 'name' => '10% Discount Voucher', 'points' => 100, 'description' => 'Get 10% off your next purchase'],
    ['id' => 2, 'name' => '15% Discount Voucher', 'points' => 200, 'description' => 'Get 15% off your next purchase'],
    ['id' => 3, 'name' => '20% Discount Voucher', 'points' => 300, 'description' => 'Get 20% off your next purchase'],
    ['id' => 4, 'name' => 'Free Coffee', 'points' => 150, 'description' => 'Get a free coffee of your choice'],
    ['id' => 5, 'name' => 'Free Pastry', 'points' => 120, 'description' => 'Get a free pastry from our selection'],
];

// Calculate tier progress
function getTierProgress($tier, $totalTransaksi) {
    $tiers = [
        'Bronze' => ['min' => 0, 'max' => 100000, 'next' => 'Silver'],
        'Silver' => ['min' => 100000, 'max' => 500000, 'next' => 'Gold'],
        'Gold' => ['min' => 500000, 'max' => 1000000, 'next' => 'Platinum'],
        'Platinum' => ['min' => 1000000, 'max' => 1000000, 'next' => 'Platinum']
    ];
    
    $current = $tiers[$tier];
    if ($tier === 'Platinum') {
        return ['progress' => 100, 'remaining' => 0, 'next_tier' => 'Platinum'];
    }
    
    $progress = (($totalTransaksi - $current['min']) / ($current['max'] - $current['min'])) * 100;
    $remaining = $current['max'] - $totalTransaksi;
    
    return [
        'progress' => max(0, min(100, $progress)),
        'remaining' => max(0, $remaining),
        'next_tier' => $current['next']
    ];
}

$tierProgress = getTierProgress($member['tingkatan'], $member['total_transaksi']);

// Handle point redemption
if ($_POST && isset($_POST['redeem_reward'])) {
    $reward_id = (int)$_POST['reward_id'];
    $reward = array_filter($rewards, function($r) use ($reward_id) {
        return $r['id'] === $reward_id;
    });
    
    if (!empty($reward) && $member['poin'] >= $reward[0]['points']) {
        // Deduct points (in real app, you'd update the database)
        $success_message = "Successfully redeemed: " . $reward[0]['name'];
    } else {
        $error_message = "Insufficient points for this reward.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - Cafe de Flour</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .logo i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 12px;
            border: 3px solid #e2e8f0;
        }

        .user-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .user-tier {
            font-size: 14px;
            color: #64748b;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            margin: 4px 16px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #f1f5f9;
            color: #3b82f6;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            background: #f8fafc;
        }

        .top-bar {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .search-container {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .notification-btn {
            position: relative;
            padding: 8px;
            color: #64748b;
            background: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background: #f1f5f9;
            color: #3b82f6;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        /* Content Area */
        .content-area {
            padding: 32px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 16px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .stat-title {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-icon.points { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .stat-icon.spent { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon.tier { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .stat-change {
            font-size: 14px;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-content {
            padding: 24px;
        }

        /* Tier Progress */
        .tier-display {
            text-align: center;
            margin-bottom: 32px;
        }

        .tier-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 16px;
        }

        .tier-bronze { background: linear-gradient(135deg, #cd7f32, #b8860b); color: white; }
        .tier-silver { background: linear-gradient(135deg, #c0c0c0, #a8a8a8); color: #1e293b; }
        .tier-gold { background: linear-gradient(135deg, #ffd700, #f59e0b); color: #1e293b; }
        .tier-platinum { background: linear-gradient(135deg, #e5e4e2, #d1d5db); color: #1e293b; }

        .progress-container {
            margin-top: 24px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: #64748b;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* Rewards Section */
        .rewards-grid {
            display: grid;
            gap: 16px;
        }

        .reward-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .reward-item:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .reward-info h4 {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .reward-info p {
            font-size: 14px;
            color: #64748b;
        }

        .reward-points {
            font-weight: 600;
            color: #f59e0b;
            margin-bottom: 8px;
        }

        .redeem-btn {
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .redeem-btn:hover:not(:disabled) {
            background: #2563eb;
        }

        .redeem-btn:disabled {
            background: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* Transactions */
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-info h4 {
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .transaction-date {
            font-size: 14px;
            color: #64748b;
        }

        .transaction-amount {
            text-align: right;
        }

        .amount {
            font-weight: 600;
            color: #1e293b;
        }

        .points-earned {
            font-size: 14px;
            color: #10b981;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                padding: 16px;
            }

            .content-area {
                padding: 16px;
            }
        }

        /* Alert Messages */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-coffee"></i>
                    <span>Cafe de Flour</span>
                </div>
            </div>

            <div class="user-profile">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo urlencode($member['nama_member']); ?>" 
                     alt="Avatar" class="user-avatar">
                <div class="user-name"><?php echo htmlspecialchars($member['nama_member']); ?></div>
                <div class="user-tier"><?php echo $member['tingkatan']; ?> Member</div>
            </div>

            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="#dashboard" class="nav-link active" onclick="showSection('dashboard')">
                        <i class="fas fa-chart-pie"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#rewards" class="nav-link" onclick="showSection('rewards')">
                        <i class="fas fa-gift"></i>
                        <span>Redeem Points</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#transactions" class="nav-link" onclick="showSection('transactions')">
                        <i class="fas fa-history"></i>
                        <span>Transaction History</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#profile" class="nav-link" onclick="showSection('profile')">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search transactions, rewards..." id="searchInput">
                </div>
                <div class="top-bar-actions">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                    </button>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="page-header">
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Welcome back! Here's your membership overview.</p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Points</div>
                                <div class="stat-icon points">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <div class="stat-value"><?php echo number_format($member['poin']); ?></div>
                            <div class="stat-change">
                                <i class="fas fa-arrow-up"></i>
                                Available for redemption
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Total Spent</div>
                                <div class="stat-icon spent">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="stat-value">Rp <?php echo number_format($member['total_transaksi']); ?></div>
                            <div class="stat-change">
                                <i class="fas fa-arrow-up"></i>
                                Lifetime purchases
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title">Current Tier</div>
                                <div class="stat-icon tier">
                                    <i class="fas fa-medal"></i>
                                </div>
                            </div>
                            <div class="stat-value"><?php echo $member['tingkatan']; ?></div>
                            <div class="stat-change">
                                <i class="fas fa-crown"></i>
                                Membership level
                            </div>
                        </div>
                    </div>

                    <!-- Content Grid -->
                    <div class="content-grid">
                        <!-- Tier Progress -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-trophy"></i>
                                    Membership Progress
                                </h2>
                            </div>
                            <div class="card-content">
                                <div class="tier-display">
                                    <div class="tier-badge tier-<?php echo strtolower($member['tingkatan']); ?>">
                                        <i class="fas fa-medal"></i>
                                        <?php echo $member['tingkatan']; ?> Member
                                    </div>
                                    
                                    <?php if ($member['tingkatan'] !== 'Platinum'): ?>
                                        <div class="progress-container">
                                            <div class="progress-label">
                                                <span>Progress to <?php echo $tierProgress['next_tier']; ?></span>
                                                <span><?php echo number_format($tierProgress['progress'], 1); ?>%</span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $tierProgress['progress']; ?>%"></div>
                                            </div>
                                            <div style="text-align: center; margin-top: 12px; color: #64748b; font-size: 14px;">
                                                Rp <?php echo number_format($tierProgress['remaining']); ?> more to reach <?php echo $tierProgress['next_tier']; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p style="color: #64748b; margin-top: 16px;">
                                            <i class="fas fa-crown"></i> You've reached the highest tier!
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-zap"></i>
                                    Quick Actions
                                </h2>
                            </div>
                            <div class="card-content">
                                <div style="display: grid; gap: 12px;">
                                    <button class="redeem-btn" onclick="showSection('rewards')" style="width: 100%; padding: 16px;">
                                        <i class="fas fa-gift"></i> Redeem Points
                                    </button>
                                    <button class="redeem-btn" onclick="showSection('transactions')" style="width: 100%; padding: 16px; background: #10b981;">
                                        <i class="fas fa-history"></i> View History
                                    </button>
                                    <button class="redeem-btn" onclick="showSection('profile')" style="width: 100%; padding: 16px; background: #8b5cf6;">
                                        <i class="fas fa-user"></i> Edit Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rewards Section -->
                <div id="rewards-section" class="content-section" style="display: none;">
                    <div class="page-header">
                        <h1 class="page-title">Redeem Points</h1>
                        <p class="page-subtitle">Exchange your points for amazing rewards and discounts.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-gift"></i>
                                Available Rewards
                            </h2>
                        </div>
                        <div class="card-content">
                            <div class="rewards-grid">
                                <?php foreach ($rewards as $reward): ?>
                                <div class="reward-item">
                                    <div class="reward-info">
                                        <h4><?php echo $reward['name']; ?></h4>
                                        <p><?php echo $reward['description']; ?></p>
                                        <div class="reward-points"><?php echo $reward['points']; ?> points</div>
                                    </div>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                        <button type="submit" name="redeem_reward" class="redeem-btn" 
                                                <?php echo ($member['poin'] < $reward['points']) ? 'disabled' : ''; ?>>
                                            <?php echo ($member['poin'] >= $reward['points']) ? 'Redeem' : 'Not enough points'; ?>
                                        </button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Section -->
                <div id="transactions-section" class="content-section" style="display: none;">
                    <div class="page-header">
                        <h1 class="page-title">Transaction History</h1>
                        <p class="page-subtitle">View your recent purchases and points earned.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-history"></i>
                                Recent Transactions
                            </h2>
                        </div>
                        <div class="card-content">
                            <?php if (empty($transactions)): ?>
                                <div style="text-align: center; padding: 40px; color: #64748b;">
                                    <i class="fas fa-receipt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                    <p>No transactions found.</p>
                                    <p style="font-size: 14px; margin-top: 8px;">Start shopping to see your purchase history here!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                <div class="transaction-item">
                                    <div class="transaction-info">
                                        <h4>Purchase #<?php echo $transaction['id_transaction']; ?></h4>
                                        <div class="transaction-date">
                                            <?php echo date('M j, Y - g:i A', strtotime($transaction['transaction_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="transaction-amount">
                                        <div class="amount">Rp <?php echo number_format($transaction['amount']); ?></div>
                                        <div class="points-earned">+<?php echo $transaction['points_earned']; ?> points</div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile-section" class="content-section" style="display: none;">
                    <div class="page-header">
                        <h1 class="page-title">Profile</h1>
                        <p class="page-subtitle">Manage your account information and preferences.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user"></i>
                                Member Information
                            </h2>
                        </div>
                        <div class="card-content">
                            <div style="display: grid; gap: 24px;">
                                <div style="display: flex; align-items: center; gap: 24px;">
                                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo urlencode($member['nama_member']); ?>" 
                                         alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid #e2e8f0;">
                                    <div>
                                        <h3 style="margin-bottom: 8px;"><?php echo htmlspecialchars($member['nama_member']); ?></h3>
                                        <p style="color: #64748b;">Member ID: #<?php echo $member['id_member']; ?></p>
                                    </div>
                                </div>
                                
                                <div style="display: grid; gap: 16px;">
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #64748b;">Phone Number</span>
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($member['nomor_hp']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #64748b;">Membership Tier</span>
                                        <span class="tier-badge tier-<?php echo strtolower($member['tingkatan']); ?>" style="font-size: 14px; padding: 4px 12px;">
                                            <?php echo $member['tingkatan']; ?>
                                        </span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #64748b;">Total Points</span>
                                        <span style="font-weight: 600; color: #f59e0b;"><?php echo number_format($member['poin']); ?> points</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0;">
                                        <span style="color: #64748b;">Total Spent</span>
                                        <span style="font-weight: 600;">Rp <?php echo number_format($member['total_transaksi']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Navigation functionality
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Show selected section
            document.getElementById(sectionName + '-section').style.display = 'block';

            // Update active nav link
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('.nav-link').classList.add('active');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Search in rewards
            const rewardItems = document.querySelectorAll('.reward-item');
            rewardItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });

            // Search in transactions
            const transactionItems = document.querySelectorAll('.transaction-item');
            transactionItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-open');
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bar
            const progressBar = document.querySelector('.progress-fill');
            if (progressBar) {
                const width = progressBar.style.width;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = width;
                }, 500);
            }

            // Animate stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>