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
                    <li class="nav-item"><a class="nav-link" href="/member_dashboard/login.php">Member Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Cashier Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

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
                            <a href="#about" class="btn-outline-custom">
                                <i class="fas fa-play"></i>
                                Our Story
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="text-center">
                        <img src="assets/img/hero-coffee-cup.png" alt="Coffee" class="img-fluid" style="max-width: 400px;">
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
                                <button class="btn btn-sm btn-primary-custom order-button" style="color: white;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderModal" 
                                        data-id="<?php echo $menu['id_menu']; ?>">
                                    Order Now
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
                            </div>
                        </div>
                        <p class="text-muted mb-3 fst-italic">"'.htmlspecialchars($r['komentar']).'"</p>
                        <small class="text-muted">'.date('M d, Y', strtotime($r['tanggal'])).'</small>
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
                                    <button class="btn btn-sm btn-primary-custom order-button" style="color:white"
                                            data-bs-toggle="modal"
                                            data-bs-target="#orderModal"
                                            data-id="<?php echo $menu['id_menu']; ?>">
                                        <i class="fas fa-plus"></i> Order
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
                        <h5 class="font-display mb-2">Fahish al-Azka</h5>
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
                                <button class="btn btn-primary-custom" type="submit">
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
    <section class="section contact-section">
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
                        <li><a href="login.php" class="text-light text-decoration-none">Member Login</a></li>
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

    <!-- Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="orderForm" method="POST" action="../website/purple-free/dist/backend/transaksi/simpan-web.php" class="modal-content modal-content-modern">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title font-display">Order Form</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_menu_default" id="id_menu_default">
                    <input type="hidden" name="diskon_harga" id="diskon-harga" value="0">

                    <!-- Customer Name -->
                    <div class="mb-4">
                        <label for="nama_pelanggan" class="form-label fw-semibold">Customer Name</label>
                        <input type="text" class="form-control form-control-modern" name="nama_pelanggan" id="nama_pelanggan" placeholder="Enter your name" required>
                    </div>

                    <!-- Menu Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Menu</label>
                        <div id="menu-container">
                            <div class="menu-item mb-3 p-3 border rounded-3">
                                <select name="menu[0][id_menu]" class="form-select form-control-modern mb-2" required>
                                    <option value="">Choose Menu</option>
                                    <?php 
                                    $menuResult = mysqli_query($conn, "SELECT * FROM menu ORDER BY nama_menu ASC");
                                    while ($menu = mysqli_fetch_assoc($menuResult)) {
                                        echo "<option value='{$menu['id_menu']}' data-harga='{$menu['harga']}'>" . htmlspecialchars($menu['nama_menu']) . " - Rp" . number_format($menu['harga'], 0, ',', '.') . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type="number" name="menu[0][jumlah]" class="form-control form-control-modern" placeholder="Quantity" min="1" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" id="add-menu">
                            <i class="fas fa-plus"></i> Add More Items
                        </button>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="catatan" class="form-label fw-semibold">Special Notes (optional)</label>
                        <textarea class="form-control form-control-modern" name="catatan" id="catatan" rows="2" placeholder="e.g., no sugar, extra hot, etc."></textarea>
                    </div>

                    <!-- Promo Code -->
                    <div class="mb-4">
                        <label for="kode_promo" class="form-label fw-semibold">Promo Code</label>
                        <input type="text" name="kode_promo" id="kode_promo" class="form-control form-control-modern" placeholder="Enter promo code">
                        <div id="promo-feedback" class="form-text d-none"></div>
                    </div>

                    <!-- Payment & Location -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="metode" class="form-label fw-semibold">Payment Method</label>
                            <select class="form-select form-control-modern" name="metode" id="metode" required>
                                <option value="" disabled selected>Choose method</option>
                                <option value="Tunai">Cash</option>
                                <option value="QRIS">QRIS</option>
                                <option value="Debit">Debit Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label fw-semibold">Service Type</label>
                            <select class="form-select form-control-modern" name="lokasi" id="lokasi" required>
                                <option value="Dine In">Dine In</option>
                                <option value="Take Away">Take Away</option>
                            </select>
                        </div>
                    </div>

                    <!-- Total Price -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subtotal</label>
                            <input type="text" id="total-harga" class="form-control form-control-modern bg-light" readonly value="Rp0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total After Discount</label>
                            <input type="text" id="total-akhir" class="form-control form-control-modern bg-light" readonly value="Rp0">
                        </div>
                    </div>

                    <!-- Payment Amount -->
                    <div class="mb-4">
                        <label for="bayar" class="form-label fw-semibold">Payment Amount (Rp)</label>
                        <input type="number" name="bayar" id="bayar" class="form-control form-control-modern" min="0" required>
                        <div id="bayar-warning" class="form-text text-danger d-none">Insufficient payment amount.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" id="submit-btn" class="btn-primary-custom w-100">
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
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
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

        // Order Modal Logic
        const idMenuInput = document.getElementById('id_menu_default');
        const orderModalEl = document.getElementById('orderModal');
        const bayarInput = document.getElementById("bayar");
        const totalAkhirInput = document.getElementById("total-akhir");
        const bayarWarning = document.getElementById("bayar-warning");
        const submitBtn = document.getElementById("submit-btn");

        let menuIndex = 1;

        // Order button click handlers
        document.querySelectorAll('.order-button').forEach(button => {
            button.addEventListener('click', function () {
                const menuId = this.getAttribute('data-id');
                idMenuInput.value = menuId;
            });
        });

        // Modal show event
        orderModalEl.addEventListener('show.bs.modal', () => {
            const idMenuDefault = idMenuInput.value;
            const selectMenu = document.querySelector('#menu-container select');
            const inputJumlah = document.querySelector('#menu-container input[type=number]');
            if (selectMenu && inputJumlah && idMenuDefault) {
                selectMenu.value = idMenuDefault;
                inputJumlah.value = 1;
            }
            hitungTotal();
        });

        // Add menu item
        document.getElementById("add-menu").addEventListener("click", function () {
            const container = document.getElementById("menu-container");
            const menuItem = document.createElement("div");
            menuItem.className = "menu-item mb-3 p-3 border rounded-3";
            menuItem.innerHTML = `
                <select name="menu[${menuIndex}][id_menu]" class="form-select form-control-modern mb-2" required>
                    <option value="">Choose Menu</option>
                    <?php 
                    $menuResult = mysqli_query($conn, "SELECT * FROM menu ORDER BY nama_menu ASC");
                    while ($menu = mysqli_fetch_assoc($menuResult)) {
                        echo "<option value='{$menu['id_menu']}' data-harga='{$menu['harga']}'>" . htmlspecialchars($menu['nama_menu']) . " - Rp" . number_format($menu['harga'], 0, ',', '.') . "</option>";
                    }
                    ?>
                </select>
                <input type="number" name="menu[${menuIndex}][jumlah]" class="form-control form-control-modern" placeholder="Quantity" min="1" required>
                <button type="button" class="btn btn-sm btn-outline-danger mt-2 remove-menu">
                    <i class="fas fa-trash"></i> Remove
                </button>
            `;
            container.appendChild(menuItem);
            menuIndex++;
            updateEventListeners();
            hitungTotal();
        });

        // Remove menu item
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-menu')) {
                e.target.closest('.menu-item').remove();
                hitungTotal();
            }
        });

        function updateEventListeners() {
            document.querySelectorAll('#menu-container select, #menu-container input[type="number"]').forEach(el => {
                el.removeEventListener('change', hitungTotal);
                el.addEventListener('change', hitungTotal);
            });
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        }

        function parseRupiah(str) {
            return parseInt(str.replace(/[^0-9]/g, '')) || 0;
        }

        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('.menu-item').forEach(item => {
                const select = item.querySelector('select');
                const jumlahInput = item.querySelector('input[type="number"]');
                const selectedOption = select.options[select.selectedIndex];
                const harga = selectedOption?.getAttribute('data-harga') || 0;
                const jumlah = jumlahInput.value || 0;
                total += parseInt(harga) * parseInt(jumlah);
            });
            document.getElementById("total-harga").value = formatRupiah(total);
            hitungDiskon(total);
        }

        function hitungDiskon(total) {
            const kode = document.getElementById("kode_promo")?.value?.trim().toUpperCase();
            const feedback = document.getElementById("promo-feedback");
            const diskonField = document.getElementById("diskon-harga");
            const totalAkhirField = document.getElementById("total-akhir");

            if (!kode) {
                diskonField.value = 0;
                totalAkhirField.value = formatRupiah(total);
                if (feedback) feedback.classList.add("d-none");
                return;
            }

            fetch(`../website/purple-free/dist/backend/promo/cek-promo.php?kode=${kode}`)
                .then(res => res.json())
                .then(data => {
                    let diskon = 0;
                    if (data.valid) {
                        diskon = data.jenis === 'persen' ? Math.floor(total * data.nilai / 100) : data.nilai;
                        const totalSetelahDiskon = Math.max(total - diskon, 0);
                        diskonField.value = diskon;
                        totalAkhirField.value = formatRupiah(totalSetelahDiskon);
                        feedback.classList.remove("d-none", "text-danger");
                        feedback.classList.add("text-success");
                        feedback.textContent = `Promo applied: -${formatRupiah(diskon)}`;
                    } else {
                        diskonField.value = 0;
                        totalAkhirField.value = formatRupiah(total);
                        feedback.classList.remove("text-success");
                        feedback.classList.add("text-danger");
                        feedback.classList.remove("d-none");
                        feedback.textContent = "Invalid or expired promo code.";
                    }
                    cekBayar();
                })
                .catch(() => {
                    diskonField.value = 0;
                    totalAkhirField.value = formatRupiah(total);
                    if (feedback) {
                        feedback.classList.remove("text-success");
                        feedback.classList.add("text-danger");
                        feedback.classList.remove("d-none");
                        feedback.textContent = "Failed to check promo code.";
                    }
                    cekBayar();
                });
        }

        function cekBayar() {
            const bayar = parseInt(bayarInput.value) || 0;
            const totalAkhir = parseRupiah(totalAkhirInput.value);
            if (bayar < totalAkhir) {
                bayarWarning.classList.remove("d-none");
                submitBtn.disabled = true;
            } else {
                bayarWarning.classList.add("d-none");
                submitBtn.disabled = false;
            }
        }

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

        // Initialize event listeners
        document.addEventListener("DOMContentLoaded", () => {
            updateEventListeners();
            document.getElementById("menu-container").addEventListener("input", hitungTotal);
            document.getElementById("kode_promo").addEventListener("input", () => hitungTotal());
            bayarInput.addEventListener("input", cekBayar);
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
    </script>
</body>
</html>

