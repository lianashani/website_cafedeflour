<?php
include '../../../../config/koneksi.php';

// Hapus semua transaksi_detail dulu
mysqli_query($conn, "DELETE FROM transaksi_detail");

// Baru hapus semua transaksi
mysqli_query($conn, "DELETE FROM transaksi");

header("Location: ../../pages/charts/chartjs.php?clearall=1");
exit;
