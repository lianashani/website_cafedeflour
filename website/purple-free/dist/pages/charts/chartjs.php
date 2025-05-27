<?php
session_start(); 
if (!isset($_SESSION['nama_kasir'])) {
  echo "Kasir belum login, silakan login terlebih dahulu!";
  exit;
}

include '../../../../../config/koneksi.php';

// Ambil data menu untuk dropdown
$menuQuery = "SELECT * FROM menu";
$menuResult = mysqli_query($conn, $menuQuery);

// Filter data
$where = [];
if (!empty($_GET['tanggal'])) {
    $tanggal = mysqli_real_escape_string($conn, $_GET['tanggal']);
    $where[] = "t.tanggal = '$tanggal'";
}
if (!empty($_GET['kasir'])) {
    $kasir = mysqli_real_escape_string($conn, $_GET['kasir']);
    $where[] = "k.nama_kasir LIKE '%$kasir%'";
}
$whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$transaksiQuery = "
SELECT 
  t.kode_transaksi,
  MAX(t.tanggal) as tanggal,
  MAX(t.waktu) as waktu,
  COALESCE(MAX(sm.nama_member), MAX(t.nama_pelanggan), '-') AS nama_pelanggan,
  MAX(t.lokasi) as lokasi,
  MAX(k.nama_kasir) as nama_kasir,
  MAX(t.catatan) as catatan,
  GROUP_CONCAT(CONCAT(m.nama_menu, ' (', td.jumlah, ')') SEPARATOR ', ') as daftar_menu,
  SUM(td.harga_saat_transaksi * td.jumlah) as total_harga,
  MAX(t.diskon) as diskon,
  MAX(t.total_setelah_diskon) as total_setelah_diskon,
  MAX(t.bayar) as bayar,
  MAX(t.kembali) as kembali,
  MAX(t.status) as status
FROM transaksi t
LEFT JOIN special_members sm ON t.id_pelanggan = sm.id_member
JOIN kasir k ON t.id_kasir = k.id_kasir
JOIN transaksi_detail td ON t.id_transaksi = td.id_transaksi
JOIN menu m ON td.id_menu = m.id_menu
$whereSQL
GROUP BY t.kode_transaksi
ORDER BY MAX(t.id_transaksi) DESC
";

$transaksiResult = mysqli_query($conn, $transaksiQuery);
if (!$transaksiResult) {
    die("Query gagal: " . mysqli_error($conn));
}

// Foto kasir
$nama_kasir = strtolower($_SESSION['nama_kasir']);
$foto_kasir = "../../assets/images/faces/" . $nama_kasir . ".jpeg";
if (!file_exists($foto_kasir)) {
  $foto_kasir = "../../assets/images/faces/default.jpg";
}

// Proses tambah transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $menus = $_POST['menu'];
  $tanggal = date('Y-m-d');
  $waktu = date('H:i:s');
  $nama_kasir = $_SESSION['nama_kasir'];

  $kasirQuery = mysqli_query($conn, "SELECT id_kasir FROM kasir WHERE nama_kasir = '$nama_kasir'");
  $kasirData = mysqli_fetch_assoc($kasirQuery);
  $id_kasir = $kasirData['id_kasir'];

  $id_member = !empty($_POST['id_pelanggan']) ? $_POST['id_pelanggan'] : 'NULL';
  $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
  $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $catatan = isset($_POST['catatan']) ? mysqli_real_escape_string($conn, $_POST['catatan']) : '';
  $bayar = (int)$_POST['bayar'];
  $diskon = isset($_POST['diskon']) ? (int)$_POST['diskon'] : 0;

  // Hitung total harga
  $totalHarga = 0;
  foreach ($menus as $menu) {
    $id_menu = $menu['id_menu'];
    $jumlah = $menu['jumlah'];

    $hargaQuery = "SELECT harga FROM menu WHERE id_menu = $id_menu";
    $hargaResult = mysqli_query($conn, $hargaQuery);
    $menuData = mysqli_fetch_assoc($hargaResult);
    $harga = $menuData['harga'];

    $subtotal = $harga * $jumlah;
    $totalHarga += $subtotal;
  }

  // Hitung total setelah diskon
  $totalSetelahDiskon = $totalHarga - $diskon;
  if ($totalSetelahDiskon < 0) $totalSetelahDiskon = 0;

  $kembali = $bayar - $totalSetelahDiskon;
  if ($kembali < 0) $kembali = 0;

  $kode_transaksi = 'TRX' . time();

  $insertTransaksi = "INSERT INTO transaksi (
    kode_transaksi, tanggal, waktu, total_harga, diskon, total_setelah_diskon,
    bayar, kembali, id_kasir, id_pelanggan, nama_pelanggan, catatan, lokasi, status
  ) 
  VALUES (
    '$kode_transaksi', '$tanggal', '$waktu', $totalHarga, $diskon, $totalSetelahDiskon,
    $bayar, $kembali, $id_kasir, " . ($id_member === 'NULL' ? "NULL" : $id_member) . ",
    '$nama_pelanggan', '$catatan', '$lokasi', 'Selesai'
  )";
  mysqli_query($conn, $insertTransaksi);

  $id_transaksi = mysqli_insert_id($conn);

  // Simpan detail
  foreach ($menus as $menu) {
    $id_menu = $menu['id_menu'];
    $jumlah = $menu['jumlah'];

    $hargaQuery = "SELECT harga FROM menu WHERE id_menu = $id_menu";
    $hargaResult = mysqli_query($conn, $hargaQuery);
    $menuData = mysqli_fetch_assoc($hargaResult);
    $harga = $menuData['harga'];

    $insertDetail = "INSERT INTO transaksi_detail (id_transaksi, id_menu, jumlah, harga_saat_transaksi) 
                     VALUES ($id_transaksi, $id_menu, $jumlah, $harga)";
    mysqli_query($conn, $insertDetail);
  }

  header("Location: chartjs.php?sukses=1");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Purple Admin</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End Plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
    
    <style>
      .menu-item {
        position: relative;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        background: #f8f9fc;
      }
      
      .menu-item .remove-menu {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        font-size: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .menu-item .remove-menu:hover {
        background: #c82333;
      }
      
      .menu-counter {
        background: #007bff;
        color: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 10px;
      }
      
      .total-display {
        background: #28a745;
        color: white;
        padding: 10px;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
        margin-top: 10px;
      }
    </style>
  </head>
  <body>
    <div class="container-scroller">
      <!-- partial:../../partials/_navbar.html -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <a class="navbar-brand brand-logo" href="../../index.php"><img src="../../assets/images/logo.svg" alt="logo" /></a>
          <a class="navbar-brand brand-logo-mini" href="../../index.php"><img src="../../assets/images/logo-mini.svg" alt="logo" /></a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button>
          <div class="search-field d-none d-md-block">
            <form class="d-flex align-items-center h-100" action="#">
              <div class="input-group">
                <div class="input-group-prepend bg-transparent">
                  <i class="input-group-text border-0 mdi mdi-magnify"></i>
                </div>
                <input type="text" class="form-control bg-transparent border-0" placeholder="Search projects">
              </div>
            </form>
          </div>
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
              <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="nav-profile-img">
                  <img src="<?php echo $foto_kasir; ?>" alt="image">
                  <span class="availability-status online"></span>
                </div>
                <div class="nav-profile-text">
                  <p class="mb-1 text-black"><?php echo htmlspecialchars($_SESSION['nama_kasir']); ?></p>
                </div>
              </a>
              <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                <a class="dropdown-item" href="#">
                  <i class="mdi mdi-cached me-2 text-success"></i> Activity Log </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="/website/index.php">
                  <i class="mdi mdi-logout me-2 text-primary"></i> Signout </a>
              </div>
            </li>
            <li class="nav-item d-none d-lg-block full-screen-link">
              <a class="nav-link">
                <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="mdi mdi-email-outline"></i>
                <span class="count-symbol bg-warning"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                <h6 class="p-3 mb-0">Messages</h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="../../assets/images/faces/fahis.jpeg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Fahish send you a message</h6>
                    <p class="text-gray mb-0"> 1 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="../../assets/images/faces/rachel.jpeg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Rachel send you a message</h6>
                    <p class="text-gray mb-0"> 15 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="../../assets/images/faces/bilqif.jpeg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Bilqif Profile picture updated</h6>
                    <p class="text-gray mb-0"> 18 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <h6 class="p-3 mb-0 text-center">4 new messages</h6>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="mdi mdi-bell-outline"></i>
                <span class="count-symbol bg-danger"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                <h6 class="p-3 mb-0">Notifications</h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-success">
                      <i class="mdi mdi-calendar"></i>
                    </div>
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject font-weight-normal mb-1">Event today</h6>
                    <p class="text-gray ellipsis mb-0"> Just a reminder that you have an event today </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-warning">
                      <i class="mdi mdi-cog"></i>
                    </div>
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject font-weight-normal mb-1">Settings</h6>
                    <p class="text-gray ellipsis mb-0"> Update dashboard </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-info">
                      <i class="mdi mdi-link-variant"></i>
                    </div>
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject font-weight-normal mb-1">Launch Admin</h6>
                    <p class="text-gray ellipsis mb-0"> New admin wow! </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <h6 class="p-3 mb-0 text-center">See all notifications</h6>
              </div>
            </li>
            <li class="nav-item nav-logout d-none d-lg-block">
              <a class="nav-link" href="#">
                <i class="mdi mdi-power"></i>
              </a>
            </li>
            <li class="nav-item nav-settings d-none d-lg-block">
              <a class="nav-link" href="#">
                <i class="mdi mdi-format-line-spacing"></i>
              </a>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:../../partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item nav-profile">
              <a href="#" class="nav-link">
                <div class="nav-profile-image">
                  <img src="<?php echo $foto_kasir; ?>" alt="profile" />
                  <span class="login-status online"></span>
                  <!--change to offline or busy as needed-->
                </div>
                <div class="nav-profile-text d-flex flex-column">
                  <span class="font-weight-bold mb-2"><?php echo htmlspecialchars($_SESSION['nama_kasir']); ?></span>
                  <span class="text-secondary text-small">Admin Cafe de Flour</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../../index.php">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
              </a>
            </li>
           
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                <span class="menu-title">Orders</span>
                <i class="mdi mdi-chart-bar menu-icon"></i>
              </a>
              <div class="collapse" id="charts">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item">
                    <a class="nav-link" href="../../pages/charts/chartjs.php">Orders</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
                <span class="menu-title">Tables</span>
                <i class="mdi mdi-table-large menu-icon"></i>
              </a>
              <div class="collapse" id="tables">
                <ul class="nav flex-column sub-menu">
                 <li class="nav-item">
                    <a class="nav-link" href="../../pages/tables/basic-table-kasir.php">Data Kasir</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-datamember.php">Data member</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-daftarmenu.php">Daftar menu</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-review.php">Review</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-diskon.php">Kode promo</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-transaksi.php">Riwayat Transaksi</a>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </nav>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title">Orders</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Orders</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
              </nav>
            </div>
            <div class="row">
              <div class="container mt-4">
                <div class="row">
                  <!-- Form Tambah Transaksi -->
                  <div class="col-lg-6 grid-margin stretch-card">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Tambah Transaksi</h4>
                        <form action="/website/purple-free/dist/backend/transaksi/simpan.php" method="POST" class="forms-sample">

                          <?php if (isset($_GET['sukses'])): ?>
                            <div class="alert alert-success">Transaksi berhasil ditambahkan!</div>
                          <?php endif; ?>

                          <div class="form-group">
                            <label for="id_pelanggan">Pilih Member</label>
                            <select name="id_pelanggan" id="id_pelanggan" class="form-control">
                              <option value="">Bukan Member</option>
                              <?php
                                $members = mysqli_query($conn, "SELECT * FROM special_members");
                                while ($row = mysqli_fetch_assoc($members)) {
                                  echo "<option value='{$row['id_member']}'>" . htmlspecialchars($row['nama_member']) . " - {$row['nomor_hp']} ({$row['tingkatan']})</option>";
                                }
                              ?>
                            </select>
                          </div>

                          <div class="form-group">
                            <label for="nama_pelanggan">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" id="nama_pelanggan" class="form-control" placeholder="Nama Pelanggan" required>
                          </div>

                          <div class="form-group">
                            <label for="lokasi">Lokasi</label>
                            <select name="lokasi" class="form-control" required>
                              <option value="Dine In">Dine In</option>
                              <option value="Take Away">Take Away</option>
                            </select>
                          </div>

                          <div class="form-group">
                            <label for="menu">Pilih Menu</label>
                            <div id="menu-container">
                              <div class="menu-item" data-index="0">
                                <div class="menu-counter">1</div>
                                <select name="menu[0][id_menu]" class="form-control menu-select" required>
                                  <option value="">Pilih Menu</option>
                                  <?php
                                  mysqli_data_seek($menuResult, 0);
                                  while ($menu = mysqli_fetch_assoc($menuResult)):
                                    $nama = htmlspecialchars($menu['nama_menu']);
                                    $harga = number_format($menu['harga'], 0, ',', '.');
                                    $stok = (int) $menu['stok'];
                                    $disabled = $stok <= 0 ? 'disabled' : '';
                                  ?>
                                    <option value="<?= $menu['id_menu']?>" data-harga="<?= $menu['harga'] ?>" data-stok="<?= $stok ?>" <?= $disabled ?>>
                                      <?= "$nama - Rp$harga (Stok: $stok)" ?>
                                    </option>
                                  <?php endwhile; ?>
                                </select>
                                <input type="number" name="menu[0][jumlah]" class="form-control mt-2 jumlah-input" placeholder="Jumlah" min="1" required>
                              </div>
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="add-menu">
                              <i class="mdi mdi-plus"></i> Tambah Menu
                            </button>
                            <button type="button" class="btn btn-outline-danger mt-2" id="clear-all-menu" style="display: none;">
                              <i class="mdi mdi-delete"></i> Hapus Semua
                            </button>
                          </div>

                          <div class="form-group">
                            <label for="catatan">Catatan</label>
                            <textarea name="catatan" class="form-control" placeholder="Catatan tambahan..." rows="2"></textarea>
                          </div>

                          <div class="form-group">
                            <label for="total_harga">Total Harga</label>
                            <input type="text" name="total_harga" id="total_harga" class="form-control" readonly>
                            <div class="total-display" id="total-display" style="display: none;">
                              Total: Rp <span id="total-amount">0</span>
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="bayar">Bayar (Rp)</label>
                            <input type="number" name="bayar" id="bayar" class="form-control" min="0" required>
                            <small class="form-text text-muted" id="kembalian-info"></small>
                          </div>

                          <button type="submit" class="btn btn-gradient-primary me-2">
                            <i class="mdi mdi-content-save"></i> Tambah Transaksi
                          </button>
                          <button type="button" class="btn btn-outline-secondary" id="reset-form">
                            <i class="mdi mdi-refresh"></i> Reset Form
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-6 grid-margin stretch-card">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Riwayat Transaksi</h4>
                        <form class="form-inline mb-3" method="GET">
                          <input type="date" name="tanggal" class="form-control mr-2" value="<?= isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : '' ?>">
                          <input type="text" name="kasir" class="form-control mr-2" placeholder="Nama kasir" value="<?= isset($_GET['kasir']) ? htmlspecialchars($_GET['kasir']) : '' ?>">
                          <button type="submit" class="btn btn-primary">Filter</button>
                          <a href="chartjs.php" class="btn btn-secondary ml-2">Reset</a>
                          <a href="../../backend/transaksi/antrian.php" class="btn btn-primary"> Lihat Antrian Pesanan</a>
                          <a href="../../backend/transaksi/tracking.php" class="btn btn-outline-success mt-3"> Lacak Pesanan Customer</a>
                        </form>

                        <form method="POST" action="../../backend/transaksi/aksi_batch.php" id="batchForm">
                          <div class="mb-3">
                            <!-- Tombol untuk toggle checkbox -->
                            <button type="button" class="btn btn-danger btn-sm" id="toggleCheckboxBtn">Hapus Terpilih</button>

                            <!-- Tombol submit hapus yang hanya muncul saat checkbox aktif -->
                            <button type="submit" name="hapus_terpilih" id="submitHapusBtn" class="btn btn-danger btn-sm d-none" onclick="return confirm('Yakin ingin menghapus yang dipilih?')">Konfirmasi Hapus</button>
                            <a href="../../backend/transaksi/clear_all.php" class="btn btn-outline-danger btn-sm ml-2" onclick="return confirm('Yakin ingin menghapus semua transaksi?')">Clear All</a>
                          </div>

                          <div class="table-responsive">
                            <table class="table table-striped">
                              <thead>
                                <tr>
                                  <th class="select-col d-none"><input type="checkbox" id="checkAll"></th>
                                  <th>Tanggal</th>
                                  <th>Waktu</th>
                                  <th>Nama</th>
                                  <th>Lokasi</th>
                                  <th>Menu</th>
                                  <th>Subtotal</th>
                                  <th>Diskon</th>
                                  <th>Total</th>
                                  <th>Bayar</th>
                                  <th>Kembali</th>
                                  <th>Kasir</th>
                                  <th>Catatan</th>
                                  <th>Struk</th>
                                  <th>Aksi</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php while ($row = mysqli_fetch_assoc($transaksiResult)): ?>
                                <tr>
                                  <td class="select-col d-none">
                                    <input type="checkbox" name="kode_transaksi[]" value="<?= $row['kode_transaksi'] ?>">
                                  </td>
                                  <td><?= $row['tanggal'] ?></td>
                                  <td><?= $row['waktu'] ?></td>
                                  <td><?= $row['nama_pelanggan'] ?></td>
                                  <td><?= $row['lokasi'] ?></td>
                                  <td><?= $row['daftar_menu'] ?></td>
                                  <td>Rp<?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                  <td>Rp<?= number_format($row['diskon'], 0, ',', '.') ?></td>
                                  <td>Rp<?= number_format($row['total_setelah_diskon'], 0, ',', '.') ?></td>
                                  <td>Rp<?= number_format($row['bayar'], 0, ',', '.') ?></td>
                                  <td>Rp<?= number_format($row['kembali'], 0, ',', '.') ?></td>
                                  <td><?= $row['nama_kasir'] ?></td>
                                  <td><?= $row['catatan'] ?></td>
                                  <td><a href="../../backend/transaksi/cetak_struk.php?kode=<?= $row['kode_transaksi'] ?>" target="_blank">🧾</a></td>
                                  <td>
                                    <a href="../../backend/transaksi/edit.php?kode=<?= $row['kode_transaksi'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="../../backend/transaksi/hapus.php?kode=<?= $row['kode_transaksi'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus transaksi ini?')">Hapus</a>
                                  </td>
                                  <td>
                                    <?php if ($row['status'] === 'Selesai'): ?>
                                      <span class="badge badge-success"><?= $row['status'] ?></span>
                                    <?php elseif ($row['status'] === 'Pending'): ?>
                                      <span class="badge badge-warning"><?= $row['status'] ?></span>
                                    <?php else: ?>
                                      <span class="badge badge-danger"><?= $row['status'] ?></span>
                                    <?php endif; ?>
                                  </td>
                                </tr>
                                <?php endwhile; ?>
                              </tbody>
                            </table>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    
    <!-- plugins:js -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="../../assets/vendors/chart.js/chart.umd.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/misc.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <script src="../../assets/js/jquery.cookie.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="../../assets/js/chart.js"></script>
    
    <!-- CONSOLIDATED JAVASCRIPT - NO DUPLICATES -->
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Global variables
        let menuIndex = 1;
        const menuContainer = document.getElementById('menu-container');
        const totalHargaField = document.getElementById('total_harga');
        const totalDisplay = document.getElementById('total-display');
        const totalAmount = document.getElementById('total-amount');
        const addMenuBtn = document.getElementById('add-menu');
        const clearAllMenuBtn = document.getElementById('clear-all-menu');
        const resetFormBtn = document.getElementById('reset-form');
        const bayarInput = document.getElementById('bayar');
        const kembalianInfo = document.getElementById('kembalian-info');
        const memberSelect = document.getElementById('id_pelanggan');
        const namaPelangganInput = document.getElementById('nama_pelanggan');

        // Menu options template
        const menuOptionsTemplate = `
          <?php
          mysqli_data_seek($menuResult, 0);
          $options = '<option value="">Pilih Menu</option>';
          while ($menu = mysqli_fetch_assoc($menuResult)):
            $nama = htmlspecialchars($menu['nama_menu']);
            $harga = number_format($menu['harga'], 0, ',', '.');
            $stok = (int) $menu['stok'];
            $disabled = $stok <= 0 ? 'disabled' : '';
            $options .= '<option value="' . $menu['id_menu'] . '" data-harga="' . $menu['harga'] . '" data-stok="' . $stok . '" ' . $disabled . '>' . $nama . ' - Rp' . $harga . ' (Stok: ' . $stok . ')</option>';
          endwhile;
          echo $options;
          ?>
        `;

        // Add menu functionality
        addMenuBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Prevent double execution
          if (addMenuBtn.disabled) return;
          addMenuBtn.disabled = true;
          
          setTimeout(() => {
            addMenuBtn.disabled = false;
          }, 300);

          const newMenu = document.createElement('div');
          newMenu.classList.add('menu-item');
          newMenu.setAttribute('data-index', menuIndex);
          
          newMenu.innerHTML = `
            <div class="menu-counter">${menuIndex + 1}</div>
            <button type="button" class="remove-menu" onclick="removeMenuItem(${menuIndex})">×</button>
            <select name="menu[${menuIndex}][id_menu]" class="form-control menu-select" required>
              ${menuOptionsTemplate}
            </select>
            <input type="number" name="menu[${menuIndex}][jumlah]" class="form-control mt-2 jumlah-input" placeholder="Jumlah" min="1" required>
          `;
          
          menuContainer.appendChild(newMenu);
          menuIndex++;
          
          updateMenuCounters();
          updateClearButtonVisibility();
          hitungTotal();
        });

        // Remove menu item function (global scope)
        window.removeMenuItem = function(index) {
          const menuItem = document.querySelector(`[data-index="${index}"]`);
          if (menuItem) {
            menuItem.remove();
            updateMenuCounters();
            updateClearButtonVisibility();
            hitungTotal();
          }
        };

        // Clear all menu items
        clearAllMenuBtn.addEventListener('click', function() {
          if (confirm('Hapus semua menu yang ditambahkan?')) {
            const menuItems = menuContainer.querySelectorAll('.menu-item');
            menuItems.forEach((item, index) => {
              if (index > 0) { // Keep the first menu item
                item.remove();
              }
            });
            
            // Reset first menu item
            const firstMenuItem = menuContainer.querySelector('.menu-item');
            if (firstMenuItem) {
              firstMenuItem.querySelector('select').value = '';
              firstMenuItem.querySelector('input').value = '';
            }
            
            menuIndex = 1;
            updateMenuCounters();
            updateClearButtonVisibility();
            hitungTotal();
          }
        });

        // Update menu counters
        function updateMenuCounters() {
          const menuItems = menuContainer.querySelectorAll('.menu-item');
          menuItems.forEach((item, index) => {
            const counter = item.querySelector('.menu-counter');
            if (counter) {
              counter.textContent = index + 1;
            }
          });
        }

        // Update clear button visibility
        function updateClearButtonVisibility() {
          const menuItems = menuContainer.querySelectorAll('.menu-item');
          clearAllMenuBtn.style.display = menuItems.length > 1 ? 'inline-block' : 'none';
        }

        // Calculate total price
        function hitungTotal() {
          let total = 0;
          const menuItems = document.querySelectorAll('#menu-container .menu-item');

          menuItems.forEach(item => {
            const selectMenu = item.querySelector('select');
            const jumlah = parseInt(item.querySelector('input[type="number"]').value) || 0;
            const selectedOption = selectMenu.options[selectMenu.selectedIndex];
            const harga = parseInt(selectedOption?.dataset.harga) || 0;
            total += jumlah * harga;
          });

          const formattedTotal = total.toLocaleString('id-ID');
          totalHargaField.value = total > 0 ? 'Rp' + formattedTotal : '';
          totalAmount.textContent = formattedTotal;
          totalDisplay.style.display = total > 0 ? 'block' : 'none';
          
          updateKembalian();
        }

        // Update kembalian calculation
        function updateKembalian() {
          const total = getCurrentTotal();
          const bayar = parseInt(bayarInput.value) || 0;
          
          if (bayar > 0 && total > 0) {
            const kembalian = bayar - total;
            if (kembalian >= 0) {
              kembalianInfo.textContent = `Kembalian: Rp ${kembalian.toLocaleString('id-ID')}`;
              kembalianInfo.className = 'form-text text-success';
            } else {
              kembalianInfo.textContent = `Kurang: Rp ${Math.abs(kembalian).toLocaleString('id-ID')}`;
              kembalianInfo.className = 'form-text text-danger';
            }
          } else {
            kembalianInfo.textContent = '';
          }
        }

        // Get current total as number
        function getCurrentTotal() {
          let total = 0;
          const menuItems = document.querySelectorAll('#menu-container .menu-item');
          
          menuItems.forEach(item => {
            const selectMenu = item.querySelector('select');
            const jumlah = parseInt(item.querySelector('input[type="number"]').value) || 0;
            const selectedOption = selectMenu.options[selectMenu.selectedIndex];
            const harga = parseInt(selectedOption?.dataset.harga) || 0;
            total += jumlah * harga;
          });
          
          return total;
        }

        // Member selection handling
        function toggleNamaPelanggan() {
          if (memberSelect.value !== "") {
            namaPelangganInput.disabled = true;
            namaPelangganInput.required = false;
            namaPelangganInput.value = "";
          } else {
            namaPelangganInput.disabled = false;
            namaPelangganInput.required = true;
          }
        }

        // Reset form
        resetFormBtn.addEventListener('click', function() {
          if (confirm('Reset semua data form?')) {
            document.querySelector('form').reset();
            
            // Clear additional menu items
            const menuItems = menuContainer.querySelectorAll('.menu-item');
            menuItems.forEach((item, index) => {
              if (index > 0) {
                item.remove();
              }
            });
            
            menuIndex = 1;
            updateMenuCounters();
            updateClearButtonVisibility();
            hitungTotal();
            toggleNamaPelanggan();
          }
        });

        // Event listeners
        menuContainer.addEventListener('change', function(e) {
          if (e.target.classList.contains('menu-select')) {
            const selectedOption = e.target.selectedOptions[0];
            const stok = selectedOption.getAttribute('data-stok');
            const jumlahInput = e.target.nextElementSibling;
            if (stok) {
              jumlahInput.setAttribute('max', stok);
            }
          }
          hitungTotal();
        });

        menuContainer.addEventListener('input', hitungTotal);
        bayarInput.addEventListener('input', updateKembalian);
        memberSelect.addEventListener('change', toggleNamaPelanggan);

        // Checkbox functionality
        const checkAll = document.getElementById('checkAll');
        if (checkAll) {
          checkAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="kode_transaksi[]"]');
            for (const cb of checkboxes) {
              cb.checked = this.checked;
            }
          });
        }

        // Toggle delete mode
        const toggleBtn = document.getElementById('toggleCheckboxBtn');
        const submitBtn = document.getElementById('submitHapusBtn');
        const selectCols = document.querySelectorAll('.select-col');
        let checkboxMode = false;

        if (toggleBtn) {
          toggleBtn.addEventListener('click', () => {
            checkboxMode = !checkboxMode;

            selectCols.forEach(col => {
              col.classList.toggle('d-none', !checkboxMode);
            });

            if (submitBtn) {
              submitBtn.classList.toggle('d-none', !checkboxMode);
            }

            toggleBtn.textContent = checkboxMode ? 'Batal Hapus' : 'Hapus Terpilih';
          });
        }

        // Initialize
        toggleNamaPelanggan();
        updateClearButtonVisibility();
        hitungTotal();
      });
    </script>
  </body>
</html>
