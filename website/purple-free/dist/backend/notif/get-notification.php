<?php
header('Content-Type: application/json');
include '../../../../config/koneksi.php';

$query = "SELECT * FROM transaksi WHERE status = 'diproses' ORDER BY tanggal DESC, waktu DESC";
$result = mysqli_query($conn, $query);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = [
        'kode' => $row['kode_transaksi'],
        'nama' => $row['nama_pelanggan'],
        'waktu' => substr($row['waktu'], 0, 5), // jam:menit
        'lokasi' => $row['lokasi'],
        'catatan' => $row['catatan'] ?? '-'
    ];
}

echo json_encode($notifications);
