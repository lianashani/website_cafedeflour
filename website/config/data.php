<?php
include 'koneksi.php';
// $kasir = $_SESSION['id_kasir'];
// $nkasir = $_SESSION['nama'];
// $lkasir = $_SESSION['level'];
$q1 = mysqli_query($conn, "SELECT COUNT(*) as jumlah_menu FROM menu");
$q2 = mysqli_query($conn, "SELECT COUNT(*) as jumlah_transaksi FROM transaksi");
$d1 = mysqli_fetch_array($q1);
$d2 = mysqli_fetch_array($q2);

$jumlah_menu = $d1['jumlah_menu'];
$jumlah_transaksi = $d2['jumlah_transaksi'];
?>