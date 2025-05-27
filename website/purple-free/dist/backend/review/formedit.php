<?php 
session_start(); 

if (!isset($_SESSION['nama_kasir'])) {
    echo "Kasir belum login, silakan login terlebih dahulu!";
    exit;
}

include '../../../../config/koneksi.php';

$nama_kasir = strtolower($_SESSION['nama_kasir']);
$foto_kasir = "../../assets/images/faces/" . $nama_kasir . ".jpeg";

// Fallback jika foto tidak ada
if (!file_exists($foto_kasir)) {
  $foto_kasir = "../../assets/images/faces/default.jpg";
}

// Inisialisasi filter
$keyword = $_GET['keyword'] ?? '';
$kategori = $_GET['kategori'] ?? '';

// Query dinamis
$query = "SELECT * FROM menu WHERE 1=1";

if ($keyword) {
    $query .= " AND nama_menu LIKE '%$keyword%'";
}
if ($kategori) {
    $query .= " AND kategori = '$kategori'";
}

$result = mysqli_query($conn, $query);


$transaksiQuery = "
SELECT 
  t.kode_transaksi,
  MAX(t.tanggal) as tanggal,
  MAX(t.waktu) as waktu,
  COALESCE(MAX(p.nama), MAX(t.nama_pelanggan), '-') AS nama_pelanggan,
  MAX(t.lokasi) as lokasi,
  MAX(k.nama_kasir) as nama_kasir,
  MAX(t.catatan) as catatan,
  GROUP_CONCAT(CONCAT(m.nama_menu, ' (', td.jumlah, ')') SEPARATOR ', ') as daftar_menu,
  SUM(td.harga_saat_transaksi * td.jumlah) as total_harga,
  MAX(t.bayar) as bayar,
  MAX(t.kembali) as kembali,
  MAX(t.status) as status
FROM transaksi t
LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
JOIN kasir k ON t.id_kasir = k.id_kasir
JOIN transaksi_detail td ON t.id_transaksi = td.id_transaksi
JOIN menu m ON td.id_menu = m.id_menu
GROUP BY t.kode_transaksi
ORDER BY MAX(t.id_transaksi) DESC
";

$transaksiResult = mysqli_query($conn, $transaksiQuery);
if (!$transaksiResult) {
    die("Query gagal: " . mysqli_error($conn));
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
        </nav>
        <!-- partial -->
<?php
$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM review WHERE id_review='$id'");
$row = mysqli_fetch_assoc($data);
?>

<div class="main-panel">
  <div class="content-wrapper">
    <div class="page-header">
      <h3 class="page-title">Edit Review</h3>
    </div>

    <div class="row">
      <div class="col-lg-8 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <form action="../../backend/review/update.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id_review" value="<?= $row['id_review']; ?>">

              <div class="form-group">
                <label>Nama Pelanggan</label>
                <input type="text" name="nama_pelanggan" class="form-control" value="<?= htmlspecialchars($row['nama_pelanggan']); ?>" required>
              </div>

              <div class="form-group">
                <label>Rating</label>
                <input type="number" name="rating" min="1" max="5" class="form-control" value="<?= $row['rating']; ?>" required>
              </div>

              <div class="form-group">
                <label>Komentar</label>
                <textarea name="komentar" class="form-control" rows="4" required><?= htmlspecialchars($row['komentar']); ?></textarea>
              </div>

              <div class="form-group">
                <label>Foto Saat Ini</label><br>
                <?php if ($row['foto']) { ?>
                  <img src="/website/assets/img/upload/review/<?= $row['foto']; ?>" style="height: 80px; border-radius: 6px;">
                <?php } else {
                  echo '-';
                } ?>
              </div>

              <div class="form-group">
                <label>Ganti Foto (opsional)</label>
                <input type="file" name="foto" class="form-control">
              </div>

              <button type="submit" name="update" class="btn btn-primary">Update</button>
              <a href="../../pages/tables/basic-table-review.php" class="btn btn-secondary">Batal</a>
            </form>

            <?php
            if (isset($_POST['update'])) {
              $nama = $_POST['nama_pelanggan'];
              $rating = $_POST['rating'];
              $komentar = $_POST['komentar'];

              // Handle file upload
              if ($_FILES['foto']['name']) {
                $foto = $_FILES['foto']['name'];
                $tmp = $_FILES['foto']['tmp_name'];
                move_uploaded_file($tmp, "../../assets/img/upload/review/$foto");
                $update = mysqli_query($conn, "UPDATE review SET nama_pelanggan='$nama', rating='$rating', komentar='$komentar', foto='$foto' WHERE id_review='$id'");
              } else {
                $update = mysqli_query($conn, "UPDATE review SET nama_pelanggan='$nama', rating='$rating', komentar='$komentar' WHERE id_review='$id'");
              }

              if ($update) {
                echo "<script>alert('Berhasil update review'); location.href='data-review.php';</script>";
              } else {
                echo "<script>alert('Gagal update review');</script>";
              }
            }
            ?>

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
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/misc.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <script src="../../assets/js/jquery.cookie.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <!-- End custom js for this page -->
  </body>
</html>
