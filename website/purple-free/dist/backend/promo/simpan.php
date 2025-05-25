<?php
include '../../../../config/koneksi.php';

if (isset($_POST['simpan'])) {
  $kode = $_POST['kode'];
  $jenis = $_POST['jenis'];
  $nilai = $_POST['nilai'];
  $aktif = $_POST['aktif'];
  $tanggal_mulai = $_POST['tanggal_mulai'];
  $tanggal_akhir = $_POST['tanggal_akhir'];

  $query = "INSERT INTO kode_promo (kode, jenis, nilai, aktif, tanggal_mulai, tanggal_akhir) 
            VALUES ('$kode', '$jenis', '$nilai', '$aktif', '$tanggal_mulai', '$tanggal_akhir')";
  $result = mysqli_query($conn, $query);

  if ($result) {
    header("Location: ../../pages/tables/basic-table-diskon.php");
    exit;
  } else {
    echo "Gagal menyimpan data: " . mysqli_error($conn);
  }
}
?>
