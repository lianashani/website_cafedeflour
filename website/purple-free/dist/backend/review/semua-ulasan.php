<?php
include '../../../../config/koneksi.php';

// Kirim Balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['balas_komentar'])) {
  $parent_id = (int)$_POST['parent_id'];
  $nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
  $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);

  mysqli_query($conn, "
    INSERT INTO review (nama_pelanggan, komentar, rating, tanggal, parent_id, foto)
    VALUES ('$nama', '$komentar', 0, NOW(), $parent_id, '')
  ");
  header("Location: semua-ulasan.php");
  exit;
}

// Ringkasan
$summaryQuery = mysqli_query($conn, "SELECT COUNT(*) AS jumlah, AVG(rating) AS rata FROM review WHERE parent_id IS NULL");
$summary = mysqli_fetch_assoc($summaryQuery);
$jumlah_ulasan = $summary['jumlah'];
$rata_rata = round($summary['rata'], 1);

// Distribusi bintang
$stars_data = [];
$total_rating = 0;
for ($i = 5; $i >= 1; $i--) {
  $q = mysqli_query($conn, "SELECT COUNT(*) as jml FROM review WHERE rating = $i AND parent_id IS NULL");
  $stars_data[$i] = mysqli_fetch_assoc($q)['jml'];
  $total_rating += $stars_data[$i];
}

// Pagination dan filter
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$filter = "parent_id IS NULL";
$filter_url = "";

// Filter rating bintang
if (isset($_GET['bintang']) && in_array($_GET['bintang'], ['1','2','3','4','5'])) {
  $bintang = $_GET['bintang'];
  $filter .= " AND rating = $bintang";
  $filter_url .= "&bintang=$bintang";
}

// Pengurutan
$sort_options = [
  'terbaru' => 'tanggal DESC',
  'terlama' => 'tanggal ASC',
  'rating_tertinggi' => 'rating DESC',
  'rating_terendah' => 'rating ASC'
];
$sort = $_GET['sort'] ?? 'terbaru';
$order_by = $sort_options[$sort] ?? $sort_options['terbaru'];
$filter_url .= "&sort=$sort";

// Ambil data ulasan
$ulasan = mysqli_query($conn, "
  SELECT * FROM review
  WHERE $filter
  ORDER BY $order_by
  LIMIT $start, $limit
");

// Hitung total untuk pagination
$count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM review WHERE $filter");
$total_rows = mysqli_fetch_assoc($count)['total'];
$total_pages = ceil($total_rows / $limit);

// Loop data ulasan
$reviews = [];
while ($r = mysqli_fetch_assoc($ulasan)) {
  $reviews[] = $r;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Café de Flour</title>
    
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
            line-height: 1.6;
            color: var(--text-dark);
            background: linear-gradient(135deg, var(--bg-cream) 0%, #f8f6f3 100%);
            min-height: 100vh;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Header Section */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0 2rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .page-header .container {
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        /* Review Summary Card */
        .review-summary {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-medium);
            margin-bottom: 3rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .summary-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .summary-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .overall-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .rating-display {
            text-align: center;
        }

        .rating-number {
            font-size: 4rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
        }

        .rating-stars {
            color: #fbbf24;
            font-size: 1.5rem;
            margin: 0.5rem 0;
        }

        .rating-count {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .rating-breakdown {
            flex: 1;
            max-width: 400px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            margin: 0.75rem 0;
            text-decoration: none;
            color: inherit;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .rating-bar:hover {
            background: var(--bg-cream);
            transform: translateX(5px);
            color: inherit;
        }

        .rating-bar.active {
            background: var(--accent-color);
            color: var(--primary-color);
            font-weight: 600;
        }

        .rating-label {
            width: 60px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bar-container {
            flex: 1;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin: 0 1rem;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #fbbf24, #f59e0b);
            border-radius: 6px;
            transition: width 0.5s ease;
        }

        .rating-percentage {
            width: 50px;
            text-align: right;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Filter Controls */
        .filter-controls {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-light);
            margin-bottom: 2rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--text-dark);
            white-space: nowrap;
        }

        .form-select-modern {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: white;
        }

        .form-select-modern:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.1);
        }

        .btn-clear-filter {
            background: var(--bg-cream);
            color: var(--text-dark);
            border: 2px solid var(--accent-color);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-clear-filter:hover {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        /* Review Cards */
        .review-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .reviewer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--bg-cream);
        }

        .reviewer-details h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .review-date {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #fbbf24;
            font-size: 1.2rem;
        }

        .rating-badge {
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .review-content {
            margin: 1.5rem 0;
            font-size: 1.05rem;
            line-height: 1.7;
            color: var(--text-dark);
            font-style: italic;
            position: relative;
        }

        .review-content::before {
            content: '"';
            font-size: 3rem;
            color: var(--accent-color);
            position: absolute;
            left: -1rem;
            top: -0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .review-photo {
            max-width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 12px;
            margin: 1rem 0;
            box-shadow: var(--shadow-light);
        }

        .review-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }

        .action-btn:hover {
            background: var(--bg-cream);
            color: var(--primary-color);
        }

        .action-btn.liked {
            color: #ef4444;
        }

        .action-btn.liked:hover {
            background: #fee2e2;
        }

        /* Replies */
        .replies-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }

        .reply-item {
            background: var(--bg-cream);
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
            border-left: 4px solid var(--accent-color);
        }

        .reply-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .reply-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .reply-author {
            font-weight: 600;
            color: var(--primary-color);
        }

        .reply-content {
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Reply Form */
        .reply-form {
            background: var(--bg-cream);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            border: 2px dashed var(--accent-color);
        }

        .form-control-modern {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control-modern:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.1);
        }

        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .btn-outline-custom {
            background: transparent;
            color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Add Review Form */
        .add-review-form {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .rating-input {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .rating-star {
            font-size: 2rem;
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rating-star:hover,
        .rating-star.active {
            color: #fbbf24;
            transform: scale(1.1);
        }

        /* Pagination */
        .pagination-modern {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 3rem 0;
        }

        .page-link-modern {
            padding: 0.75rem 1rem;
            background: white;
            color: var(--text-dark);
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-link-modern:hover {
            background: var(--bg-cream);
            border-color: var(--accent-color);
            color: var(--primary-color);
        }

        .page-link-modern.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .overall-rating {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .review-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .review-actions {
                flex-wrap: wrap;
            }
        }

        /* Loading Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
    </style>
</head>

<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="text-center">
                <h1 class="page-title" data-aos="fade-down">Customer Reviews</h1>
                <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Thank you for all your feedback and support! Your reviews help us grow and serve you better.
                </p>
                <a href="/website/index.php#Review-section" class="btn-back" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-arrow-left me-2"></i>Back to Homepage
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Review Summary -->
        <div class="review-summary" data-aos="fade-up">
            <div class="summary-header">
                <h2 class="summary-title">Café de Flour Review Summary</h2>
            </div>

            <div class="overall-rating">
                <div class="rating-display">
                    <div class="rating-number"><?= str_replace('.', ',', $rata_rata) ?></div>
                    <div class="rating-stars">
                        <?php 
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= round($rata_rata) ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <div class="rating-count"><?= number_format($jumlah_ulasan, 0, ',', '.') ?> reviews</div>
                </div>

                <div class="rating-breakdown">
                    <?php for ($i = 5; $i >= 1; $i--): 
                        $persen = $total_rating > 0 ? ($stars_data[$i] / $total_rating) * 100 : 0;
                        $active = (isset($_GET['bintang']) && $_GET['bintang'] == $i) ? 'active' : '';
                    ?>
                        <a href="?bintang=<?= $i ?>&sort=<?= $sort ?>" class="rating-bar <?= $active ?>">
                            <div class="rating-label">
                                <span><?= $i ?></span>
                                <i class="fas fa-star" style="color: #fbbf24;"></i>
                            </div>
                            <div class="bar-container">
                                <div class="bar-fill" style="width: <?= $persen ?>%;"></div>
                            </div>
                            <div class="rating-percentage"><?= number_format($persen, 1) ?>%</div>
                        </a>
                    <?php endfor; ?>

                    <?php if (isset($_GET['bintang'])): ?>
                        <div class="text-center mt-3">
                            <a href="semua-ulasan.php?sort=<?= $sort ?>" class="btn-clear-filter">
                                <i class="fas fa-times me-1"></i>Show All Reviews
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls" data-aos="fade-up" data-aos-delay="100">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="filter-group">
                        <label class="filter-label">Filter by Rating:</label>
                        <form method="GET" class="flex-grow-1">
                            <select name="bintang" onchange="this.form.submit()" class="form-select form-select-modern">
                                <option value="">All Ratings</option>
                                <?php for ($i=5; $i>=1; $i--): ?>
                                    <option value="<?= $i ?>" <?= isset($_GET['bintang']) && $_GET['bintang']==$i ? 'selected' : '' ?>>
                                        <?= $i ?> Star<?= $i > 1 ? 's' : '' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <input type="hidden" name="sort" value="<?= $sort ?>">
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-group">
                        <label class="filter-label">Sort by:</label>
                        <form method="GET" class="flex-grow-1">
                            <select name="sort" onchange="this.form.submit()" class="form-select form-select-modern">
                                <option value="terbaru" <?= $sort=='terbaru' ? 'selected' : '' ?>>Newest First</option>
                                <option value="terlama" <?= $sort=='terlama' ? 'selected' : '' ?>>Oldest First</option>
                                <option value="rating_tertinggi" <?= $sort=='rating_tertinggi' ? 'selected' : '' ?>>Highest Rating</option>
                                <option value="rating_terendah" <?= $sort=='rating_terendah' ? 'selected' : '' ?>>Lowest Rating</option>
                            </select>
                            <?php if (isset($_GET['bintang'])): ?>
                                <input type="hidden" name="bintang" value="<?= $_GET['bintang'] ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list">
            <?php 
            $delay = 200;
            foreach ($reviews as $r):
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= $i <= $r['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                }
                $foto = $r['foto'] ? '<img src="/website/assets/img/upload/review/'.$r['foto'].'" class="review-photo" alt="Review photo">' : '';
                $avatar = 'https://ui-avatars.com/api/?name='.urlencode($r['nama_pelanggan']).'&background=random&size=120';
            ?>
                <div class="review-card" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($r['nama_pelanggan']) ?>" class="reviewer-avatar">
                            <div class="reviewer-details">
                                <h5><?= htmlspecialchars($r['nama_pelanggan']) ?></h5>
                                <div class="review-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?= date('F j, Y', strtotime($r['tanggal'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="stars"><?= $stars ?></div>
                            <span class="rating-badge"><?= $r['rating'] ?>/5</span>
                        </div>
                    </div>

                    <div class="review-content">
                        <?= htmlspecialchars($r['komentar']) ?>
                    </div>

                    <?= $foto ?>

                    <div class="review-actions">
                        <button class="action-btn like-btn" data-review-id="<?= $r['id_review'] ?>">
                            <i class="far fa-heart"></i>
                            <span>Helpful</span>
                        </button>
                        <button class="action-btn reply-btn" data-review-id="<?= $r['id_review'] ?>">
                            <i class="fas fa-reply"></i>
                            <span>Reply</span>
                        </button>
                        <button class="action-btn share-btn">
                            <i class="fas fa-share"></i>
                            <span>Share</span>
                        </button>
                    </div>

                    <!-- Existing Replies -->
                    <?php
                    $resBalasan = mysqli_query($conn, "SELECT * FROM review WHERE parent_id = ".$r['id_review']." ORDER BY tanggal ASC");
                    if (mysqli_num_rows($resBalasan) > 0):
                    ?>
                        <div class="replies-section">
                            <h6 class="mb-3"><i class="fas fa-comments me-2"></i>Replies</h6>
                            <?php while ($balas = mysqli_fetch_assoc($resBalasan)): 
                                $reply_avatar = 'https://ui-avatars.com/api/?name='.urlencode($balas['nama_pelanggan']).'&background=random&size=80';
                            ?>
                                <div class="reply-item">
                                    <div class="reply-header">
                                        <img src="<?= $reply_avatar ?>" alt="<?= htmlspecialchars($balas['nama_pelanggan']) ?>" class="reply-avatar">
                                        <div>
                                            <div class="reply-author"><?= htmlspecialchars($balas['nama_pelanggan']) ?></div>
                                            <small class="text-muted"><?= date('M j, Y', strtotime($balas['tanggal'])) ?></small>
                                        </div>
                                    </div>
                                    <div class="reply-content"><?= htmlspecialchars($balas['komentar']) ?></div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Reply Form -->
                    <div class="reply-form" id="reply-form-<?= $r['id_review'] ?>" style="display: none;">
                        <h6 class="mb-3"><i class="fas fa-pen me-2"></i>Write a Reply</h6>
                        <form method="POST">
                            <input type="hidden" name="parent_id" value="<?= $r['id_review'] ?>">
                            <div class="mb-3">
                                <input type="text" name="nama_pelanggan" placeholder="Your Name" class="form-control form-control-modern" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="komentar" class="form-control form-control-modern" rows="3" placeholder="Write your reply..." required></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="balas_komentar" class="btn-primary-custom">
                                    <i class="fas fa-paper-plane"></i>
                                    Send Reply
                                </button>
                                <button type="button" class="btn-outline-custom cancel-reply" data-review-id="<?= $r['id_review'] ?>">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php 
            $delay += 100;
            endforeach; 
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Reviews pagination">
                <div class="pagination-modern">
                    <?php if($page > 1): ?>
                        <a class="page-link-modern" href="?page=<?= $page - 1 ?><?= $filter_url ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a class="page-link-modern <?= $i == $page ? 'active' : '' ?>" href="?page=<?= $i ?><?= $filter_url ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a class="page-link-modern" href="?page=<?= $page + 1 ?><?= $filter_url ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>

        <!-- Add Review Form -->
        <div class="add-review-form" data-aos="fade-up">
            <h3 class="form-title">Share Your Experience</h3>
            <form action="simpan-ulasan.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Your Name</label>
                        <input type="text" name="nama_pelanggan" class="form-control form-control-modern" placeholder="Enter your name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Your Rating</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="rating-star" data-rating="<?= $i ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                        <select name="rating" class="form-select form-select-modern d-none" id="rating-select" required>
                            <option value="">Select Rating</option>
                            <?php for ($i=5; $i>=1; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Your Review</label>
                    <textarea name="komentar" class="form-control form-control-modern" rows="4" placeholder="Tell us about your experience at Café de Flour..." required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">Photo (Optional)</label>
                    <input type="file" name="foto" accept="image/*" class="form-control form-control-modern">
                    <small class="form-text text-muted">Share a photo of your experience with us!</small>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-star me-2"></i>
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });

        // Like button functionality
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.classList.toggle('liked');
                const icon = this.querySelector('i');
                const text = this.querySelector('span');
                
                if (this.classList.contains('liked')) {
                    icon.className = 'fas fa-heart';
                    text.textContent = 'Helpful!';
                } else {
                    icon.className = 'far fa-heart';
                    text.textContent = 'Helpful';
                }
            });
        });

        // Reply button functionality
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-review-id');
                const replyForm = document.getElementById('reply-form-' + reviewId);
                
                if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                    replyForm.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-times"></i><span>Cancel</span>';
                } else {
                    replyForm.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-reply"></i><span>Reply</span>';
                }
            });
        });

        // Cancel reply functionality
        document.querySelectorAll('.cancel-reply').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.getAttribute('data-review-id');
                const replyForm = document.getElementById('reply-form-' + reviewId);
                const replyBtn = document.querySelector(`.reply-btn[data-review-id="${reviewId}"]`);
                
                replyForm.style.display = 'none';
                replyBtn.innerHTML = '<i class="fas fa-reply"></i><span>Reply</span>';
            });
        });

        // Star rating functionality
        const ratingStars = document.querySelectorAll('.rating-star');
        const ratingSelect = document.getElementById('rating-select');
        
        ratingStars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                ratingSelect.value = rating;
                
                ratingStars.forEach((s, i) => {
                    if (i < rating) {
                        s.textContent = '★';
                        s.classList.add('active');
                    } else {
                        s.textContent = '☆';
                        s.classList.remove('active');
                    }
                });
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                
                ratingStars.forEach((s, i) => {
                    if (i < rating) {
                        s.textContent = '★';
                    } else {
                        s.textContent = '☆';
                    }
                });
            });
        });

        // Reset stars on mouse leave
        document.querySelector('.rating-input').addEventListener('mouseleave', function() {
            const currentRating = ratingSelect.value;
            
            ratingStars.forEach((s, i) => {
                if (i < currentRating) {
                    s.textContent = '★';
                } else {
                    s.textContent = '☆';
                }
            });
        });

        // Share functionality
        document.querySelectorAll('.share-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (navigator.share) {
                    navigator.share({
                        title: 'Café de Flour Review',
                        text: 'Check out this review for Café de Flour!',
                        url: window.location.href
                    });
                } else {
                    // Fallback: copy to clipboard
                    navigator.clipboard.writeText(window.location.href).then(() => {
                        alert('Link copied to clipboard!');
                    });
                }
            });
        });

        // Smooth scrolling for pagination
        document.querySelectorAll('.page-link-modern').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                
                // Add loading state
                document.body.style.opacity = '0.7';
                
                // Navigate after brief delay for smooth transition
                setTimeout(() => {
                    window.location.href = href;
                }, 150);
            });
        });

        // Auto-hide success messages
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Loading animation for review cards
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

        // Observe review cards for staggered animation
        document.querySelectorAll('.review-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
    </script>
</body>
</html>