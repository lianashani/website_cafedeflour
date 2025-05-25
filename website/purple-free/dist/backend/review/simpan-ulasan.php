<?php
include '../../../../config/koneksi.php';

// Ambil data dari form
$nama = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
$komentar = mysqli_real_escape_string($conn, $_POST['komentar']);
$rating = (int)$_POST['rating'];
$tanggal = date('Y-m-d');
$foto_name = '';

// Lokasi folder upload
$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/website/assets/img/upload/review/";

// Buat folder jika belum ada
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Proses upload foto jika ada
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto_name = time() . '_' . uniqid() . '.' . $ext;
    $target_file = $target_dir . $foto_name;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        echo "Gagal mengunggah gambar.";
        exit;
    }
}

// Simpan review tanpa id_menu
$query = "
    INSERT INTO review (nama_pelanggan, komentar, rating, tanggal, foto)
    VALUES ('$nama', '$komentar', $rating, '$tanggal', '$foto_name')
";
mysqli_query($conn, $query);

// Kembali ke halaman semua-ulasan
header('Location: semua-ulasan.php');
exit;
?>
