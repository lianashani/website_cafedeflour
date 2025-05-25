<?php
include '../../../../config/koneksi.php';

if (isset($_POST['update'])) {
  $id = $_POST['id_review'];
  $nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
  $rating = mysqli_real_escape_string($conn, $_POST['rating']);
  $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);

  // Proses upload foto jika ada
  if ($_FILES['foto']['name']) {
    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];
    $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed_ext)) {
      $new_name = uniqid() . '.' . $ext;
      $upload_path = "../../assets/img/upload/review/" . $new_name;
      move_uploaded_file($tmp, $upload_path);

      $query = "UPDATE review SET 
                  nama_pelanggan='$nama', 
                  rating='$rating', 
                  komentar='$komentar', 
                  foto='$new_name' 
                WHERE id_review='$id'";
    } else {
      echo "<script>alert('Ekstensi file tidak diizinkan. Hanya JPG, JPEG, PNG, WEBP.'); history.back();</script>";
      exit;
    }
  } else {
    $query = "UPDATE review SET 
                nama_pelanggan='$nama', 
                rating='$rating', 
                komentar='$komentar' 
              WHERE id_review='$id'";
  }

  $update = mysqli_query($conn, $query);

  if ($update) {
    echo "<script>alert('Berhasil update review'); location.href='../../pages/tables/basic-table-review.php';</script>";
  } else {
    echo "<script>alert('Gagal update review'); history.back();</script>";
  }
} else {
  echo "<script>alert('Akses tidak sah'); location.href='../../pages/tables/basic-table-review.php';</script>";
}
?>
