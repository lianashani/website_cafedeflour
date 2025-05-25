<?php
include '../../../../config/koneksi.php';

if (isset($_POST['update'])) {
  $id = $_POST['id'];
  $kode = $_POST['kode'];
  $jenis = $_POST['jenis'];
  $nilai = $_POST['nilai'];
  $aktif = $_POST['aktif'];
  $tanggal_mulai = $_POST['tanggal_mulai'];
  $tanggal_akhir = $_POST['tanggal_akhir'];

  $query = "UPDATE kode_promo SET 
            kode = '$kode',
            jenis = '$jenis',
            nilai = '$nilai',
            aktif = '$aktif',
            tanggal_mulai = '$tanggal_mulai',
            tanggal_akhir = '$tanggal_akhir'
            WHERE id = '$id'";

  $result = mysqli_query($conn, $query);

  if ($result) {
    header("Location: ../../pages/tables/basic-table-diskon.php");
    exit;
  } else {
    echo "Gagal mengupdate data: " . mysqli_error($conn);
  }
}
?>
