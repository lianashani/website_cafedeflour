<?php
include '../../../../config/koneksi.php';

$id = $_GET['id'];

// Ambil foto jika ada untuk dihapus dari folder
$data = mysqli_query($conn, "SELECT foto FROM review WHERE id_review='$id'");
$row = mysqli_fetch_assoc($data);
if ($row['foto']) {
  $path = $_SERVER['DOCUMENT_ROOT'] . "/website/assets/img/upload/review/" . $row['foto'];
  if (file_exists($path)) {
    unlink($path);
  }
}

// Hapus dari database
$hapus = mysqli_query($conn, "DELETE FROM review WHERE id_review='$id'");

if ($hapus) {
  echo "<script>
    alert('Review berhasil dihapus');
    window.location.href='../../pages/tables/basic-table-review.php';
  </script>";
} else {
  echo "<script>
    alert('Gagal menghapus review');
    window.history.back();
  </script>";
}
?>
