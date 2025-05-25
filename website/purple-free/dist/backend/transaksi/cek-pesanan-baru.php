<?php
include '../../../../config/koneksi.php';

// Cek transaksi yang baru masuk dalam 30 detik terakhir
$cek = mysqli_query($conn, "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'Belum Diproses' AND waktu >= DATE_SUB(NOW(), INTERVAL 30 SECOND)");

$data = mysqli_fetch_assoc($cek);
echo json_encode(['total' => $data['total']]);
?>
