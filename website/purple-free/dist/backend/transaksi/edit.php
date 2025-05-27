<?php 
include '../../../../config/koneksi.php';
session_start(); 

if (!isset($_SESSION['nama_kasir'])) {
    echo "Kasir belum login, silakan login terlebih dahulu!";
    exit;
}

$nama_kasir = strtolower($_SESSION['nama_kasir']);
$foto_kasir = "../../assets/images/faces/" . $nama_kasir . ".jpeg";
if (!file_exists($foto_kasir)) {
    $foto_kasir = "../../assets/images/faces/default.jpg";
}

if (!isset($_GET['kode'])) {
    echo "Kode transaksi tidak ditemukan.";
    exit;
}

$kode = $_GET['kode'] ?? '';

// Ambil data transaksi
$query = "
SELECT 
  t.*,
  td.id_menu,
  td.jumlah,
  td.harga_saat_transaksi,
  m.nama_menu,
  COALESCE(sm.nama_member, t.nama_pelanggan) AS nama_pelanggan
FROM transaksi t
LEFT JOIN transaksi_detail td ON t.id_transaksi = td.id_transaksi
LEFT JOIN menu m ON td.id_menu = m.id_menu
LEFT JOIN special_members sm ON t.id_pelanggan = sm.id_member
WHERE t.kode_transaksi = '$kode'
";

$result = mysqli_query($conn, $query);
$transaksi = [];
while ($row = mysqli_fetch_assoc($result)) {
    $transaksi[] = $row;
}
if (empty($transaksi)) {
    echo "Data tidak ditemukan.";
    exit;
}

$id_transaksi = $transaksi[0]['id_transaksi'];

// Ambil data menu
$menuResult = mysqli_query($conn, "SELECT * FROM menu");

// Ambil data pelanggan dari special_members
$pelangganResult = mysqli_query($conn, "SELECT * FROM special_members");

// Simpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $lokasi         = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $catatan        = mysqli_real_escape_string($conn, $_POST['catatan']);
    $bayar          = (int) $_POST['bayar'];
    $menuInput      = $_POST['menu'];

    // Ambil id_member dari special_members
    $pelangganCheck = mysqli_query($conn, "SELECT id_member FROM special_members WHERE nama_member = '$nama_pelanggan'");
    if (mysqli_num_rows($pelangganCheck) > 0) {
        $pelangganData = mysqli_fetch_assoc($pelangganCheck);
        $id_pelanggan = $pelangganData['id_member'];
    } else {
        // Insert jika belum ada
        mysqli_query($conn, "INSERT INTO special_members (nama_member) VALUES ('$nama_pelanggan')");
        $id_pelanggan = mysqli_insert_id($conn);
    }

    // Update transaksi utama
    mysqli_query($conn, "
        UPDATE transaksi SET 
            id_pelanggan = '$id_pelanggan',
            lokasi = '$lokasi',
            catatan = '$catatan',
            bayar = '$bayar'
        WHERE id_transaksi = '$id_transaksi'
    ");

    // Hapus transaksi_detail lama
    mysqli_query($conn, "DELETE FROM transaksi_detail WHERE id_transaksi = '$id_transaksi'");

    // Simpan transaksi_detail baru
    foreach ($menuInput as $item) {
        $id_menu = (int) $item['id_menu'];
        $jumlah  = (int) $item['jumlah'];

        // Ambil harga dari menu
        $hargaQuery = mysqli_query($conn, "SELECT harga FROM menu WHERE id_menu = '$id_menu'");
        $hargaRow = mysqli_fetch_assoc($hargaQuery);
        $harga_saat_transaksi = $hargaRow['harga'];

        mysqli_query($conn, "
            INSERT INTO transaksi_detail (id_transaksi, id_menu, jumlah, harga_saat_transaksi)
            VALUES ('$id_transaksi', '$id_menu', '$jumlah', '$harga_saat_transaksi')
        ");
    }

    header("Location: ../../pages/charts/chartjs.php?edit=1");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
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
                    <img src="assets/images/faces/fahis.jpeg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Fahish send you a message</h6>
                    <p class="text-gray mb-0"> 1 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/rachel.jpeg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Rachel send you a message</h6>
                    <p class="text-gray mb-0"> 15 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/bilqif.jpeg" alt="image" class="profile-pic">
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
                     <a class="nav-link" href="../../pages/tables/basic-table.php">Semua tabel data cafe</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-kasir.php">Data Kasir</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-datamenu.php">Data menu</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-datamember.php">Data member</a>
                    <a class="nav-link" href="../../pages/tables/basic-table-daftarmenu.php">Daftar menu</a>
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
              <h3 class="page-title"> Edit Transaksi </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Edit</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Transaksi</li>
                </ol>
              </nav>
            </div>
<div class="container mt-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title mb-4">Edit Transaksi: <?= htmlspecialchars($kode) ?></h4>
          <form method="POST">

            <div class="form-group">
              <label>Nama Pelanggan</label>
              <input type="text" name="nama_pelanggan" class="form-control"
              value="<?= htmlspecialchars($transaksi[0]['nama_pelanggan'] ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label>Lokasi</label>
              <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($transaksi[0]['lokasi']) ?>" required>
            </div>

            <div class="form-group">
              <label>Catatan</label>
              <input type="text" name="catatan" class="form-control" value="<?= htmlspecialchars($transaksi[0]['catatan']) ?>"></div>

            <div class="form-group">
              <label>Bayar</label>
              <input type="number" name="bayar" class="form-control" value="<?= htmlspecialchars($transaksi[0]['bayar']) ?>" required>
            </div>

            <div class="form-group">
              <label>Daftar Menu</label>
              <div id="menu-container">
                <?php foreach ($transaksi as $i => $item): ?>
                  <div class="form-row mb-2">
                    <div class="col">
                      <select name="menu[<?= $i ?>][id_menu]" class="form-control">
                        <?php mysqli_data_seek($menuResult, 0); while ($menu = mysqli_fetch_assoc($menuResult)): ?>
                          <option value="<?= $menu['id_menu'] ?>" <?= $menu['id_menu'] == $item['id_menu'] ? 'selected' : '' ?>>
                            <?= $menu['nama_menu'] ?>
                          </option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="col">
                      <input type="number" name="menu[<?= $i ?>][jumlah]" class="form-control" value="<?= $item['jumlah'] ?>" min="1" required>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="d-flex justify-content-between">
              <a href="../../pages/charts/chartjs.php" class="btn btn-secondary">Batal</a>
              <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>

          </form>
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
    <script src="../../assets/vendors/select2/select2.min.js"></script>
    <script src="../../assets/vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/misc.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <script src="../../assets/js/jquery.cookie.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="../../assets/js/file-upload.js"></script>
    <script src="../../assets/js/typeahead.js"></script>
    <script src="../../assets/js/select2.js"></script>
    <!-- End custom js for this page -->
  </body>
</html>