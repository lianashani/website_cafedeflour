<?php
include '../config/koneksi.php';

$bukti = isset($_SESSION['bukti']) ? $_SESSION['bukti'] : null;
unset($_SESSION['bukti']); 

// Ambil menu terbaru (berdasarkan ID terbesar)
$menu_terbaru = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu DESC LIMIT 4");

// Ambil semua menu
$kategori_query = mysqli_query($conn, "SELECT DISTINCT kategori FROM menu");

// Ambil best seller berdasarkan qty terbanyak dari transaksi_detail
$best_seller = mysqli_query($conn, "
    SELECT m.*, SUM(td.jumlah) AS total_terjual
    FROM menu m
    JOIN transaksi_detail td ON m.id_menu = td.id_menu
    GROUP BY m.id_menu
    ORDER BY total_terjual DESC
    LIMIT 4
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Cafe de Flour - Where warmth meets flavor in every cup and bite" />
    <meta name="author" content="Cafe de Flour Team" />
    <title>Cafe de Flour - Premium Coffee & Bakery</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link rel="stylesheet" href="assets/css/stylebaru.css" />
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* Shopping Cart Styles */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            z-index: 1050;
            transition: right 0.3s ease;
            overflow-y: auto;
        }
        
        .cart-sidebar.open {
            right: 0;
        }
        
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .cart-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .cart-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background: #8B4513;
            color: white;
        }
        
        .cart-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .cart-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: #8B4513;
            font-weight: 500;
        }
        
        .cart-quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quantity-btn:hover {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }
        
        .cart-total {
            padding: 20px;
            border-top: 2px solid #8B4513;
            background: #f8f9fa;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .cart-button {
            position: relative;
            background: #8B4513;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .cart-button:hover {
            background: #6d3410;
            transform: translateY(-2px);
        }
        
        .add-to-cart-btn {
            background: #8B4513;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .add-to-cart-btn:hover {
            background: #6d3410;
            transform: translateY(-2px);
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-cart i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
        }
        
        /* Enhanced Order Button Styles */
        .order-button {
            background: linear-gradient(135deg, #8B4513, #A0522D);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        
        .order-button:hover {
            background: linear-gradient(135deg, #6d3410, #8B4513);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
        }
        
        /* Quick Add Animation */
        .quick-add-animation {
            animation: quickAdd 0.6s ease;
        }
        
        @keyframes quickAdd {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Cart notification */
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 1060;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .cart-notification.show {
            transform: translateX(0);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-coffee me-2"></i>Café de Flour
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#menu">Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team">Team</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reviews">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="#track">Track Order</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item">
                        <button class="cart-button" onclick="toggleCart()">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-badge" id="cartBadge">0</span>
                        </button>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>

    <!-- Shopping Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Your Cart</h5>
                <button class="btn btn-link text-white p-0" onclick="toggleCart()">
                    <i class="fas fa-times fa-lg"></i>
                </button>
            </div>
        </div>
        
        <div id="cartItems">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <small>Add some delicious items to get started!</small>
            </div>
        </div>
        
        <div class="cart-total" id="cartTotal" style="display: none;">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span id="cartSubtotal">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <strong>Total:</strong>
                <strong id="cartTotalAmount">Rp 0</strong>
            </div>
            <button class="btn btn-primary w-100" onclick="proceedToCheckout()">
                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
            </button>
            <button class="btn btn-outline-secondary w-100 mt-2" onclick="clearCart()">
                <i class="fas fa-trash me-2"></i>Clear Cart
            </button>
        </div>
    </div>

    <!-- Cart Notification -->
    <div class="cart-notification" id="cartNotification">
        <i class="fas fa-check-circle me-2"></i>
        <span id="notificationText">Item added to cart!</span>
    </div>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content" data-aos="fade-right">
                        <h1 class="hero-title">Where Warmth Meets Flavor</h1>
                        <p class="hero-subtitle">
                            Experience the perfect blend of artisanal coffee and freshly baked pastries 
                            in our cozy corner of Bandung. Every cup tells a story, every bite creates a memory.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="#menu" class="btn-primary-custom">
                                <i class="fas fa-utensils"></i>
                                Explore Menu
                            </a>
                            <a href="#about" class="btn-outline-custom" style="color: white;">
                                <i class="fas fa-play"></i>
                                Our Story
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="text-center">
                        <img src="/assets/img/about.png" alt="Coffee" class="img-fluid" style="max-width: 400px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Menu Section -->
    <section class="section bg-cream">
        <div class="container">
            <div data-aos="fade-up">
                <h2 class="section-title">Our Latest Creations</h2>
                <p class="section-subtitle">
                    Fresh from our kitchen, these new additions are crafted with love and the finest ingredients
                </p>
            </div>

            <div class="row g-4">
                <?php 
                $delay = 100;
                while ($menu = mysqli_fetch_assoc($menu_terbaru)) { 
                ?>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card-modern h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="assets/img/newmenu/<?php echo $menu['gambar']; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo $menu['nama_menu']; ?>"
                                 style="height: 250px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-primary px-3 py-2 rounded-pill">New!</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title font-display mb-2"><?php echo $menu['nama_menu']; ?></h5>
                            <p class="card-text text-muted small mb-3"><?php echo $menu['deskripsi']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                                <button class="add-to-cart-btn" 
                                        onclick="addToCart(<?php echo $menu['id_menu']; ?>, '<?php echo addslashes($menu['nama_menu']); ?>', <?php echo $menu['harga']; ?>, 'assets/img/newmenu/<?php echo $menu['gambar']; ?>', <?php echo $menu['stok']; ?>)"
                                        <?php echo $menu['stok'] <= 0 ? 'disabled' : ''; ?>>
                                    <?php echo $menu['stok'] <= 0 ? 'Out of Stock' : '<i class="fas fa-plus me-1"></i>Add to Cart'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $delay += 100;
                } 
                ?>
            </div>

            <div class="text-center mt-5" data-aos="fade-up">
                <a href="#menu" class="btn-outline-custom">
                    <i class="fas fa-arrow-down"></i>
                    See Full Menu
                </a>
            </div>
        </div>
    </section>

    <!-- Reviews Preview Section -->
    <section class="section" id="reviews">
        <div class="container">
            <div data-aos="fade-up">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">
                    Real stories from real people who've experienced the warmth of Café de Flour
                </p>
            </div>

            <div class="d-flex gap-4 overflow-auto pb-4" style="scroll-snap-type: x mandatory;">
                <?php
                $ulasan = mysqli_query($conn, "
                    SELECT * FROM review
                    WHERE parent_id IS NULL
                    ORDER BY tanggal DESC LIMIT 8
                ");

                while($r = mysqli_fetch_assoc($ulasan)) {
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= $i <= $r['rating'] 
                            ? '<i class="fas fa-star"></i>' 
                            : '<i class="far fa-star"></i>';
                    }

                    $avatar = 'https://ui-avatars.com/api/?name='.urlencode($r['nama_pelanggan']).'&background=random&size=100';
                    $foto = $r['foto'] ? '<img src="/website/assets/img/upload/review/'.$r['foto'].'" class="img-fluid rounded mt-3" style="max-height:120px;">' : '';

                    echo '
                    <div class="review-card flex-shrink-0" style="scroll-snap-align: start;">
                        <div class="d-flex align-items-center mb-3">
                            <img src="'.$avatar.'" alt="avatar" class="review-avatar me-3">
                            <div>
                                <h6 class="mb-1 font-weight-bold">'.htmlspecialchars($r['nama_pelanggan']).'</h6>
                                <div class="star-rating">'.$stars.'</div>
                                 <small class="text-muted">'.date('M d, Y', strtotime($r['tanggal'])).'</small>

                            </div>
                        </div>
                        <p class="text-muted mb-3 fst-italic">"'.htmlspecialchars($r['komentar']).'"</p>
                        '.$foto.'
                    </div>';
                }
                ?>
            </div>

            <div class="text-center mt-4" data-aos="fade-up">
                <a href="#review-form" class="btn-primary-custom me-3">
                    <i class="fas fa-star"></i>
                    Write a Review
                </a>
                <a href="../website/purple-free/dist/backend/review/semua-ulasan.php" class="btn-outline-custom">
                    View All Reviews
                </a>
            </div>
        </div>
    </section>

    <!-- Full Menu Section -->
    <section id="menu" class="section bg-cream">
        <div class="container">
            <div data-aos="fade-up">
                <h2 class="section-title">Our Complete Menu</h2>
                <p class="section-subtitle">
                    From aromatic coffees to delectable pastries, discover our full range of offerings
                </p>
            </div>

            <?php 
            mysqli_data_seek($kategori_query, 0); // Reset pointer
            while ($kategori = mysqli_fetch_assoc($kategori_query)) {
                $nama_kategori = $kategori['kategori'];
                echo "<div class='mb-5' data-aos='fade-up'>";
                echo "<h3 class='font-display text-primary-custom mb-4'>" . ucfirst($nama_kategori) . "</h3>";
                
                $menu_query = mysqli_query($conn, "SELECT * FROM menu WHERE kategori='$nama_kategori'");
            ?>
            
            <div class="row g-4">
                <?php while ($menu = mysqli_fetch_assoc($menu_query)) { 
                    $stok = $menu['stok'];
                    $stok_class = 'stock-high';
                    $stok_text = 'In Stock';
                    
                    if ($stok <= 3) {
                        $stok_class = 'stock-low';
                        $stok_text = 'Low Stock';
                    } elseif ($stok <= 10) {
                        $stok_class = 'stock-medium';
                        $stok_text = 'Limited';
                    }
                ?>
                <div class="col-lg-6">
                    <div class="menu-card d-flex align-items-center">
                        <img src="assets/img/newmenu/<?php echo $menu['gambar']; ?>" 
                             alt="<?php echo $menu['nama_menu']; ?>" 
                             class="menu-image">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-1 font-display"><?php echo $menu['nama_menu']; ?></h5>
                                <span class="price-tag">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                            </div>
                            <p class="text-muted small mb-2"><?php echo $menu['deskripsi']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="stock-indicator <?php echo $stok_class; ?>">
                                    <?php echo $stok_text; ?> (<?php echo $stok; ?>)
                                </span>
                                <?php if ($menu['stok'] > 0): ?>
                                    <button class="add-to-cart-btn"
                                            onclick="addToCart(<?php echo $menu['id_menu']; ?>, '<?php echo addslashes($menu['nama_menu']); ?>', <?php echo $menu['harga']; ?>, 'assets/img/newmenu/<?php echo $menu['gambar']; ?>', <?php echo $menu['stok']; ?>)">
                                        <i class="fas fa-plus me-1"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <?php echo "</div>"; } ?>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section">
        <div class="container">
            <div data-aos="fade-up">
                <h2 class="section-title">Our Story</h2>
                <p class="section-subtitle">
                    From a small dream to a beloved community gathering place
                </p>
            </div>

            <!-- Video Section -->
            <div class="text-center mb-5" data-aos="zoom-in">
                <div class="ratio ratio-16x9 rounded-4 shadow-lg mx-auto" style="max-width: 800px;">
                    <iframe
                        src="https://www.youtube.com/embed/n5pU3vyZ8bA?autoplay=1&mute=1&controls=1&modestbranding=1&rel=0"
                        title="Café de Flour Story"
                        allow="autoplay; encrypted-media"
                        allowfullscreen
                        class="rounded-4">
                    </iframe>
                </div>
            </div>

            <!-- Timeline -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div data-aos="fade-right">
                        <div class="timeline-item">
                            <h5 class="font-display text-primary-custom" style="color: black;">2018 – Our First Brew</h5>
                            <p class="text-muted">Started in a quiet Bandung corner with handcrafted cappuccino and warm rolls, driven by passion for perfect coffee.</p>
                        </div>
                    </div>
                    
                    <div data-aos="fade-left">
                        <div class="timeline-item">
                            <h5 class="font-display text-primary-custom" style="color: black;">2020 – Artisan Bakery</h5>
                            <p class="text-muted">Launched our full bakery with family recipes and daily-fresh pastries, expanding our offerings to delight every palate.</p>
                        </div>
                    </div>
                    
                    <div data-aos="fade-right">
                        <div class="timeline-item">
                            <h5 class="font-display text-primary-custom" style="color: black;">2023 – Going Green</h5>
                            <p class="text-muted">Embraced sustainability with eco-packaging and local sourcing, caring for our community and environment.</p>
                        </div>
                    </div>
                    
                    <div data-aos="fade-left">
                        <div class="timeline-item">
                            <h5 class="font-display text-primary-custom" style="color: black;">Today – More than a Café</h5>
                            <p class="text-muted">Now a second home for creators, friends, and everyone seeking peace, warmth, and exceptional coffee experiences.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="section">
        <div class="container">
            <div data-aos="fade-up">
                <h2 class="section-title">Meet Our Team</h2>
                <p class="section-subtitle">
                    The passionate people behind every perfect cup and delicious bite
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Team Member 1 -->
                <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="team-card">
                        <img src="assets/img/team/fahis.jpeg" alt="Fahish al-Azka" class="team-avatar">
                        <h5 class="font-display mb-2">Fahish al-A</h5>
                        <p class="text-muted small mb-3">Lead Developer</p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/fhisssc?igsh=MXFvOGNlN3MxY3owZg=="><i class="fab fa-instagram"></i></a>
                            <a href="https://github.com/FahishA21"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 2 -->
                <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="team-card">
                        <img src="assets/img/team/acel.jpeg" alt="Rachel Dyaa Ramadhina" class="team-avatar">
                        <h5 class="font-display mb-2">Rachel Dyaa</h5>
                        <p class="text-muted small mb-3">UI/UX Designer</p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/aiichel_?igsh=MXEzcGd0c2N2YzVrOA=="><i class="fab fa-instagram"></i></a>
                            <a href="https://github.com/racheldyaaramadhina21"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 3 -->
                <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="team-card">
                        <img src="assets/img/team/sani.jpeg" alt="Nurliana Sani" class="team-avatar">
                        <h5 class="font-display mb-2">Nurliana Sani</h5>
                        <p class="text-muted small mb-3">Backend Developer</p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/lianneshan_?igsh=YXZpamU1cGk1MnJs"><i class="fab fa-instagram"></i></a>
                            <a href="https://github.com/lianashani"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 4 -->
                <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="team-card">
                        <img src="assets/img/team/bilqif.jpeg" alt="Bilqif Albari Shidqi" class="team-avatar">
                        <h5 class="font-display mb-2">Bilqif Albari</h5>
                        <p class="text-muted small mb-3">Full Stack Developer</p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/blqifalbiii_?igsh=NGIxeWN6NTIzcmFh"><i class="fab fa-instagram"></i></a>
                            <a href="https://linkedin.com/bilqif-shidqi"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 5 -->
                <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="team-card">
                        <img src="assets/img/team/iki.jpeg" alt="Rizki Al Ikhsan" class="team-avatar">
                        <h5 class="font-display mb-2">Rizki Al Ihsan</h5>
                        <p class="text-muted small mb-3">Project Manager</p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/evolaysed?igsh=ajBlMTYzeG13Nzl3"><i class="fab fa-instagram"></i></a>
                            <a href="https://github.com/RizqiAlIhsan"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Review Form Section -->
    <section id="review-form" class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="form-modern" data-aos="fade-up">
                        <h3 class="font-display text-center mb-4">Share Your Experience</h3>
                        <form action="../website/purple-free/dist/backend/review/simpan-ulasan.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <input type="text" name="nama_pelanggan" class="form-control form-control-modern" placeholder="Your Name" required>
                            </div>
                            
                            <div class="mb-4 text-center">
                                <label class="form-label fw-semibold d-block mb-3">Your Rating</label>
                                <div class="d-flex justify-content-center gap-2 fs-3" id="star-container">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star text-muted" data-value="<?= $i ?>" style="cursor: pointer;">☆</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="rating-value" required>
                            </div>

                            <div class="mb-4">
                                <textarea name="komentar" rows="4" class="form-control form-control-modern" placeholder="Tell us about your experience..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Photo (optional)</label>
                                <input type="file" name="foto" accept="image/*" class="form-control form-control-modern">
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-primary-custom">
                                    <i class="fas fa-paper-plane"></i>
                                    Submit Review
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Track Order Section -->
    <section id="track" class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="form-modern" data-aos="fade-up">
                        <h3 class="font-display text-center mb-4">Track Your Order</h3>
                        <p class="text-center text-muted mb-4">Enter your transaction code or upload QR code to track your order</p>

                        <?php
                        $kode = $_GET['kode'] ?? '';
                        $data = null;
                        if ($kode != '') {
                            $q = mysqli_query($conn, "SELECT * FROM transaksi WHERE kode_transaksi = '$kode'");
                            $data = mysqli_fetch_assoc($q);
                        }
                        ?>

                        <form method="get" class="mb-4" id="formTracking">
                            <div class="input-group mb-3">
                                <input type="text" name="kode" id="kodeInput" value="<?= htmlspecialchars($kode) ?>" 
                                       class="form-control form-control-modern" placeholder="Enter your transaction code" required>
                                <button class="btn btn-primary-custom" type="submit" style="color: white;">
                                    <i class="fas fa-search"></i> Track
                                </button>
                            </div>
                        </form>

                        <div class="mb-4">
                            <label for="qrImage" class="form-label">Or upload QR code:</label>
                            <input type="file" class="form-control form-control-modern" id="qrImage" accept="image/*">
                            <img id="preview" src="#" alt="Preview QR" class="img-fluid rounded mt-3" style="display: none; max-height: 200px;">
                            <div class="text-muted mt-2" id="qrResult"></div>
                        </div>

                        <?php if ($data): ?>
                            <div class="alert alert-success rounded-4">
                                <h5 class="mb-3">Order Found: <span class="text-primary"><?= $data['kode_transaksi'] ?></span></h5>
                                <div class="row g-3">
                                    <div class="col-6"><strong>Customer:</strong> <?= $data['nama_pelanggan'] ?></div>
                                    <div class="col-6"><strong>Date:</strong> <?= $data['tanggal'] ?> <?= $data['waktu'] ?></div>
                                    <div class="col-6"><strong>Location:</strong> <?= $data['lokasi'] ?></div>
                                    <div class="col-6"><strong>Status:</strong> <span class="badge bg-info"><?= $data['status'] ?></span></div>
                                    <div class="col-12"><strong>Notes:</strong> <?= $data['catatan'] ?: 'No special notes' ?></div>
                                </div>
                            </div>
                        <?php elseif ($kode): ?>
                            <div class="alert alert-danger rounded-4">
                                Order with code <strong><?= htmlspecialchars($kode) ?></strong> not found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section contact-section" id="contact">
        <div class="container">
            <div data-aos="fade-up">
                <h2 class="section-title">Get in Touch</h2>
                <p class="section-subtitle">
                    We're here for you – whether it's a question, suggestion, or just to say hi
                </p>
            </div>

            <div class="row g-4 justify-content-center text-center">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="p-4">
                        <div class="mb-3">
                            <i class="fas fa-map-marker-alt fa-2x text-accent"></i>
                        </div>
                        <h5 class="font-display">Visit Us</h5>
                        <p class="text-light">Jl. Marhas margahayu 1<br>Bandung, Indonesia</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="p-4">
                        <div class="mb-3">
                            <i class="fas fa-phone fa-2x text-accent"></i>
                        </div>
                        <h5 class="font-display">Call Us</h5>
                        <p class="text-light">+62 812-3456-7890</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="p-4">
                        <div class="mb-3">
                            <i class="fas fa-envelope fa-2x text-accent"></i>
                        </div>
                        <h5 class="font-display">Email Us</h5>
                        <p class="text-light">contact@cafedeflour.com</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5" data-aos="fade-up">
                <a href="https://forms.gle/eP3t3buupatm9zMc7" target="_blank" class="btn-primary-custom">
                    <i class="fas fa-paper-plane"></i>
                    Send Us Feedback
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="font-display mb-3">Café de Flour</h5>
                    <p class="text-light">Where warmth meets flavor in every cup and bite. Creating memorable experiences since 2018.</p>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#menu" class="text-light text-decoration-none">Menu</a></li>
                        <li><a href="#about" class="text-light text-decoration-none">About Us</a></li>
                        <li><a href="#track" class="text-light text-decoration-none">Track Order</a></li>
                        <li><a href="login.php" class="text-light text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Follow Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-light">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 Café de Flour. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="checkoutForm" method="POST" action="../website/purple-free/dist/backend/transaksi/simpan-web.php" class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title font-display">Complete Your Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Cart items will be populated here -->
                    <div id="checkoutItems"></div>
                    
                    <!-- Customer Information -->
                    <div class="mb-4">
                        <label for="checkout_nama_pelanggan" class="form-label fw-semibold">Customer Name</label>
                        <input type="text" class="form-control form-control-modern" name="nama_pelanggan" id="checkout_nama_pelanggan" placeholder="Enter your name" required>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="checkout_catatan" class="form-label fw-semibold">Special Notes (optional)</label>
                        <textarea class="form-control form-control-modern" name="catatan" id="checkout_catatan" rows="2" placeholder="e.g., no sugar, extra hot, etc."></textarea>
                    </div>

                    <!-- Promo Code -->
                    <div class="mb-4">
                        <label for="checkout_kode_promo" class="form-label fw-semibold">Promo Code</label>
                        <input type="text" name="kode_promo" id="checkout_kode_promo" class="form-control form-control-modern" placeholder="Enter promo code">
                        <div id="checkout-promo-feedback" class="form-text d-none"></div>
                    </div>

                    <!-- Payment & Location -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="checkout_metode" class="form-label fw-semibold">Payment Method</label>
                            <select class="form-select form-control-modern" name="metode" id="checkout_metode" required>
                                <option value="" disabled selected>Choose method</option>
                                <option value="Tunai">Cash</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Debit">Debit Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="checkout_lokasi" class="form-label fw-semibold">Service Type</label>
                            <select class="form-select form-control-modern" name="lokasi" id="checkout_lokasi" required>
                                <option value="Dine In">Dine In</option>
                                <option value="Take Away">Take Away</option>
                            </select>
                        </div>
                    </div>

                    <!-- Total Price -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subtotal</label>
                            <input type="text" id="checkout-total-harga" class="form-control form-control-modern bg-light" readonly value="Rp0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total After Discount</label>
                            <input type="text" id="checkout-total-akhir" class="form-control form-control-modern bg-light" readonly value="Rp0">
                        </div>
                    </div>

                    <!-- Payment Amount -->
                    <div class="mb-4">
                        <label for="checkout_bayar" class="form-label fw-semibold">Payment Amount (Rp)</label>
                        <input type="number" name="bayar" id="checkout_bayar" class="form-control form-control-modern" min="0" required>
                        <div id="checkout-bayar-warning" class="form-text text-danger d-none">Insufficient payment amount.</div>
                    </div>

                    <input type="hidden" name="diskon_harga" id="checkout-diskon-harga" value="0">
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" id="checkout-submit-btn" class="btn-primary-custom w-100">
                        <i class="fas fa-shopping-cart"></i> Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>

    <script>
        // Shopping Cart System
        let cart = [];
        let cartTotal = 0;

        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Cart Functions
        function addToCart(id, name, price, image, stock) {
            // Check if item already exists in cart
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity += 1;
                    showNotification(`${name} quantity updated in cart!`);
                } else {
                    showNotification(`Sorry, only ${stock} items available in stock!`, 'warning');
                    return;
                }
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1,
                    stock: stock
                });
                showNotification(`${name} added to cart!`);
            }
            
            updateCartDisplay();
            animateCartButton();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            updateCartDisplay();
        }

        function updateQuantity(id, change) {
            const item = cart.find(item => item.id === id);
            if (item) {
                const newQuantity = item.quantity + change;
                if (newQuantity <= 0) {
                    removeFromCart(id);
                } else if (newQuantity <= item.stock) {
                    item.quantity = newQuantity;
                    updateCartDisplay();
                } else {
                    showNotification(`Sorry, only ${item.stock} items available!`, 'warning');
                }
            }
        }

        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartBadge = document.getElementById('cartBadge');
            const cartTotal = document.getElementById('cartTotal');
            const cartSubtotal = document.getElementById('cartSubtotal');
            const cartTotalAmount = document.getElementById('cartTotalAmount');

            // Update badge
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartBadge.textContent = totalItems;
            cartBadge.style.display = totalItems > 0 ? 'flex' : 'none';

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Your cart is empty</p>
                        <small>Add some delicious items to get started!</small>
                    </div>
                `;
                cartTotal.style.display = 'none';
                return;
            }

            // Calculate total
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            // Display cart items
            cartItems.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">Rp ${formatNumber(item.price)}</div>
                        <div class="cart-quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Update totals
            cartSubtotal.textContent = `Rp ${formatNumber(subtotal)}`;
            cartTotalAmount.textContent = `Rp ${formatNumber(subtotal)}`;
            cartTotal.style.display = 'block';
        }

        function toggleCart() {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');
            
            cartSidebar.classList.toggle('open');
            cartOverlay.classList.toggle('show');
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                cart = [];
                updateCartDisplay();
                showNotification('Cart cleared!', 'info');
            }
        }

        function proceedToCheckout() {
            if (cart.length === 0) {
                showNotification('Your cart is empty!', 'warning');
                return;
            }

            // Populate checkout modal
            populateCheckoutModal();
            
            // Close cart and open checkout modal
            toggleCart();
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }

        function populateCheckoutModal() {
            const checkoutItems = document.getElementById('checkoutItems');
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Create hidden inputs for menu items
            let menuInputs = '';
            cart.forEach((item, index) => {
                menuInputs += `
                    <input type="hidden" name="menu[${index}][id_menu]" value="${item.id}">
                    <input type="hidden" name="menu[${index}][jumlah]" value="${item.quantity}">
                `;
            });

            checkoutItems.innerHTML = `
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Order Summary</h6>
                    ${cart.map(item => `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${item.name} x ${item.quantity}</span>
                            <span>Rp ${formatNumber(item.price * item.quantity)}</span>
                        </div>
                    `).join('')}
                    <hr>
                    <div class="d-flex justify-content-between align-items-center fw-bold">
                        <span>Subtotal:</span>
                        <span>Rp ${formatNumber(subtotal)}</span>
                    </div>
                </div>
                ${menuInputs}
            `;

            // Update total fields
            document.getElementById('checkout-total-harga').value = `Rp ${formatNumber(subtotal)}`;
            document.getElementById('checkout-total-akhir').value = `Rp ${formatNumber(subtotal)}`;
            document.getElementById('checkout_bayar').value = subtotal;
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('cartNotification');
            const notificationText = document.getElementById('notificationText');
            
            notificationText.textContent = message;
            notification.className = `cart-notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        function animateCartButton() {
            const cartButton = document.querySelector('.cart-button');
            cartButton.classList.add('quick-add-animation');
            setTimeout(() => {
                cartButton.classList.remove('quick-add-animation');
            }, 600);
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Checkout form handling
        document.getElementById('checkout_kode_promo').addEventListener('input', function() {
            const kode = this.value.trim().toUpperCase();
            const feedback = document.getElementById('checkout-promo-feedback');
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            if (!kode) {
                document.getElementById('checkout-diskon-harga').value = 0;
                document.getElementById('checkout-total-akhir').value = `Rp ${formatNumber(subtotal)}`;
                feedback.classList.add('d-none');
                return;
            }

            fetch(`../website/purple-free/dist/backend/promo/cek-promo.php?kode=${kode}`)
                .then(res => res.json())
                .then(data => {
                    let diskon = 0;
                    if (data.valid) {
                        diskon = data.jenis === 'persen' ? Math.floor(subtotal * data.nilai / 100) : data.nilai;
                        const totalSetelahDiskon = Math.max(subtotal - diskon, 0);
                        document.getElementById('checkout-diskon-harga').value = diskon;
                        document.getElementById('checkout-total-akhir').value = `Rp ${formatNumber(totalSetelahDiskon)}`;
                        document.getElementById('checkout_bayar').value = totalSetelahDiskon;
                        feedback.classList.remove('d-none', 'text-danger');
                        feedback.classList.add('text-success');
                        feedback.textContent = `Promo applied: -Rp ${formatNumber(diskon)}`;
                    } else {
                        document.getElementById('checkout-diskon-harga').value = 0;
                        document.getElementById('checkout-total-akhir').value = `Rp ${formatNumber(subtotal)}`;
                        document.getElementById('checkout_bayar').value = subtotal;
                        feedback.classList.remove('text-success');
                        feedback.classList.add('text-danger');
                        feedback.classList.remove('d-none');
                        feedback.textContent = "Invalid or expired promo code.";
                    }
                })
                .catch(() => {
                    document.getElementById('checkout-diskon-harga').value = 0;
                    document.getElementById('checkout-total-akhir').value = `Rp ${formatNumber(subtotal)}`;
                    feedback.classList.remove('text-success');
                    feedback.classList.add('text-danger');
                    feedback.classList.remove('d-none');
                    feedback.textContent = "Failed to check promo code.";
                });
        });

        // Payment validation
        document.getElementById('checkout_bayar').addEventListener('input', function() {
            const bayar = parseInt(this.value) || 0;
            const totalAkhirText = document.getElementById('checkout-total-akhir').value;
            const totalAkhir = parseInt(totalAkhirText.replace(/[^0-9]/g, '')) || 0;
            const warning = document.getElementById('checkout-bayar-warning');
            const submitBtn = document.getElementById('checkout-submit-btn');
            
            if (bayar < totalAkhir) {
                warning.classList.remove('d-none');
                submitBtn.disabled = true;
            } else {
                warning.classList.add('d-none');
                submitBtn.disabled = false;
            }
        });

        // Form submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                showNotification('Your cart is empty!', 'warning');
                return;
            }
            
            // Clear cart after successful submission
            cart = [];
            updateCartDisplay();
        });

        // Star Rating System
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                document.getElementById('rating-value').value = value;
                
                document.querySelectorAll('.star').forEach((s, index) => {
                    if (index < value) {
                        s.textContent = '★';
                        s.classList.remove('text-muted');
                        s.classList.add('text-warning');
                    } else {
                        s.textContent = '☆';
                        s.classList.remove('text-warning');
                        s.classList.add('text-muted');
                    }
                });
            });
        });

        // QR Code Scanner
        document.getElementById('qrImage').addEventListener('change', function (event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');
            const qrResult = document.getElementById('qrResult');

            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    context.drawImage(img, 0, 0, canvas.width, canvas.height);

                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, canvas.width, canvas.height);

                    if (code) {
                        qrResult.innerText = 'QR code detected: ' + code.data;
                        document.getElementById('kodeInput').value = code.data;
                        document.getElementById('formTracking').submit();
                    } else {
                        qrResult.innerText = 'QR code not detected. Please try another image.';
                    }
                };
                img.src = e.target.result;
                preview.src = img.src;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });

        // Loading animation for cards
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

        // Observe all cards for animation
        document.querySelectorAll('.card-modern, .menu-card, .review-card, .team-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Close cart when clicking outside
        document.addEventListener('click', function(e) {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartButton = document.querySelector('.cart-button');
            
            if (!cartSidebar.contains(e.target) && !cartButton.contains(e.target) && cartSidebar.classList.contains('open')) {
                toggleCart();
            }
        });

        // Initialize cart display
        updateCartDisplay();
    </script>

</body>
</html>