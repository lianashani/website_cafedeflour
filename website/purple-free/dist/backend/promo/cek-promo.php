<?php
include '../../../../config/koneksi.php';

$kode = strtoupper($_GET['kode'] ?? '');

$response = ['valid' => false];

if ($kode) {
  $query = mysqli_query($conn, "
    SELECT * FROM kode_promo 
    WHERE UPPER(kode) = '$kode'
    AND aktif = 1
    AND (CURDATE() BETWEEN tanggal_mulai AND tanggal_akhir)
    LIMIT 1
  ");
  
  if ($row = mysqli_fetch_assoc($query)) {
    $response = [
      'valid' => true,
      'jenis' => $row['jenis'],
      'nilai' => (int)$row['nilai']
    ];
  }
}

echo json_encode($response);
?>
