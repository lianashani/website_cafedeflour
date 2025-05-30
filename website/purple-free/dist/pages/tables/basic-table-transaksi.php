<?php 
session_start(); 

if (!isset($_SESSION['nama_kasir'])) {
    echo "Kasir belum login, silakan login terlebih dahulu!";
    exit;
}

include '../../../../config/koneksi.php';

$notif = "";
if (isset($_SESSION['notif'])) {
  $notif = $_SESSION['notif'];
  unset($_SESSION['notif']);
}


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
  COALESCE(MAX(sm.nama_member), MAX(t.nama_pelanggan), '-') AS nama_pelanggan,
  MAX(t.lokasi) as lokasi,
  MAX(k.nama_kasir) as nama_kasir,
  MAX(t.catatan) as catatan,
  GROUP_CONCAT(CONCAT(m.nama_menu, ' (', td.jumlah, ')') SEPARATOR ', ') as daftar_menu,
  SUM(td.harga_saat_transaksi * td.jumlah) as total_harga,
  MAX(t.bayar) as bayar,
  MAX(t.kembali) as kembali,
  MAX(t.status) as status
FROM transaksi t
LEFT JOIN special_members sm ON t.id_pelanggan = sm.id_member
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

<!-- Tampilkan notif -->
<?php if ($notif): ?>
  <div class="alert alert-info"><?= $notif ?></div>
<?php endif; ?>

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
                <a class="dropdown-item" href="/website/index.php">
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
              <span class="count-symbol bg-danger" id="notificationCount" style="display: none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="notificationDropdown" id="notificationList">
              <h6 class="p-3 mb-0">Notifications</h6>
              <div class="dropdown-divider"></div>
              <div id="notificationItems">
                <p class="text-center text-muted mb-0">Tidak ada notifikasi baru</p>
              </div>
              <div class="dropdown-divider"></div>
              <a href="/website/purple-free/dist/backend/transaksi/antrian.php" class="p-3 mb-0 text-center d-block text-primary" style="text-decoration: none;">Lihat Semua</a>
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
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="page-header">
              <h3 class="page-title"> Data transaksi </h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#">Tables</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Data transaksi</li>
                </ol>
              </nav>
            </div>
              <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
              <?php
            $pesananBaru = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id_transaksi DESC LIMIT 5");
            ?>
            <div class="card-body">
              <h4 class="card-title">Riwayat Transaksi</h4>
          

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
                <th>Total</th>
                <th>Bayar</th>
                <th>Kembali</th>
                <th>Kasir</th>
                <th>catatan</th>
                <th>Struk</th>
                <th>Aksi </th>
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
     <script>
    function fetchNotifications() {
      fetch('backend/notif/get-notification.php')
        .then(response => response.json())
        .then(data => {
          const list = document.getElementById('notificationItems');
          const count = document.getElementById('notificationCount');
          list.innerHTML = '';

          if (data.length > 0) {
            count.style.display = 'inline-block';
            count.innerText = data.length;

            data.forEach(item => {
              list.innerHTML += `
                <a href="backend/transaksi/antrian.php" class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-success">
                      <i class="mdi mdi-cart"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal mb-1">Pesanan Baru dari ${item.nama}</h6>
                    <p class="text-gray ellipsis mb-0">Kode: ${item.kode}</p>
                    <p class="text-gray ellipsis mb-0">Waktu: ${item.waktu}</p>
                    <p class="text-gray ellipsis mb-0">Lokasi: ${item.lokasi}</p>
                    <p class="text-gray ellipsis mb-0">Catatan: ${item.catatan || '-'}</p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
              `;
            });

          } else {
            count.style.display = 'none';
            list.innerHTML = `<p class="text-center text-muted mb-0">Tidak ada notifikasi baru</p>`;
          }
        })
        .catch(err => {
          console.error('Gagal mengambil notifikasi:', err);
        });
    }

    // Fetch setiap 5 detik
    setInterval(fetchNotifications, 5000);
    fetchNotifications(); // Panggil sekali saat pertama load
    </script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <!-- End custom js for this page -->
  </body>
</html>
