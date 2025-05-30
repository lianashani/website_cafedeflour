<?php
session_start(); 
if (!isset($_SESSION['nama_kasir'])) {
  echo "Kasir belum login, silakan login terlebih dahulu!";
  exit;
}

include '../../../../../config/koneksi.php';

// Ambil filter status dari URL (jika ada)
$status_filter = $_GET['status'] ?? '';

// Query transaksi sesuai filter
$query = "SELECT * FROM transaksi WHERE status != 'Selesai'";
if ($status_filter !== '') {
    $query = "SELECT * FROM transaksi WHERE status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}
$query .= " ORDER BY id_transaksi DESC";
$result = mysqli_query($conn, $query);

// Untuk foto kasir
$nama_kasir = strtolower($_SESSION['nama_kasir']);
$foto_kasir = "../../assets/images/faces/" . $nama_kasir . ".jpeg";
if (!file_exists($foto_kasir)) {
  $foto_kasir = "../../assets/images/faces/default.jpg";
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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
    .highlight-baru {
    background-color: #fff3cd !important;
    animation: pulse 1s infinite alternate;
    }

    @keyframes pulse {
    from { box-shadow: 0 0 0 rgba(255, 193, 7, 0.3); }
    to { box-shadow: 0 0 10px rgba(255, 193, 7, 0.8); }
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
                <span class="me\\nu-title">Dashboard</span>
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
                  <li class="breadcrumb-item"><a href="#">Antrian Pesanan</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
              </nav>
            </div>
            
    <div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title mb-4">Antrian Pesanan</h3>

            <!-- Tombol Filter -->
            <div class="mb-4 d-flex flex-wrap gap-2">
                <a href="antrian.php" class="btn btn-outline-secondary btn-sm <?= $status_filter == '' ? 'active' : '' ?>">Semua</a>
                <a href="?status=Belum Diproses" class="btn btn-outline-primary btn-sm <?= $status_filter == 'Belum Diproses' ? 'active' : '' ?>">Belum Diproses</a>
                <a href="?status=Sedang Disiapkan" class="btn btn-outline-warning btn-sm <?= $status_filter == 'Sedang Disiapkan' ? 'active' : '' ?>">Sedang Disiapkan</a>
                <a href="?status=Siap Diambil" class="btn btn-outline-info btn-sm <?= $status_filter == 'Siap Diambil' ? 'active' : '' ?>">Siap Diambil</a>
                <a href="?status=Selesai" class="btn btn-outline-success btn-sm <?= $status_filter == 'Selesai' ? 'active' : '' ?>">Selesai</a>
            </div>

            <!-- Tabel -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Lokasi</th>
                            <th>Waktu</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Ubah Status</th>
                            <th>Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><strong><?= $row['kode_transaksi'] ?></strong></td>
                                <td><?= $row['nama_pelanggan'] ?></td>
                                <td><?= $row['lokasi'] ?></td>
                                <td><?= $row['tanggal'] ?> <?= $row['waktu'] ?></td>
                                <td><?= $row['catatan'] ?></td>
                                <td>
                                    <span class="badge text-dark
                                        <?= $row['status'] == 'Belum Diproses' ? 'bg-secondary text-dark highlight-baru' : '' ?>
                                        <?= $row['status'] == 'Sedang Disiapkan' ? 'bg-warning text-dark' : '' ?>
                                        <?= $row['status'] == 'Siap Diambil' ? 'bg-info text-light' : '' ?>
                                        <?= $row['status'] == 'Selesai' ? 'bg-success' : '' ?>">
                                        <?= $row['status'] ?>
                                    </span>


                                </td>
                                <td>
                                    <form method="POST" action="../../backend/transaksi/update_status.php">
                                        <input type="hidden" name="id" value="<?= $row['id_transaksi'] ?>">
                                        <div class="input-group input-group-sm">
                                            <select name="status" class="form-select">
                                                <option value="Belum Diproses" <?= $row['status'] == 'Belum Diproses' ? 'selected' : '' ?>>Belum Diproses</option>
                                                <option value="Sedang Disiapkan" <?= $row['status'] == 'Sedang Disiapkan' ? 'selected' : '' ?>>Sedang Disiapkan</option>
                                                <option value="Siap Diambil" <?= $row['status'] == 'Siap Diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                                                <option value="Selesai" <?= $row['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="../../backend/transaksi/update_status.php" onsubmit="return confirm('Tandai pesanan ini sebagai selesai?')">
                                        <input type="hidden" name="id" value="<?= $row['id_transaksi'] ?>">
                                        <input type="hidden" name="status" value="Selesai">
                                        <button class="btn btn-success btn-sm"> Selesaikan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data antrian ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    <!-- <script src="../../pages/charts/menu.js"></script> -->
   

    <!-- End custom js for this page -->
  </body>
</html>