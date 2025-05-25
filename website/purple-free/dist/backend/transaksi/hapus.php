<?php
session_start();
include '../../../../config/koneksi.php';

if (isset($_GET['kode'])) {
  $kode = mysqli_real_escape_string($conn, $_GET['kode']);

  $deleteQuery = "DELETE FROM transaksi WHERE kode_transaksi = '$kode'";
  mysqli_query($conn, $deleteQuery);

  // Redirect ke halaman chartjs yang benar
  header("Location: ../../pages/charts/chartjs.php?hapus=1");
  exit;
} else {
  // Redirect jika kode tidak ada
  header("Location: ../../pages/charts/chartjs.php?hapus=0");
  exit;
}
