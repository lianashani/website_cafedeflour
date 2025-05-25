<?php
include '../../../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'];
    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $gambar_lama = $_POST['gambar_lama'];
    $gambar = $gambar_lama;

    // Cek jika upload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $namaFile = basename($_FILES['gambar']['name']);
        $targetDir = "../../../assets/img/";
        $targetFile = $targetDir . $namaFile;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
            $gambar = $namaFile;
        }
    }

    $update = "UPDATE menu SET 
        nama_menu = '$nama', 
        harga = '$harga', 
        stok = '$stok', 
        kategori = '$kategori', 
        deskripsi = '$deskripsi',
        gambar = '$gambar'
        WHERE id_menu = $id";

    if (mysqli_query($conn, $update)) {
        // Redirect ke daftar menu
        header("Location: http://db_cafedeflour.test/website/purple-free/dist/pages/tables/basic-table-daftarmenu.php");
        exit;
    } else {
        echo "Gagal mengedit menu: " . mysqli_error($conn);
    }
}
?>
