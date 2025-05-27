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

// Check for order success message from simpan-web.php
if (isset($_SESSION['member_order_success'])) {
    $success_message = $_SESSION['member_order_success'];
    unset($_SESSION['member_order_success']);
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

// Get recent transactions (using nama_pelanggan to match member name)
$transactions = [];
try {
    $member_identifier = $member['nama_member'] . " (Member #" . $member['id_member'] . ")";
    $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE nama_pelanggan LIKE ? ORDER BY tanggal DESC, waktu DESC LIMIT 10");
    $stmt->execute(["%Member #" . $member['id_member'] . "%"]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Transactions table might not exist or have different structure
}

// Get menu items by category
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT DISTINCT kategori FROM menu ORDER BY kategori");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $categories = [];
}

// Get all menu items
$menuItems = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM menu ORDER BY kategori, nama_menu");
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $menuItems = [];
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
        try {
            // Deduct points from member account
            $stmt = $pdo->prepare("UPDATE special_members SET poin = poin - ? WHERE id_member = ?");
            $stmt->execute([$reward[0]['points'], $member['id_member']]);
            
            // Refresh member data
            $stmt = $pdo->prepare("SELECT * FROM special_members WHERE id_member = ?");
            $stmt->execute([$_SESSION['member_id']]);
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $success_message = "Successfully redeemed: " . $reward[0]['name'] . " for " . $reward[0]['points'] . " points!";
        } catch (Exception $e) {
            $error_message = "Failed to redeem reward: " . $e->getMessage();
        }
    } else {
        $error_message = "Insufficient points for this reward.";
    }
}

// Handle order submission - REDIRECT TO SIMPAN-WEB.PHP
if ($_POST && isset($_POST['submit_order'])) {
    try {
        // Decode order items from JSON
        $orderItemsJson = $_POST['order_items'] ?? '';
        if (empty($orderItemsJson)) {
            throw new Exception("No order items found");
        }
        
        $orderItems = json_decode($orderItemsJson, true);
        if (!$orderItems || !is_array($orderItems)) {
            throw new Exception("Invalid order data");
        }
        
        // Get payment amount
        $payment_amount = (float)($_POST['payment_amount'] ?? 0);
        if ($payment_amount <= 0) {
            throw new Exception("Please enter a valid payment amount");
        }
        
        // Check if member discount should be applied
        $apply_discount = isset($_POST['apply_member_discount']) && $_POST['apply_member_discount'] === '1';
        
        // Calculate total for member benefits
        $total = 0;
        $menu_array = [];
        
        foreach ($orderItems as $item) {
            $itemId = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            
            if ($itemId <= 0 || $quantity <= 0) {
                continue;
            }
            
            // Get menu item details
            $stmt = $pdo->prepare("SELECT harga, stok FROM menu WHERE id_menu = ?");
            $stmt->execute([$itemId]);
            $menuItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$menuItem) {
                throw new Exception("Menu item not found: ID $itemId");
            }
            
            if ($menuItem['stok'] < $quantity) {
                throw new Exception("Insufficient stock for item ID: $itemId");
            }
            
            $total += $menuItem['harga'] * $quantity;
            
            // Format for simpan-web.php
            $menu_array[] = [
                'id_menu' => $itemId,
                'jumlah' => $quantity
            ];
        }
        
        if (empty($menu_array)) {
            throw new Exception("No valid items in order");
        }
        
        // Apply member discount only if selected (5% for all members)
        $discount = $apply_discount ? ($total * 0.05) : 0;
        $final_total = $total - $discount;
        
        // Check if payment amount is sufficient
        if ($payment_amount < $final_total) {
            throw new Exception("Payment amount (Rp " . number_format($payment_amount) . ") is insufficient. Total amount: Rp " . number_format($final_total));
        }
        
        // Create member identifier in customer name
        $customer_name = $member['nama_member'] . " (Member #" . $member['id_member'] . ")";
        
        // Prepare notes
        $notes = $_POST['notes'] ?? '';
        if ($apply_discount) {
            $notes .= " | Member Discount Applied: -Rp" . number_format($discount);
        } else {
            $notes .= " | Member chose not to use discount";
        }
        
        // Store order data in session for simpan-web.php
        $_SESSION['member_order_data'] = [
            'menu' => $menu_array,
            'nama_pelanggan' => $customer_name,
            'lokasi' => $_POST['service_type'] ?? 'Dine In',
            'catatan' => $notes,
            'metode' => $_POST['payment_method'] ?? 'Cash',
            'bayar' => $payment_amount,
            'id_pelanggan' => null, // Web customer
            'kode_promo' => '', // No promo code for member orders
            'is_member_order' => true,
            'member_id' => $member['id_member'],
            'member_discount' => $discount,
            'original_total' => $total,
            'apply_discount' => $apply_discount
        ];
        
        // Redirect to simpan-web.php to process the order
        header('Location: /website/purple-free/dist/backend/transaksi/simpan-web.php');
        exit;
        
    } catch (Exception $e) {
        $error_message = "Failed to place order: " . $e->getMessage();
    }
}

// Handle order tracking
$tracking_result = null;
if ($_POST && isset($_POST['track_order'])) {
    $tracking_code = trim($_POST['tracking_code'] ?? '');
    if (!empty($tracking_code)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE kode_transaksi = ?");
            $stmt->execute([$tracking_code]);
            $tracking_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tracking_result) {
                $tracking_error = "Order not found with code: " . htmlspecialchars($tracking_code);
            }
        } catch (Exception $e) {
            $tracking_error = "Error tracking order: " . $e->getMessage();
        }
    } else {
        $tracking_error = "Please enter a tracking code";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--soft-beige);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--warm-white);
            border-right: 1px solid var(--light-brown);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: var(--shadow-medium);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--light-brown);
            background: linear-gradient(135deg, var(--coffee-dark), var(--mocha));
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-white);
            font-family: 'Playfair Display', serif;
        }

        .logo i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--gold), var(--copper));
            color: var(--text-white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            padding: 20px 24px;
            border-bottom: 1px solid var(--light-brown);
            background: var(--cream);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 12px;
            border: 3px solid var(--gold);
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
            font-family: 'Playfair Display', serif;
        }

        .user-tier {
            font-size: 14px;
            color: var(--text-secondary);
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
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius);
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(135deg, var(--gold-light), var(--cream));
            color: var(--coffee-dark);
            transform: translateX(4px);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            background: var(--soft-beige);
        }

        .top-bar {
            background: var(--warm-white);
            padding: 16px 32px;
            border-bottom: 1px solid var(--light-brown);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-soft);
        }

        .search-container {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid var(--light-brown);
            border-radius: var(--radius);
            font-size: 14px;
            background: var(--cream);
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--gold);
            background: var(--warm-white);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .notification-btn {
            position: relative;
            padding: 8px;
            color: var(--text-secondary);
            background: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background: var(--cream);
            color: var(--gold);
        }

        .logout-btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--coffee-dark), var(--mocha));
            color: var(--text-white);
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, var(--espresso), var(--coffee-dark));
            transform: translateY(-1px);
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
            color: var(--text-primary);
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
        }

        .page-subtitle {
            color: var(--text-secondary);
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
            background: var(--warm-white);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--light-brown);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-soft);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-medium);
            border-color: var(--gold);
            transform: translateY(-2px);
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
            color: var(--text-secondary);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-white);
        }

        .stat-icon.points { background: linear-gradient(135deg, var(--gold), var(--copper)); }
        .stat-icon.spent { background: linear-gradient(135deg, var(--mocha), var(--coffee-dark)); }
        .stat-icon.tier { background: linear-gradient(135deg, var(--coffee-medium), var(--espresso)); }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
            font-family: 'Playfair Display', serif;
        }

        .stat-change {
            font-size: 14px;
            color: var(--success);
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
            background: var(--warm-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--light-brown);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .card-header {
            padding: 24px;
            border-bottom: 1px solid var(--light-brown);
            background: var(--cream);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Playfair Display', serif;
        }

        .card-content {
            padding: 24px;
        }

        /* Menu Section Styles */
        .menu-categories {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 16px;
            border: 2px solid var(--light-brown);
            background: var(--warm-white);
            color: var(--text-secondary);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .category-btn.active,
        .category-btn:hover {
            background: linear-gradient(135deg, var(--gold), var(--copper));
            color: var(--text-white);
            border-color: var(--gold);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .menu-item {
            background: var(--warm-white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--light-brown);
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
        }

        .menu-item:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-medium);
            border-color: var(--gold);
        }

        .menu-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-info {
            padding: 20px;
        }

        .menu-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
        }

        .menu-description {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .menu-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-price {
            font-size: 18px;
            font-weight: 700;
            color: var(--gold);
            font-family: 'Playfair Display', serif;
        }

        .add-to-cart-btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--coffee-dark), var(--mocha));
            color: var(--text-white);
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, var(--espresso), var(--coffee-dark));
            transform: translateY(-1px);
        }

        .add-to-cart-btn:disabled {
            background: var(--light-brown);
            color: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }

        .stock-info {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
        }

        .stock-low {
            color: var(--error);
        }

        /* Cart Styles */
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--light-brown);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .cart-item-price {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-btn {
            width: 24px;
            height: 24px;
            border: 1px solid var(--light-brown);
            background: var(--warm-white);
            color: var(--text-secondary);
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .quantity-btn:hover {
            background: var(--gold);
            color: var(--text-white);
            border-color: var(--gold);
        }

        .quantity-display {
            min-width: 30px;
            text-align: center;
            font-weight: 600;
        }

        .remove-item-btn {
            color: var(--error);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }

        .remove-item-btn:hover {
            background: rgba(211, 47, 47, 0.1);
        }

        .cart-total {
            padding: 16px 0;
            border-top: 2px solid var(--light-brown);
            margin-top: 16px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .total-label {
            color: var(--text-secondary);
        }

        .total-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .final-total {
            font-size: 18px;
            font-weight: 700;
            color: var(--gold);
            font-family: 'Playfair Display', serif;
        }

        .checkout-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--gold), var(--copper));
            color: var(--text-white);
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 16px;
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, var(--copper), var(--gold));
            transform: translateY(-1px);
        }

        .checkout-btn:disabled {
            background: var(--light-brown);
            color: var(--text-light);
            cursor: not-allowed;
            transform: none;
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
        .tier-silver { background: linear-gradient(135deg, #c0c0c0, #a8a8a8); color: var(--text-primary); }
        .tier-gold { background: linear-gradient(135deg, #ffd700, #f59e0b); color: var(--text-primary); }
        .tier-platinum { background: linear-gradient(135deg, #e5e4e2, #d1d5db); color: var(--text-primary); }

        .progress-container {
            margin-top: 24px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--light-brown);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold), var(--copper));
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
            border: 1px solid var(--light-brown);
            border-radius: var(--radius);
            transition: all 0.2s ease;
        }

        .reward-item:hover {
            border-color: var(--gold);
            background: var(--cream);
        }

        .reward-info h4 {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .reward-info p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .reward-points {
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .redeem-btn {
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--gold), var(--copper));
            color: var(--text-white);
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .redeem-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--copper), var(--gold));
        }

        .redeem-btn:disabled {
            background: var(--light-brown);
            color: var(--text-light);
            cursor: not-allowed;
        }

        /* Transactions */
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--light-brown);
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-info h4 {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .transaction-date {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .transaction-amount {
            text-align: right;
        }

        .amount {
            font-weight: 600;
            color: var(--text-primary);
        }

        .points-earned {
            font-size: 14px;
            color: var(--success);
        }

        /* Alert Messages */
        .alert {
            padding: 16px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fecaca;
            color: #dc2626;
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

            .menu-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Checkout Form Styles */
        .checkout-form {
            display: none;
            background: var(--warm-white);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--light-brown);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--light-brown);
            border-radius: var(--radius);
            background: var(--warm-white);
            color: var(--text-primary);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            padding: 12px;
            background: var(--cream);
            border-radius: var(--radius);
            border: 1px solid var(--light-brown);
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--gold);
        }

        .form-check-label {
            font-weight: 500;
            color: var(--text-primary);
            cursor: pointer;
        }

        .discount-info {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning);
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .status-processing {
            background: rgba(33, 150, 243, 0.1);
            color: #2196f3;
            border: 1px solid rgba(33, 150, 243, 0.3);
        }

        .status-ready {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .status-completed {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        /* Tracking Section */
        .tracking-form {
            background: var(--warm-white);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--light-brown);
            margin-bottom: 24px;
        }

        .tracking-result {
            background: var(--warm-white);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--light-brown);
        }

        .tracking-info {
            display: grid;
            gap: 16px;
        }

        .tracking-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--light-brown);
        }

        .tracking-row:last-child {
            border-bottom: none;
        }

        .tracking-label {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .tracking-value {
            color: var(--text-primary);
            text-align: right;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
                    <span>Café de Flour</span>
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
                    <a href="#menu" class="nav-link" onclick="showSection('menu')">
                        <i class="fas fa-utensils"></i>
                        <span>Order Menu</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#tracking" class="nav-link" onclick="showSection('tracking')">
                        <i class="fas fa-search"></i>
                        <span>Track Order</span>
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
                    <input type="text" class="search-input" placeholder="Search menu, transactions, rewards..." id="searchInput">
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
                                            <div style="text-align: center; margin-top: 12px; color: var(--text-secondary); font-size: 14px;">
                                                Rp <?php echo number_format($tierProgress['remaining']); ?> more to reach <?php echo $tierProgress['next_tier']; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p style="color: var(--text-secondary); margin-top: 16px;">
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
                                    <button class="redeem-btn" onclick="showSection('menu')" style="width: 100%; padding: 16px;">
                                        <i class="fas fa-utensils"></i> Order Menu
                                    </button>
                                    <button class="redeem-btn" onclick="showSection('tracking')" style="width: 100%; padding: 16px; background: linear-gradient(135deg, #2196f3, #1976d2);">
                                        <i class="fas fa-search"></i> Track Order
                                    </button>
                                    <button class="redeem-btn" onclick="showSection('rewards')" style="width: 100%; padding: 16px; background: linear-gradient(135deg, var(--mocha), var(--coffee-dark));">
                                        <i class="fas fa-gift"></i> Redeem Points
                                    </button>
                                    <button class="redeem-btn" onclick="showSection('transactions')" style="width: 100%; padding: 16px; background: linear-gradient(135deg, var(--success), #059669);">
                                        <i class="fas fa-history"></i> View History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Section -->
                <div id="menu-section" class="content-section" style="display: none;">
                    <div class="page-header">
                        <h1 class="page-title">Order Menu</h1>
                        <p class="page-subtitle">Browse our delicious menu and place your order with member benefits.</p>
                    </div>

                    <div class="content-grid">
                        <!-- Menu Items -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-utensils"></i>
                                    Our Menu
                                </h2>
                            </div>
                            <div class="card-content">
                                <!-- Category Filter -->
                                <div class="menu-categories">
                                    <button class="category-btn active" onclick="filterMenu('all')">All Items</button>
                                    <?php foreach ($categories as $category): ?>
                                        <button class="category-btn" onclick="filterMenu('<?php echo strtolower($category); ?>')">
                                            <?php echo ucfirst($category); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Menu Grid -->
                                <div class="menu-grid" id="menuGrid">
                                    <?php foreach ($menuItems as $item): ?>
                                        <div class="menu-item" data-category="<?php echo strtolower($item['kategori']); ?>">
                                            <img src="/website/assets/img/newmenu/<?php echo $item['gambar']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['nama_menu']); ?>" 
                                                 class="menu-image"
                                                 onerror="this.src='https://via.placeholder.com/300x200/8b4513/ffffff?text=<?php echo urlencode($item['nama_menu']); ?>'">
                                            <div class="menu-info">
                                                <h3 class="menu-name"><?php echo htmlspecialchars($item['nama_menu']); ?></h3>
                                                <p class="menu-description"><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                                                <div class="menu-footer">
                                                    <div>
                                                        <div class="menu-price">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></div>
                                                        <div class="stock-info <?php echo $item['stok'] <= 5 ? 'stock-low' : ''; ?>">
                                                            Stock: <?php echo $item['stok']; ?>
                                                        </div>
                                                    </div>
                                                    <button class="add-to-cart-btn" 
                                                            onclick="addToCart(<?php echo $item['id_menu']; ?>, '<?php echo htmlspecialchars($item['nama_menu']); ?>', <?php echo $item['harga']; ?>, <?php echo $item['stok']; ?>)"
                                                            <?php echo $item['stok'] <= 0 ? 'disabled' : ''; ?>>
                                                        <?php echo $item['stok'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Shopping Cart -->
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-shopping-cart"></i>
                                    Your Order (<span id="cartCount">0</span>)
                                </h2>
                            </div>
                            <div class="card-content">
                                <div id="cartItems">
                                    <p style="text-align: center; color: var(--text-light); padding: 20px;">
                                        Your cart is empty
                                    </p>
                                </div>
                                
                                <div id="cartTotal" style="display: none;">
                                    <div class="cart-total">
                                        <div class="total-row">
                                            <span class="total-label">Subtotal:</span>
                                            <span class="total-value" id="subtotal">Rp 0</span>
                                        </div>
                                        <div class="total-row" id="discountRow" style="display: none;">
                                            <span class="total-label">Member Discount (5%):</span>
                                            <span class="total-value" id="discount" style="color: var(--success);">-Rp 0</span>
                                        </div>
                                        <div class="total-row">
                                            <span class="total-label final-total">Total:</span>
                                            <span class="total-value final-total" id="finalTotal">Rp 0</span>
                                        </div>
                                    </div>
                                    
                                    <button class="checkout-btn" onclick="showCheckoutForm()">
                                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                                    </button>
                                </div>

                                <!-- Checkout Form -->
                                <div class="checkout-form" id="checkoutForm">
                                    <h3 style="margin-bottom: 16px; color: var(--text-primary);">Order Details</h3>
                                    <form id="orderForm" method="POST">
                                        <input type="hidden" name="submit_order" value="1">
                                        <input type="hidden" name="order_items" id="orderItemsInput">
                                        
                                        <!-- Member Discount Option -->
                                        <div class="form-check">
                                            <input type="checkbox" id="applyDiscount" name="apply_member_discount" value="1" checked onchange="updateCartDisplay()">
                                            <label for="applyDiscount" class="form-check-label">
                                                Apply Member Discount (5%)
                                                <div class="discount-info">Save 5% on your total order as a member benefit</div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label class="form-label">Payment Amount *</label>
                                                <input type="number" name="payment_amount" id="paymentAmount" class="form-control" 
                                                       placeholder="Enter payment amount" min="0" step="1000" required>
                                                <small style="color: var(--text-secondary); font-size: 12px;">
                                                    Minimum: <span id="minimumPayment">Rp 0</span>
                                                </small>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Payment Method</label>
                                                <select name="payment_method" class="form-control" required>
                                                    <option value="">Choose method</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="QRIS">QRIS</option>
                                                    <option value="Debit">Debit Card</option>
                                                    <option value="Credit">Credit Card</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Service Type</label>
                                            <select name="service_type" class="form-control" required>
                                                <option value="Dine In">Dine In</option>
                                                <option value="Take Away">Take Away</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="form-label">Special Notes (Optional)</label>
                                            <textarea name="notes" class="form-control" rows="3" placeholder="Any special requests or notes..."></textarea>
                                        </div>
                                        
                                        <div style="display: flex; gap: 12px;">
                                            <button type="button" class="checkout-btn" onclick="hideCheckoutForm()" style="background: var(--light-brown); color: var(--text-secondary);">
                                                Cancel
                                            </button>
                                            <button type="submit" class="checkout-btn" style="flex: 1;">
                                                <i class="fas fa-check"></i> Place Order
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Tracking Section -->
                <div id="tracking-section" class="content-section" style="display: none;">
                    <div class="page-header">
                        <h1 class="page-title">Track Your Order</h1>
                        <p class="page-subtitle">Enter your transaction code to view order status and details.</p>
                    </div>

                    <div class="tracking-form">
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">Transaction Code</label>
                                <input type="text" name="tracking_code" class="form-control" 
                                       placeholder="Enter your transaction code (e.g., TRX001234567890)" 
                                       value="<?php echo htmlspecialchars($_POST['tracking_code'] ?? ''); ?>" required>
                            </div>
                            <button type="submit" name="track_order" class="checkout-btn" style="width: auto;">
                                <i class="fas fa-search"></i> Track Order
                            </button>
                        </form>
                    </div>

                    <?php if (isset($tracking_error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $tracking_error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($tracking_result): ?>
                        <div class="tracking-result">
                            <div class="card-header" style="margin: -24px -24px 24px -24px;">
                                <h3 class="card-title">
                                    <i class="fas fa-receipt"></i>
                                    Order Details
                                </h3>
                            </div>
                            
                            <div class="tracking-info">
                                <div class="tracking-row">
                                    <span class="tracking-label">Transaction Code:</span>
                                    <span class="tracking-value"><?php echo htmlspecialchars($tracking_result['kode_transaksi']); ?></span>
                                </div>
                                <div class="tracking-row">
                                    <span class="tracking-label">Customer:</span>
                                    <span class="tracking-value"><?php echo htmlspecialchars($tracking_result['nama_pelanggan']); ?></span>
                                </div>
                                <div class="tracking-row">
                                    <span class="tracking-label">Date & Time:</span>
                                    <span class="tracking-value"><?php echo htmlspecialchars($tracking_result['tanggal']); ?> at <?php echo htmlspecialchars($tracking_result['waktu']); ?></span>
                                </div>
                                <div class="tracking-row">
                                    <span class="tracking-label">Location:</span>
                                    <span class="tracking-value"><?php echo htmlspecialchars($tracking_result['lokasi']); ?></span>
                                </div>
                                <div class="tracking-row">
                                    <span class="tracking-label">Payment Method:</span>
                                    <span class="tracking-value"><?php echo htmlspecialchars($tracking_result['metode_pembayaran']); ?></span>
                                </div>
                                <div class="tracking-row">
                                    <span class="tracking-label">Total Amount:</span>
                                    <span class="tracking-value">Rp <?php echo number_format($tracking_result['total_harga']); ?></span>
                                </div>
                                <div class="tracking-row">
                                    <span class="tracking-label">Status:</span>
                                    <span class="tracking-value">
                                        <?php
                                        $status = strtolower(trim($tracking_result['status']));
                                        $statusClass = 'status-processing';
                                        
                                        if (in_array($status, ['belum diproses', 'pending', 'menunggu'])) {
                                            $statusClass = 'status-pending';
                                        } elseif (in_array($status, ['sedang disiapkan', 'processing'])) {
                                            $statusClass = 'status-processing';
                                        } elseif (in_array($status, ['siap diambil', 'ready'])) {
                                            $statusClass = 'status-ready';
                                        } elseif (in_array($status, ['selesai', 'completed', 'done'])) {
                                            $statusClass = 'status-completed';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($tracking_result['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if (!empty($tracking_result['catatan'])): ?>
                                <div class="tracking-row">
                                    <span class="tracking-label">Notes:</span>
                                    <span class="tracking-value"><?php echo htmlspecialchars($tracking_result['catatan']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
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
                                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                    <i class="fas fa-receipt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                    <p>No transactions found.</p>
                                    <p style="font-size: 14px; margin-top: 8px;">Start shopping to see your purchase history here!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                <div class="transaction-item">
                                    <div class="transaction-info">
                                        <h4><?php echo htmlspecialchars($transaction['kode_transaksi']); ?></h4>
                                        <div class="transaction-date">
                                            <?php echo date('M j, Y - g:i A', strtotime($transaction['tanggal'] . ' ' . $transaction['waktu'])); ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-light); margin-top: 4px;">
                                            <?php echo htmlspecialchars($transaction['lokasi']); ?> • <?php echo htmlspecialchars($transaction['metode_pembayaran']); ?>
                                            <span class="status-badge <?php 
                                                $status = strtolower(trim($transaction['status']));
                                                if (in_array($status, ['belum diproses', 'pending'])) echo 'status-pending';
                                                elseif (in_array($status, ['sedang disiapkan'])) echo 'status-processing';
                                                elseif (in_array($status, ['siap diambil'])) echo 'status-ready';
                                                elseif (in_array($status, ['selesai'])) echo 'status-completed';
                                                else echo 'status-processing';
                                            ?>" style="margin-left: 8px;">
                                                <?php echo htmlspecialchars($transaction['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="transaction-amount">
                                        <div class="amount">Rp <?php echo number_format($transaction['total_harga']); ?></div>
                                        <div class="points-earned">+<?php echo floor($transaction['total_harga'] / 1000); ?> points</div>
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
                                         alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--gold);">
                                    <div>
                                        <h3 style="margin-bottom: 8px;"><?php echo htmlspecialchars($member['nama_member']); ?></h3>
                                        <p style="color: var(--text-secondary);">Member ID: #<?php echo $member['id_member']; ?></p>
                                    </div>
                                </div>
                                
                                <div style="display: grid; gap: 16px;">
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid var(--light-brown);">
                                        <span style="color: var(--text-secondary);">Phone Number</span>
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($member['nomor_hp']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid var(--light-brown);">
                                        <span style="color: var(--text-secondary);">Membership Tier</span>
                                        <span class="tier-badge tier-<?php echo strtolower($member['tingkatan']); ?>" style="font-size: 14px; padding: 4px 12px;">
                                            <?php echo $member['tingkatan']; ?>
                                        </span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid var(--light-brown);">
                                        <span style="color: var(--text-secondary);">Total Points</span>
                                        <span style="font-weight: 600; color: var(--gold);"><?php echo number_format($member['poin']); ?> points</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 16px 0;">
                                        <span style="color: var(--text-secondary);">Total Spent</span>
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
        // Cart functionality
        let cart = [];
        let cartTotal = 0;

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

        // Menu filtering
        function filterMenu(category) {
            const menuItems = document.querySelectorAll('.menu-item');
            const categoryBtns = document.querySelectorAll('.category-btn');

            // Update active button
            categoryBtns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Filter items
            menuItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Add to cart functionality
        function addToCart(id, name, price, stock) {
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity++;
                } else {
                    alert('Cannot add more items. Stock limit reached.');
                    return;
                }
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    stock: stock
                });
            }
            
            updateCartDisplay();
        }

        // Update cart display
        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartCount = document.getElementById('cartCount');
            const cartTotal = document.getElementById('cartTotal');
            const discountRow = document.getElementById('discountRow');
            const applyDiscount = document.getElementById('applyDiscount');
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p style="text-align: center; color: var(--text-light); padding: 20px;">Your cart is empty</p>';
                cartCount.textContent = '0';
                cartTotal.style.display = 'none';
                return;
            }
            
            let itemsHtml = '';
            let subtotal = 0;
            let totalItems = 0;
            
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                totalItems += item.quantity;
                
                itemsHtml += `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">Rp ${item.price.toLocaleString('id-ID')}</div>
                        </div>
                        <div class="cart-item-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span class="quantity-display">${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)" ${item.quantity >= item.stock ? 'disabled' : ''}>+</button>
                            <button class="remove-item-btn" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            cartItems.innerHTML = itemsHtml;
            cartCount.textContent = totalItems;
            
            // Calculate discount (5% member discount if checkbox is checked)
            const shouldApplyDiscount = applyDiscount && applyDiscount.checked;
            const discount = shouldApplyDiscount ? Math.floor(subtotal * 0.05) : 0;
            const finalTotal = subtotal - discount;
            
            document.getElementById('subtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('discount').textContent = `-Rp ${discount.toLocaleString('id-ID')}`;
            document.getElementById('finalTotal').textContent = `Rp ${finalTotal.toLocaleString('id-ID')}`;
            
            // Show/hide discount row
            if (shouldApplyDiscount && discount > 0) {
                discountRow.style.display = 'flex';
            } else {
                discountRow.style.display = 'none';
            }
            
            // Update minimum payment amount
            const minimumPayment = document.getElementById('minimumPayment');
            const paymentAmount = document.getElementById('paymentAmount');
            if (minimumPayment) {
                minimumPayment.textContent = `Rp ${finalTotal.toLocaleString('id-ID')}`;
            }
            if (paymentAmount) {
                paymentAmount.min = finalTotal;
                paymentAmount.value = finalTotal;
            }
            
            cartTotal.style.display = 'block';
        }

        // Update quantity
        function updateQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            if (!item) return;
            
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                removeFromCart(id);
            } else if (newQuantity <= item.stock) {
                item.quantity = newQuantity;
                updateCartDisplay();
            } else {
                alert('Cannot add more items. Stock limit reached.');
            }
        }

        // Remove from cart
        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCartDisplay();
        }

        // Show checkout form
        function showCheckoutForm() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Ensure the cart data is in the correct format for PHP
            const orderData = cart.map(item => ({
                id: parseInt(item.id),
                quantity: parseInt(item.quantity),
                name: item.name,
                price: item.price
            }));
            
            document.getElementById('checkoutForm').style.display = 'block';
            document.getElementById('orderItemsInput').value = JSON.stringify(orderData);
            
            // Update payment amount with current total
            updateCartDisplay();
        }

        // Hide checkout form
        function hideCheckoutForm() {
            document.getElementById('checkoutForm').style.display = 'none';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Search in menu items
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });

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

            // Animate menu items on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all menu items for animation
            document.querySelectorAll('.menu-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(item);
            });
        });

        // Form submission handling
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                alert('Your cart is empty!');
                return;
            }
            
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value);
            const applyDiscount = document.getElementById('applyDiscount').checked;
            
            // Calculate total
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            const discount = applyDiscount ? Math.floor(subtotal * 0.05) : 0;
            const finalTotal = subtotal - discount;
            
            if (paymentAmount < finalTotal) {
                e.preventDefault();
                alert(`Payment amount (Rp ${paymentAmount.toLocaleString('id-ID')}) is insufficient. Total amount: Rp ${finalTotal.toLocaleString('id-ID')}`);
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Re-enable after 3 seconds if form doesn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                alert.style.transition = 'all 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Add mobile menu toggle for responsive design
        if (window.innerWidth <= 768) {
            const topBar = document.querySelector('.top-bar');
            const menuToggle = document.createElement('button');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.className = 'notification-btn';
            menuToggle.onclick = toggleSidebar;
            topBar.insertBefore(menuToggle, topBar.firstChild);
        }
    </script>
</body>
</html>
